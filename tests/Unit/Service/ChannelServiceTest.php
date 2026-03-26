<?php

namespace TwentyFourTv\Tests\Unit\Service;

use TwentyFourTv\Service\ChannelService;
use PHPUnit\Framework\TestCase;

class ChannelServiceTest extends TestCase
{
    private $httpClient;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock('TwentyFourTv\Contract\HttpClientInterface');
        $this->service = new ChannelService($this->httpClient);
    }

    public function testGetAll()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/channels', ['limit' => 5]);

        $this->service->getAll(['limit' => 5]);
    }

    public function testGetById()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/channels/1');

        $this->service->getById(1);
    }

    public function testGetSchedule()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/channels/1/schedule', []);

        $this->service->getSchedule(1);
    }

    public function testGetCategories()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/channels/categories', []);

        $this->service->getCategories();
    }

    public function testGetFreeList()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/channels/free_list');

        $this->service->getFreeList();
    }

    public function testGetPackets()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/channels/1/packets');

        $this->service->getPackets(1);
    }

    public function testGetByIdInvalidThrows()
    {
        $this->expectException('TwentyFourTv\Exception\ValidationException');
        $this->service->getById(null);
    }
}
