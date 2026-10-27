<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EndpointsTest extends TestCase
{
    use MocksHttp;

    private const PAYLOAD = ['title' => 'Test'];
    private const UUID = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';

    public static function endpoints(): array
    {
        return [
            'welcome' => ['welcome', [], 'GET', '/v1/'],
            'myAccount' => ['myAccount', [], 'GET', '/v1/account'],
            'contacts' => ['contacts', [], 'GET', '/v1/contacts'],
            'contactCreate' => ['contactCreate', [self::PAYLOAD], 'POST', '/v1/contacts', self::PAYLOAD],
            'contactUpdate' => ['contactUpdate', [7, self::PAYLOAD], 'PUT', '/v1/contacts/7', self::PAYLOAD],
            'contactShow' => ['contactShow', [7], 'GET', '/v1/contacts/7'],
            'contactDelete' => ['contactDelete', [7], 'DELETE', '/v1/contacts/7'],
            'invoices' => ['invoices', [], 'GET', '/v1/invoices'],
            'invoiceCreate' => ['invoiceCreate', [self::PAYLOAD], 'POST', '/v1/invoices', self::PAYLOAD],
            'invoiceUpdate' => ['invoiceUpdate', [self::UUID, self::PAYLOAD], 'PUT', '/v1/invoices/' . self::UUID, self::PAYLOAD],
            'invoiceShow' => ['invoiceShow', [self::UUID], 'GET', '/v1/invoices/' . self::UUID],
            'invoiceDelete' => ['invoiceDelete', [self::UUID], 'DELETE', '/v1/invoices/' . self::UUID],
            'alpSignature' => ['alpSignature', [self::PAYLOAD], 'POST', '/v1/alp-signature', self::PAYLOAD],
            'products' => ['products', [], 'GET', '/v1/products'],
            'productCreate' => ['productCreate', [self::PAYLOAD], 'POST', '/v1/products', self::PAYLOAD],
            'productUpdate' => ['productUpdate', [7, self::PAYLOAD], 'PUT', '/v1/products/7', self::PAYLOAD],
            'productShow' => ['productShow', [7], 'GET', '/v1/products/7'],
            'productDelete' => ['productDelete', [7], 'DELETE', '/v1/products/7'],
            'ocps' => ['ocps', [], 'GET', '/v1/ocps'],
            'ocpCreate' => ['ocpCreate', [self::PAYLOAD], 'POST', '/v1/ocps', self::PAYLOAD],
            'ocpUpdate' => ['ocpUpdate', [self::UUID, self::PAYLOAD], 'PUT', '/v1/ocps/' . self::UUID, self::PAYLOAD],
            'ocpShow' => ['ocpShow', [self::UUID], 'GET', '/v1/ocps/' . self::UUID],
            'ocpDelete' => ['ocpDelete', [self::UUID], 'DELETE', '/v1/ocps/' . self::UUID],
            'payments' => ['payments', [], 'GET', '/v1/payments'],
            'paymentCreate' => ['paymentCreate', [self::PAYLOAD], 'POST', '/v1/payments', self::PAYLOAD],
            'paymentDelete' => ['paymentDelete', [7], 'DELETE', '/v1/payments/7'],
            'purchases' => ['purchases', [], 'GET', '/v1/purchases'],
            'purchaseCreate' => ['purchaseCreate', [self::PAYLOAD], 'POST', '/v1/purchases', self::PAYLOAD],
            'purchaseShow' => ['purchaseShow', [7], 'GET', '/v1/purchases/7'],
            'purchaseDelete' => ['purchaseDelete', [7], 'DELETE', '/v1/purchases/7'],
            'tags' => ['tags', [], 'GET', '/v1/tags'],
            'tagShow' => ['tagShow', [7], 'GET', '/v1/tags/7'],
            'tagDelete' => ['tagDelete', [7], 'DELETE', '/v1/tags/7'],
            'gsis' => ['gsis', ['094014201'], 'GET', '/v1/gsis/094014201'],
        ];
    }

    #[Test]
    #[DataProvider('endpoints')]
    public function it_calls_the_documented_endpoint(string $method, array $args, string $httpMethod, string $path, ?array $body = null): void
    {
        $billit = $this->billit($this->json(['data' => ['id' => 1]]));

        $response = $billit->$method(...$args);

        $request = $this->lastRequest();
        $this->assertSame($httpMethod, $request->getMethod());
        $this->assertSame('api.sandbox-billit.xyz', $request->getUri()->getHost());
        $this->assertSame($path, $request->getUri()->getPath());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        $this->assertSame($body, $this->lastJsonBody());
        $this->assertSame(1, $response->data->id);
    }

    #[Test]
    public function list_filters_are_sent_as_query_string(): void
    {
        $billit = $this->billit($this->json(['data' => [], 'meta' => []]));

        $billit->contacts(['q' => 'Testing S.A.', 'tagid' => 24, 'per_page' => 250]);

        $this->assertSame('q=Testing%20S.A.&tagid=24&per_page=250', $this->lastRequest()->getUri()->getQuery());
        $this->assertSame('', (string) $this->lastRequest()->getBody());
    }

    #[Test]
    public function list_responses_are_decoded(): void
    {
        $billit = $this->billit($this->json(['data' => [['id' => 1], ['id' => 2]]]));

        $response = $billit->invoices();

        $this->assertCount(2, $response->data);
        $this->assertSame(2, $response->data[1]->id);
    }

    #[Test]
    public function an_empty_response_body_returns_an_empty_object(): void
    {
        $billit = $this->billit(new \GuzzleHttp\Psr7\Response(204));

        $this->assertEquals(new \stdClass(), $billit->invoiceDelete(self::UUID));
    }

    #[Test]
    public function the_account_is_cached_per_instance(): void
    {
        $billit = $this->billit($this->json(['data' => ['id' => 1]]), $this->json(['data' => ['id' => 2]]));

        $billit->myAccount();
        $this->assertSame(1, $billit->myAccount()->data->id);
        $this->assertCount(1, $this->history);
    }

    #[Test]
    public function identifiers_cannot_escape_their_path_segment(): void
    {
        $billit = $this->billit();

        $billit->invoiceShow('../account?x=1');

        $this->assertSame('/v1/invoices/..%2Faccount%3Fx%3D1', $this->lastRequest()->getUri()->getPath());
        $this->assertSame('', $this->lastRequest()->getUri()->getQuery());
    }

    #[Test]
    public function base_uri_follows_environment_and_version(): void
    {
        $this->assertSame('https://api.billit.io/v1/', Billit::baseUri());
        $this->assertSame('https://api.sandbox-billit.xyz/v2/', Billit::baseUri(true, 'v2'));
    }
}
