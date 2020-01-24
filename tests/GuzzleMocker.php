<?php

namespace Drakakisgeo\Billit\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

trait GuzzleMocker
{
    private function guzzleMock($action, array $response = [], $responseCode = 200)
    {
        return Mockery::mock(Client::class, function ($mock) use ($action, $responseCode, $response) {
            $mock->shouldReceive($action)->once()->andReturn(new Response($responseCode, [],
                json_encode($response)));
        });
    }

    private function guzzleOptions(): array
    {
        return [
            'base_uri' => 'http://api.billit.local',
            'verify' => false,
            'timeout' => 2.0,
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ]
        ];
    }
}