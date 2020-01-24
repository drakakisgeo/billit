<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class SuppliersTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function suppliers()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->suppliers();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }


    /**
     * @test
     */
    public function show_supplier()
    {
        $mock = $this->guzzleMock('get', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->supplierShow(123);
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function delete_supplier()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken', $mock);
        $response = $billit->supplierDelete(123);
        $this->assertEmpty($response);
    }

}