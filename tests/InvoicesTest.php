<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class InvoicesTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function invoices()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->invoices();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }

    /**
     * @test
     */
    public function create_invoice()
    {
        $mock = $this->guzzleMock('POST', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->invoiceCreate(['id' => 1, 'inCharge' => 'tester1']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function update_invoice()
    {
        $mock = $this->guzzleMock('PUT', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->invoiceUpdate("123-32123-123123-123123",['id' => 1, 'inCharge' => 'tester2']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function show_invoice()
    {
        $mock = $this->guzzleMock('get', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->invoiceShow("123-32123-123123-123123");
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function delete_invoice()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->invoiceDelete("123-32123-123123-123123");
        $this->assertEmpty($response);
    }

}