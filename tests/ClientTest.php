<?php

namespace Drakakisgeo\Billit\Tests;

use PHPUnit\Framework\TestCase;
use Drakakisgeo\Billit\Billit;
use GuzzleHttp\Client;

class ClientTest extends TestCase
{

    use GuzzleMocker;

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
    public function token_is_required()
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
        $mock = $this->guzzleMock('GET', ['msg' => "Hello from the Billit API"]);
        $billit = new Billit('randomtoken', $mock);
        $this->assertEquals("Hello from the Billit API", $billit->welcome()->msg);
    }

    /**
     * @test
     */
    public function client_returns_exceptions()
    {
        $mock = $this->guzzleMock('DELETE', [
            "statusCode" => 401,
            "error" => "Not authorized"
        ], 401);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->customerDelete(123);
        $this->assertEquals(401, $response->statusCode);
    }

}