<?php

namespace Drakakisgeo\Billit\Tests;

use PHPUnit\Framework\TestCase;
use Drakakisgeo\Billit\Billit;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Mockery;

class ClientTest extends TestCase
{


    /**
     * @test
     */
    public function it_can_be_instantiated()
    {
        $billit = new Billit('token', new Client());
        $this->assertInstanceOf(Billit::class, $billit);
    }

    /**
     * @test
     */
    public function requires_token_to_work()
    {
        $billit = new Billit('', new Client($this->guzzleOptions()));
        $this->expectExceptionMessage("You need to provide an API token");
        $billit->welcome();
    }

    /**
     * @test
     */
    public function it_welcomes_you()
    {
        $mock = Mockery::mock(Client::class,function($mock){
            $mock->shouldReceive('get')->once()->andReturn(new Response(200, [], json_encode(['msg' => "Hello from the Billit API"])));
        });
        $billit = new Billit('randomtoken', $mock);
        $this->assertEquals("Hello from the Billit API", $billit->welcome()->msg);
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