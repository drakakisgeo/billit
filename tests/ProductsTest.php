<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function products()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->products();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }

    /**
     * @test
     */
    public function create_product()
    {
        $mock = $this->guzzleMock('POST', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->productCreate(['id' => 1, 'inCharge' => 'tester1']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function update_product()
    {
        $mock = $this->guzzleMock('PUT', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->productUpdate(123,['id' => 1, 'inCharge' => 'tester2']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function show_product()
    {
        $mock = $this->guzzleMock('get', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->productShow(123);
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function delete_product()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->productDelete(123);
        $this->assertEmpty($response);
    }

}