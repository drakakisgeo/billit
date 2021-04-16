<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class PurchasesTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function purchases()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->purchases();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }

    /**
     * @test
     */
    public function create_purchase()
    {
        $mock = $this->guzzleMock('POST', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->purchaseCreate(['id' => 1, 'inCharge' => 'tester1']);
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function show_purchase()
    {
        $mock = $this->guzzleMock('get', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->purchaseShow(123);
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function delete_purchase()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->purchaseDelete(123);
        $this->assertEmpty($response);
    }

}