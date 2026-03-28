<?php

namespace TwentyFourTv\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Service\DeviceService;

class DeviceServiceTest extends TestCase
{
    private $httpClient;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new DeviceService($this->httpClient);
    }

    public function testGetAll()
    {
        $this->httpClient->expects($this->once())->method('apiGet')->with('/devices', ['limit' => 5]);
        $this->service->getAll(['limit' => 5]);
    }

    public function testCreate()
    {
        $data = ['serial' => 'ABC123'];
        $this->httpClient->expects($this->once())->method('apiPost')->with('/devices', $data);
        $this->service->create($data);
    }

    public function testGetUserDevices()
    {
        $this->httpClient->expects($this->once())->method('apiGet')->with('/users/42/devices');
        $this->service->getUserDevices(42);
    }

    public function testDeleteUserDevice()
    {
        $this->httpClient->expects($this->once())->method('apiDelete')->with('/users/42/devices/dev-1');
        $this->service->deleteUserDevice(42, 'dev-1');
    }

    public function testGetUserDevicesInvalidThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->getUserDevices(null);
    }
}
