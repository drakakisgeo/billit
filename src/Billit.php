<?php

namespace Drakakisgeo\Billit;

use GuzzleHttp\Psr7\Response;
use stdClass;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Guzzle\Http\Exception\ClientErrorResponseException;

class Billit
{

    private $client;
    private $token = null;
    private $myAccountCached = null;
    private $sandbox;
    private $version;

    /**
     * Billit constructor.
     * @param string $token
     * @param Client|null $client
     */
    public function __construct(string $token, $sandbox = false, $version = 'v1', Client $client = null)
    {
        $this->client = $client ?: $this->defaultClient();
        $this->token = $token;
        $this->sandbox = $sandbox;
        $this->version = $version;
    }

    public function welcome()
    {
        return $this->touch('get', '/');
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function myAccount(): stdClass
    {
        if (is_null($this->myAccountCached)) {
            $this->myAccountCached = $this->touch('get', 'account');
        }
        return $this->myAccountCached;
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function customers(array $params = null)
    {
        return $this->touch('get', 'customers', $params);
    }


    /**
     * @param array $payload
     * @return stdClass
     * @throws Exception
     */
    public function customerCreate(array $payload): stdClass
    {
        return $this->touch('post', 'customers', $payload);
    }


    /**
     * @param int $resourceId
     * @param array $payload
     * @return stdClass
     */
    public function customerUpdate(int $resourceId, array $payload): stdClass
    {
        return $this->touch('put', "customers/{$resourceId}", $payload);
    }

    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function customerShow(int $resourceId): stdClass
    {
        return $this->touch('get', "customers/{$resourceId}");
    }

    /**
     * @param int $resourceId
     * @throws Exception
     */
    public function customerDelete(int $resourceId)
    {
        return $this->touch('delete', "customers/{$resourceId}");
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function invoices(array $params = null)
    {
        return $this->touch('get', 'invoices', $params);
    }

    /**
     * @param array $payload
     * @return stdClass
     * @throws Exception
     */
    public function invoiceCreate(array $payload): stdClass
    {
        return $this->touch('post', 'invoices', $payload);
    }

    /**
     * @param string $uuid
     * @param array $payload
     * @return stdClass
     */
    public function invoiceUpdate(string $uuid, array $payload): stdClass
    {
        return $this->touch('put', "invoices/{$uuid}", $payload);
    }

    /**
     * @param string $uuid
     * @return stdClass
     */
    public function invoiceShow(string $uuid): stdClass
    {
        return $this->touch('get', "invoices/{$uuid}");
    }

    /**
     * @param string $uuid
     * @throws Exception
     */
    public function invoiceDelete(string $uuid)
    {
        return $this->touch('delete', "invoices/{$uuid}");
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function products(array $params = null)
    {
        return $this->touch('get', 'products', $params);
    }


    /**
     * @param array $payload
     * @return stdClass
     * @throws Exception
     */
    public function productCreate(array $payload): stdClass
    {
        return $this->touch('post', 'products', $payload);
    }


    /**
     * @param int $resourceId
     * @param array $payload
     * @return stdClass
     */
    public function productUpdate(int $resourceId, array $payload): stdClass
    {
        return $this->touch('put', "products/{$resourceId}", $payload);
    }

    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function productShow(int $resourceId): stdClass
    {
        return $this->touch('get', "products/{$resourceId}");
    }

    /**
     * @param int $resourceId
     * @throws Exception
     */
    public function productDelete(int $resourceId)
    {
        return $this->touch('delete', "products/{$resourceId}");
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function payments(array $params = null)
    {
        return $this->touch('get', 'payments', $params);
    }


    /**
     * @param array $payload
     * @return stdClass
     * @throws Exception
     */
    public function paymentCreate(array $payload): stdClass
    {
        return $this->touch('post', 'payments', $payload);
    }


    /**
     * @param int $resourceId
     * @throws Exception
     */
    public function paymentDelete(int $resourceId)
    {
        return $this->touch('delete', "payments/{$resourceId}");
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function purchases(array $params = null)
    {
        return $this->touch('get', 'purchases', $params);
    }


    /**
     * @param array $payload
     * @return stdClass
     * @throws Exception
     */
    public function purchaseCreate(array $payload): stdClass
    {
        return $this->touch('post', 'purchases', $payload);
    }


    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function purchaseShow(int $resourceId): stdClass
    {
        return $this->touch('get', "purchases/{$resourceId}");
    }

    /**
     * @param int $resourceId
     * @throws Exception
     */
    public function purchaseDelete(int $resourceId)
    {
        return $this->touch('delete', "purchases/{$resourceId}");
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function tags(array $params = null)
    {
        return $this->touch('get', 'tags', $params);
    }


    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function tagShow(int $resourceId): stdClass
    {
        return $this->touch('get', "tags/{$resourceId}");
    }

    /**
     * @param int $resourceId
     * @throws Exception
     */
    public function tagDelete(int $resourceId)
    {
        return $this->touch('delete', "tags/{$resourceId}");
    }

    /**
     * @param array|null $params
     * @return stdClass
     * @throws Exception
     */
    public function suppliers(array $params = null)
    {
        return $this->touch('get', 'suppliers', $params);
    }

    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function supplierShow(int $resourceId): stdClass
    {
        return $this->touch('get', "suppliers/{$resourceId}");
    }

    /**
     * @param int $resourceId
     * @throws Exception
     */
    public function supplierDelete(int $resourceId)
    {
        return $this->touch('delete', "suppliers/{$resourceId}");
    }


    /**
     * @param string $action
     * @param string $url
     * @param array|null $data
     */
    private function touch(string $action, string $url, array $payload = null)
    {
        if ($this->actionIsNotAllowed($action)) {
            throw new Exception("Use one of the allowed actions for this client.");
        }
        if ($this->tokenIsMissing()) {
            throw new Exception("You need to provide an API token");
        }

        try {
            $options = ['headers' => ['Authorization' => 'Bearer ' . $this->token]];
            if (!is_null($payload)) {
                $options['json'] = $payload;
            }

            /** @var Response $response */
            $response = $this->client->$action($url, $options);
            return json_decode($response->getBody()->getContents());

        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody()->getContents());
        } catch (Exception $e) {
            return json_decode(json_encode(['error' => $e->getMessage()]));
        }


    }


    private function defaultClient(): Client
    {
        return new Client([
            [
                'base_uri' => $this->sandbox ? "https://api.sandbox-billit.xyz/{$this->version}" : "https://api.billit.io/{$this->version}",
                'timeout' => 2.0,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json'
                ]
            ]
        ]);
    }

    private function actionIsNotAllowed($action)
    {
        return !in_array(strtolower($action), ['get', 'post', 'put', 'delete']);
    }

    private function tokenIsMissing()
    {
        return is_null($this->token) || $this->token == "";
    }

}
