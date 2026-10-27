<?php

namespace Drakakisgeo\Billit;

use Drakakisgeo\Billit\Exceptions\AuthenticationException;
use Drakakisgeo\Billit\Exceptions\BillitException;
use Drakakisgeo\Billit\Exceptions\ConnectionException;
use Drakakisgeo\Billit\Exceptions\NotFoundException;
use Drakakisgeo\Billit\Exceptions\RateLimitException;
use Drakakisgeo\Billit\Exceptions\ServerException;
use Drakakisgeo\Billit\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;
use stdClass;

class Billit
{
    public const PRODUCTION_URL = 'https://api.billit.io';
    public const SANDBOX_URL = 'https://api.sandbox-billit.xyz';
    public const DEFAULT_TIMEOUT = 10.0;
    public const DEFAULT_CONNECT_TIMEOUT = 5.0;

    private ClientInterface $client;
    private ?stdClass $myAccountCached = null;

    /**
     * @param string $token The API token, generated in Settings > API of your Billit account
     * @param bool $sandbox Use the sandbox environment instead of production
     * @param string $version The API version, e.g. "v1"
     * @param ClientInterface|null $client A preconfigured Guzzle client, see Billit::httpClient()
     */
    public function __construct(
        #[SensitiveParameter] private readonly string $token,
        private readonly bool $sandbox = false,
        private readonly string $version = 'v1',
        ?ClientInterface $client = null,
    ) {
        if (trim($token) === '') {
            throw new InvalidArgumentException('You need to provide an API token');
        }
        if (!preg_match('/^v\d+$/', $version)) {
            throw new InvalidArgumentException("Invalid API version [{$version}], expected something like \"v1\".");
        }

        $this->client = $client ?? self::httpClient($sandbox, $version);
    }

    /**
     * Build a Guzzle client configured for the Billit API.
     */
    public static function httpClient(
        bool $sandbox = false,
        string $version = 'v1',
        float $timeout = self::DEFAULT_TIMEOUT,
        float $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT,
    ): Client {
        return new Client([
            'base_uri' => self::baseUri($sandbox, $version),
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public static function baseUri(bool $sandbox = false, string $version = 'v1'): string
    {
        return ($sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL) . "/{$version}/";
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Check that the token works.
     *
     * @throws BillitException
     */
    public function welcome(): stdClass
    {
        return $this->request('GET', '');
    }

    /**
     * Account details, including the vat, payment method and biller ids needed for invoices.
     * The response is cached for the lifetime of this instance.
     *
     * @throws BillitException
     */
    public function myAccount(): stdClass
    {
        return $this->myAccountCached ??= $this->request('GET', 'account');
    }

    /**
     * Customers and suppliers.
     *
     * @param array<string, mixed> $params q, unpaid, tagid, withRelations, per_page, page
     * @throws BillitException
     */
    public function contacts(array $params = []): stdClass
    {
        return $this->request('GET', 'contacts', $params);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function contactCreate(array $payload): stdClass
    {
        return $this->request('POST', 'contacts', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function contactUpdate(int $resourceId, array $payload): stdClass
    {
        return $this->request('PUT', "contacts/{$resourceId}", $payload);
    }

    /**
     * @throws BillitException
     */
    public function contactShow(int $resourceId): stdClass
    {
        return $this->request('GET', "contacts/{$resourceId}");
    }

    /**
     * @throws BillitException
     */
    public function contactDelete(int $resourceId): stdClass
    {
        return $this->request('DELETE', "contacts/{$resourceId}");
    }

    /**
     * @param array<string, mixed> $params withRelations, per_page, page
     * @throws BillitException
     */
    public function invoices(array $params = []): stdClass
    {
        return $this->request('GET', 'invoices', $params);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function invoiceCreate(array $payload): stdClass
    {
        return $this->request('POST', 'invoices', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function invoiceUpdate(string $uuid, array $payload): stdClass
    {
        return $this->request('PUT', 'invoices/' . $this->segment($uuid), $payload);
    }

    /**
     * @throws BillitException
     */
    public function invoiceShow(string $uuid): stdClass
    {
        return $this->request('GET', 'invoices/' . $this->segment($uuid));
    }

    /**
     * @throws BillitException
     */
    public function invoiceDelete(string $uuid): stdClass
    {
        return $this->request('DELETE', 'invoices/' . $this->segment($uuid));
    }

    /**
     * Get a legal ALP signature to forward to a POS. Requires the Ilyda connection to be enabled.
     *
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function alpSignature(array $payload): stdClass
    {
        return $this->request('POST', 'alp-signature', $payload);
    }

    /**
     * @param array<string, mixed> $params has_stock, stock_less, stock_greater, tag_id, withRelations, per_page, page
     * @throws BillitException
     */
    public function products(array $params = []): stdClass
    {
        return $this->request('GET', 'products', $params);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function productCreate(array $payload): stdClass
    {
        return $this->request('POST', 'products', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function productUpdate(int $resourceId, array $payload): stdClass
    {
        return $this->request('PUT', "products/{$resourceId}", $payload);
    }

    /**
     * @throws BillitException
     */
    public function productShow(int $resourceId): stdClass
    {
        return $this->request('GET', "products/{$resourceId}");
    }

    /**
     * @throws BillitException
     */
    public function productDelete(int $resourceId): stdClass
    {
        return $this->request('DELETE', "products/{$resourceId}");
    }

    /**
     * One click payment links.
     *
     * @param array<string, mixed> $params per_page, page
     * @throws BillitException
     */
    public function ocps(array $params = []): stdClass
    {
        return $this->request('GET', 'ocps', $params);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function ocpCreate(array $payload): stdClass
    {
        return $this->request('POST', 'ocps', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function ocpUpdate(string $uuid, array $payload): stdClass
    {
        return $this->request('PUT', 'ocps/' . $this->segment($uuid), $payload);
    }

    /**
     * @throws BillitException
     */
    public function ocpShow(string $uuid): stdClass
    {
        return $this->request('GET', 'ocps/' . $this->segment($uuid));
    }

    /**
     * @throws BillitException
     */
    public function ocpDelete(string $uuid): stdClass
    {
        return $this->request('DELETE', 'ocps/' . $this->segment($uuid));
    }

    /**
     * @param array<string, mixed> $params per_page, page
     * @throws BillitException
     */
    public function payments(array $params = []): stdClass
    {
        return $this->request('GET', 'payments', $params);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function paymentCreate(array $payload): stdClass
    {
        return $this->request('POST', 'payments', $payload);
    }

    /**
     * @throws BillitException
     */
    public function paymentDelete(int $resourceId): stdClass
    {
        return $this->request('DELETE', "payments/{$resourceId}");
    }

    /**
     * @param array<string, mixed> $params per_page, page
     * @throws BillitException
     */
    public function purchases(array $params = []): stdClass
    {
        return $this->request('GET', 'purchases', $params);
    }

    /**
     * @param array<string, mixed> $payload
     * @throws BillitException
     */
    public function purchaseCreate(array $payload): stdClass
    {
        return $this->request('POST', 'purchases', $payload);
    }

    /**
     * @throws BillitException
     */
    public function purchaseShow(int $resourceId): stdClass
    {
        return $this->request('GET', "purchases/{$resourceId}");
    }

    /**
     * @throws BillitException
     */
    public function purchaseDelete(int $resourceId): stdClass
    {
        return $this->request('DELETE', "purchases/{$resourceId}");
    }

    /**
     * @param array<string, mixed> $params per_page, page
     * @throws BillitException
     */
    public function tags(array $params = []): stdClass
    {
        return $this->request('GET', 'tags', $params);
    }

    /**
     * @throws BillitException
     */
    public function tagShow(int $resourceId): stdClass
    {
        return $this->request('GET', "tags/{$resourceId}");
    }

    /**
     * @throws BillitException
     */
    public function tagDelete(int $resourceId): stdClass
    {
        return $this->request('DELETE', "tags/{$resourceId}");
    }

    /**
     * Look up a Greek company by its VAT id (AFM). GSIS must be enabled in your Billit account.
     *
     * @throws BillitException
     */
    public function gsis(string $vatId): stdClass
    {
        return $this->request('GET', 'gsis/' . $this->segment($vatId));
    }

    /**
     * GET and DELETE send $data as the query string, POST and PUT as a JSON body.
     *
     * @param array<string, mixed> $data
     * @throws BillitException
     */
    private function request(string $method, string $uri, array $data = []): stdClass
    {
        $options = ['headers' => ['Authorization' => 'Bearer ' . $this->token]];
        if (in_array($method, ['POST', 'PUT'], true)) {
            $options['json'] = $data;
        } elseif ($data !== []) {
            $options['query'] = $data;
        }

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (ConnectException $e) {
            throw new ConnectionException('Could not reach the Billit API: ' . $e->getMessage(), null, null, $e);
        } catch (RequestException $e) {
            if ($e->getResponse() === null) {
                throw new ConnectionException('Could not reach the Billit API: ' . $e->getMessage(), null, null, $e);
            }
            throw $this->exceptionFor($e->getResponse(), $e);
        } catch (GuzzleException $e) {
            throw new BillitException($e->getMessage(), null, null, $e);
        }

        // A client created with 'http_errors' => false hands back error responses instead of throwing.
        if ($response->getStatusCode() >= 400) {
            throw $this->exceptionFor($response);
        }

        return $this->decode($response) ?? new stdClass();
    }

    private function exceptionFor(ResponseInterface $response, ?GuzzleException $previous = null): BillitException
    {
        $status = $response->getStatusCode();
        try {
            $body = $this->decode($response);
        } catch (BillitException) {
            $body = null;
        }
        $message = trim(($body->error ?? $response->getReasonPhrase()) . ': ' . ($body->message ?? ''), ' :');
        $message = "Billit API responded with {$status}" . ($message !== '' ? " ({$message})" : '');

        return match (true) {
            $status === 400, $status === 422 => new ValidationException($message, $status, $body, $previous),
            $status === 401, $status === 403 => new AuthenticationException($message, $status, $body, $previous),
            $status === 404 => new NotFoundException($message, $status, $body, $previous),
            $status === 429 => new RateLimitException(
                $message,
                $status,
                $body,
                $previous,
                is_numeric($response->getHeaderLine('Retry-After')) ? (int) $response->getHeaderLine('Retry-After') : null,
            ),
            $status >= 500 => new ServerException($message, $status, $body, $previous),
            default => new BillitException($message, $status, $body, $previous),
        };
    }

    /**
     * @throws BillitException
     */
    private function decode(ResponseInterface $response): ?stdClass
    {
        $contents = (string) $response->getBody();
        if (trim($contents) === '') {
            return null;
        }

        try {
            $decoded = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BillitException('The Billit API returned an invalid JSON response.', $response->getStatusCode(), null, $e);
        }

        if (is_array($decoded)) {
            return (object) ['data' => $decoded];
        }

        return $decoded instanceof stdClass ? $decoded : null;
    }

    /**
     * Encode a user supplied identifier so it can't escape its path segment.
     */
    private function segment(string $value): string
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('The resource identifier cannot be empty.');
        }

        return rawurlencode($value);
    }
}
