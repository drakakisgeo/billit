<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class CustomersTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function customers()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->customers();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }

    /**
     * @test
     */
    public function create_customer()
    {
        $mock = $this->guzzleMock('POST', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->customerCreate(['id' => 1, 'inCharge' => 'tester1']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function update_customer()
    {
        $mock = $this->guzzleMock('PUT', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->customerUpdate(123,['id' => 1, 'inCharge' => 'tester2']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function show_customer()
    {
        $mock = $this->guzzleMock('get', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->customerShow(123);
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function delete_customer()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->customerDelete(123);
        $this->assertEmpty($response);
    }

}