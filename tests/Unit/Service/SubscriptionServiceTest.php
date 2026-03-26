<?php

namespace TwentyFourTv\Tests\Unit\Service;

use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Service\SubscriptionService;
use PHPUnit\Framework\TestCase;

class SubscriptionServiceTest extends TestCase
{
    /** @var HttpClientInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $httpClient;

    /** @var SubscriptionService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock('TwentyFourTv\Contract\HttpClientInterface');
        $this->service = new SubscriptionService($this->httpClient);
    }

    public function testGetAll()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/subscriptions', []);

        $this->service->getAll(42);
    }

    public function testGetCurrent()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/subscriptions/current', []);

        $this->service->getCurrent(42);
    }

    public function testConnect()
    {
        $subs = [['packet_id' => 80, 'renew' => true]];
        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/42/subscriptions', $subs);

        $this->service->connect(42, $subs);
    }

    public function testConnectSingle()
    {
        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/42/subscriptions', [
                ['packet_id' => 80, 'renew' => true],
            ]);

        $this->service->connectSingle(42, 80, true);
    }

    public function testDisconnect()
    {
        $this->httpClient->expects($this->once())
            ->method('apiDelete')
            ->with('/users/42/subscriptions/sub-1');

        $this->service->disconnect(42, 'sub-1');
    }

    public function testPause()
    {
        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/42/subscriptions/sub-1/pauses');

        $this->service->pause(42, 'sub-1');
    }

    public function testUnpause()
    {
        $this->httpClient->expects($this->once())
            ->method('apiDelete')
            ->with('/users/42/subscriptions/sub-1/pauses/pause-1');

        $this->service->unpause(42, 'sub-1', 'pause-1');
    }

    public function testGetByIdInvalidThrows()
    {
        $this->expectException('TwentyFourTv\Exception\ValidationException');
        $this->service->getAll(null);
    }
}
