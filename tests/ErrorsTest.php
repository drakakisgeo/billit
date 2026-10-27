<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use Drakakisgeo\Billit\Exceptions\AuthenticationException;
use Drakakisgeo\Billit\Exceptions\BillitException;
use Drakakisgeo\Billit\Exceptions\ConnectionException;
use Drakakisgeo\Billit\Exceptions\NotFoundException;
use Drakakisgeo\Billit\Exceptions\RateLimitException;
use Drakakisgeo\Billit\Exceptions\ServerException;
use Drakakisgeo\Billit\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ErrorsTest extends TestCase
{
    use MocksHttp;

    public static function statuses(): array
    {
        return [
            [400, ValidationException::class],
            [401, AuthenticationException::class],
            [403, AuthenticationException::class],
            [404, NotFoundException::class],
            [405, BillitException::class],
            [422, ValidationException::class],
            [429, RateLimitException::class],
            [500, ServerException::class],
            [503, ServerException::class],
        ];
    }

    #[Test]
    #[DataProvider('statuses')]
    public function error_statuses_throw_typed_exceptions(int $status, string $exception): void
    {
        $billit = $this->billit($this->json(['statusCode' => $status, 'error' => 'Nope', 'message' => 'Details'], $status));

        try {
            $billit->contacts();
            $this->fail('Expected an exception');
        } catch (BillitException $e) {
            $this->assertSame($exception, $e::class);
            $this->assertSame($status, $e->getStatusCode());
            $this->assertSame('Nope', $e->getResponseBody()->error);
            $this->assertSame("Billit API responded with {$status} (Nope: Details)", $e->getMessage());
        }
    }

    #[Test]
    public function validation_errors_are_exposed_per_field(): void
    {
        $billit = $this->billit($this->json([
            'statusCode' => 422,
            'error' => 'Validation Failed',
            'message' => 'Your input had some validation errors.',
            'errors' => ['company' => ["The 'Company' field is required."]],
        ], 422));

        try {
            $billit->contactCreate([]);
            $this->fail('Expected an exception');
        } catch (ValidationException $e) {
            $this->assertSame(['company' => ["The 'Company' field is required."]], $e->errors());
        }
    }

    #[Test]
    public function rate_limit_exposes_retry_after(): void
    {
        $billit = $this->billit($this->json(['statusCode' => 429], 429, ['Retry-After' => '17']));

        try {
            $billit->invoices();
            $this->fail('Expected an exception');
        } catch (RateLimitException $e) {
            $this->assertSame(17, $e->retryAfter());
        }
    }

    #[Test]
    public function non_json_error_bodies_still_throw_the_right_exception(): void
    {
        $billit = $this->billit(new Response(502, [], '<html>Bad Gateway</html>'));

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Billit API responded with 502 (Bad Gateway)');
        $billit->invoices();
    }

    #[Test]
    public function connection_failures_throw_a_connection_exception(): void
    {
        $billit = $this->billit(new ConnectException('cURL error 28: timed out', new Request('GET', 'invoices')));

        $this->expectException(ConnectionException::class);
        $billit->invoices();
    }

    #[Test]
    public function invalid_json_on_success_throws(): void
    {
        $billit = $this->billit(new Response(200, [], '{not json'));

        $this->expectException(BillitException::class);
        $this->expectExceptionMessage('invalid JSON');
        $billit->invoices();
    }

    #[Test]
    public function clients_without_http_errors_still_throw(): void
    {
        $client = new Client([
            'handler' => HandlerStack::create(new MockHandler([new Response(404, [], '{"error":"Not found"}')])),
            'http_errors' => false,
        ]);

        $this->expectException(NotFoundException::class);
        (new Billit('test-token', true, 'v1', $client))->invoiceShow('abc');
    }

    #[Test]
    public function the_token_is_never_part_of_the_error_message(): void
    {
        $billit = $this->billit($this->json(['error' => 'Authorization Required'], 401));

        try {
            $billit->myAccount();
            $this->fail('Expected an exception');
        } catch (AuthenticationException $e) {
            $this->assertStringNotContainsString('test-token', $e->getMessage());
            $this->assertStringNotContainsString('test-token', (string) $e);
        }
    }

    #[Test]
    public function a_token_is_required(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need to provide an API token');
        new Billit(' ');
    }

    #[Test]
    public function the_version_must_look_like_a_version(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Billit('token', false, '../v1');
    }

    #[Test]
    public function identifiers_cannot_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->billit()->invoiceShow('');
    }
}
