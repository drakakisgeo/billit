<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use PHPUnit\Framework\TestCase;

class TagsTest extends TestCase
{

    use GuzzleMocker;

    /**
     * @test
     */
    public function tags()
    {
        $mock = $this->guzzleMock('GET', [
            'data' => [
                ['id' => 1, 'inCharge' => 'tester1'],
                ['id' => 2, 'inCharge' => 'tester2']
            ],
        ]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->tags();
        $this->assertEquals(2, sizeof($response->data));
        $this->assertEquals(1, $response->data[0]->id);
    }


    /**
     * @test
     */
    public function show_tag()
    {
        $mock = $this->guzzleMock('get', ['data' => ['id' => 1, 'inCharge' => 'tester1']]);
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->tagShow(123);
        $this->assertEquals(1, $response->data->id);
    }


    /**
     * @test
     */
    public function delete_tag()
    {
        $mock = $this->guzzleMock('DELETE');
        $billit = new Billit('randomtoken',false,'v1', $mock);
        $response = $billit->tagDelete(123);
        $this->assertEmpty($response);
    }

}