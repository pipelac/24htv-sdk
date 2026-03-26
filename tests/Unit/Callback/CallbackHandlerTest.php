<?php

namespace TwentyFourTv\Tests\Unit\Callback;

use TwentyFourTv\Callback\CallbackHandler;
use TwentyFourTv\Callback\CallbackResponse;
use PHPUnit\Framework\TestCase;

class CallbackHandlerTest extends TestCase
{
    private $config;
    private $handler;

    protected function setUp(): void
    {
        // Создаём простой мок Config через анонимный класс-обёртку для PHP 5.6
        $this->config = $this->getMockBuilder('TwentyFourTv\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new CallbackHandler($this->config);
    }

    public function testHandleAuthWithCustomResolver()
    {
        $this->handler->setAuthResolver(function ($params) {
            return [
                'result'       => 'success',
                'provider_uid' => '12345',
            ];
        });

        $response = $this->handler->handle('/auth', ['ip' => '10.0.0.1']);
        $this->assertInstanceOf('TwentyFourTv\Callback\CallbackResponse', $response);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('12345', $response->getData()['provider_uid']);
    }

    public function testHandleAuthWithoutResolverReturnsError()
    {
        $response = $this->handler->handle('/auth', ['ip' => '10.0.0.1']);
        $this->assertFalse($response->isSuccess());
        $this->assertStringContainsString('not configured', $response->getData()['errmsg']);
    }

    public function testHandlePacketDefault()
    {
        $body = json_encode(['user_id' => 42, 'packet_id' => 80]);
        $response = $this->handler->handle('/packet', [], $body);
        $this->assertTrue($response->isSuccess());
    }

    public function testHandleDeleteSubscriptionDefault()
    {
        $body = json_encode(['user_id' => 42, 'subscription_id' => 'sub-1']);
        $response = $this->handler->handle('/delete_subscription', [], $body);
        $this->assertTrue($response->isSuccess());
    }

    public function testHandleUnknownPath()
    {
        $response = $this->handler->handle('/unknown');
        $this->assertFalse($response->isSuccess());
        $this->assertStringContainsString('Unknown callback type', $response->getData()['errmsg']);
    }

    public function testHandleBalanceWithoutResolver()
    {
        $response = $this->handler->handle('/balance', ['provider_uid' => '123']);
        $this->assertFalse($response->isSuccess());
        $this->assertStringContainsString('not configured', $response->getData()['errmsg']);
    }

    public function testHandleBalanceWithCustomResolver()
    {
        $this->handler->setBalanceResolver(function ($params) {
            return [
                'result'  => 'success',
                'balance' => '500.00',
            ];
        });

        $response = $this->handler->handle('/balance', ['provider_uid' => '123']);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('500.00', $response->getData()['balance']);
    }

    public function testHandlePacketWithCustomHandler()
    {
        $this->handler->setPacketHandler(function ($params, $body) {
            return ['result' => 'success', 'charged' => true];
        });

        $body = json_encode(['user_id' => 42, 'packet_id' => 80]);
        $response = $this->handler->handle('/packet', [], $body);
        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->getData()['charged']);
    }

    public function testCallbackResponseToJson()
    {
        $response = CallbackResponse::success(['balance' => '100.00']);
        $json = $response->toJson();
        $decoded = json_decode($json, true);
        $this->assertEquals('success', $decoded['result']);
        $this->assertEquals('100.00', $decoded['balance']);
    }

    public function testCallbackResponseError()
    {
        $response = CallbackResponse::error('Something went wrong');
        $this->assertFalse($response->isSuccess());
        $this->assertEquals('Something went wrong', $response->getData()['errmsg']);
    }

    public function testHandleAuthWithResolverInterface()
    {
        $resolver = $this->getMockBuilder('TwentyFourTv\Contract\AuthResolverInterface')->getMock();
        $resolver->method('__invoke')->willReturn([
            'result'       => 'success',
            'provider_uid' => '99999',
        ]);

        $this->handler->setAuthResolver($resolver);
        $response = $this->handler->handle('/auth', ['ip' => '10.0.0.1']);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('99999', $response->getData()['provider_uid']);
    }

    public function testHandleBalanceWithResolverInterface()
    {
        $resolver = $this->getMockBuilder('TwentyFourTv\Contract\BalanceResolverInterface')->getMock();
        $resolver->method('__invoke')->willReturn([
            'result'  => 'success',
            'balance' => '750.00',
        ]);

        $this->handler->setBalanceResolver($resolver);
        $response = $this->handler->handle('/balance', ['provider_uid' => '123']);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('750.00', $response->getData()['balance']);
    }
}
