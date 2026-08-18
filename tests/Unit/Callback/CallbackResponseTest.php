<?php

namespace TwentyFourTv\Tests\Unit\Callback;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Callback\CallbackResponse;

class CallbackResponseTest extends TestCase
{
    public function testConstructorSetsData()
    {
        $data = ['result' => 'success'];
        $response = new CallbackResponse($data, 200);

        $this->assertEquals($data, $response->getData());
        $this->assertEquals(200, $response->getHttpCode());
    }

    public function testIsSuccessTrue()
    {
        $response = new CallbackResponse(['result' => 'success']);

        $this->assertTrue($response->isSuccess());
    }

    public function testIsSuccessFalse()
    {
        $response = new CallbackResponse(['result' => 'error', 'errmsg' => 'fail']);

        $this->assertFalse($response->isSuccess());
    }

    public function testToJson()
    {
        $response = new CallbackResponse(['result' => 'success']);
        $json = $response->toJson();

        $this->assertStringContainsString('success', $json);
        $decoded = json_decode($json, true);
        $this->assertEquals('success', $decoded['result']);
    }

    public function testSuccessFactory()
    {
        $response = CallbackResponse::success(['balance' => '100.50']);

        $this->assertTrue($response->isSuccess());
        $data = $response->getData();
        $this->assertEquals('100.50', $data['balance']);
    }

    public function testErrorFactory()
    {
        $response = CallbackResponse::error('Something went wrong', 500);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals(500, $response->getHttpCode());
        $data = $response->getData();
        $this->assertEquals('Something went wrong', $data['errmsg']);
    }

    public function testDefaultHttpCode()
    {
        $response = new CallbackResponse(['result' => 'success']);

        $this->assertEquals(200, $response->getHttpCode());
    }
}
