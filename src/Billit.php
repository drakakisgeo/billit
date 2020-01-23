<?php

namespace DrakakisGeo\Billit;

use stdClass;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Guzzle\Http\Exception\ClientErrorResponseException;

class Billit
{

    private $client;
    private $token = null;

    /**
     * Billit constructor.
     * @param string $token
     * @param Client|null $client
     */
    public function __construct(string $token, Client $client)
    {
        $this->client = $client ?: $this->defaultClient();
        $this->token = $token;
    }

    public function welcome()
    {
        return $this->touch('get', '/');
    }

    public function myAccount()
    {
        return $this->touch('get', 'account');
    }

    public function customersList(array $params = null)
    {
        return $this->touch('get', 'customers');
    }

    public function customersCreate(array $payload)
    {
        return $this->touch('post', 'customers', $payload);
    }

    /**
     * @param int $resourceId
     * @param array $payload
     * @return stdClass
     */
    public function customersUpdate(int $resourceId, array $payload): stdClass
    {
        return $this->touch('put', "customers/{$resourceId}", $payload);
    }

    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function customersDelete(int $resourceId): stdClass
    {
        return $this->touch('delete', "customers/{$resourceId}");
    }

    /**
     * @param int $resourceId
     * @return stdClass
     */
    public function customersShow(int $resourceId): stdClass
    {
        return $this->touch('get', "customers/{$resourceId}");
    }


    /**
     * @param string $action
     * @param string $url
     * @param array|null $data
     * @return stdClass
     */
    private function touch(string $action, string $url, array $payload = null): stdClass
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
                'base_uri' => 'https://api.billit.io',
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
