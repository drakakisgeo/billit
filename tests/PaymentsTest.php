<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class PaymentsTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function payments()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->payments();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }

    /**
     * @test
     */
    public function create_payment()
    {
        $mock = $this->guzzleMock('POST', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->paymentCreate(['id' => 1, 'inCharge' => 'tester1']);
        $this->assertEquals(1, $response->data->id);
    }

    /**
     * @test
     */
    public function delete_payment()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->paymentDelete(123);
        $this->assertEmpty($response);
    }

}