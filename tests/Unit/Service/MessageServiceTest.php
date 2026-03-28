<?php

namespace TwentyFourTv\Tests\Unit\Service;

use TwentyFourTv\Service\MessageService;
use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Exception\ValidationException;

class MessageServiceTest extends TestCase
{
    private $httpClient;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new MessageService($this->httpClient);
    }

    public function testGetAll()
    {
        $this->httpClient->expects($this->once())->method('apiGet')->with('/users/42/messages');
        $this->service->getAll(42);
    }

    public function testGetById()
    {
        $this->httpClient->expects($this->once())->method('apiGet')->with('/users/42/messages/msg-1');
        $this->service->getById(42, 'msg-1');
    }

    public function testCreate()
    {
        $data = ['text' => 'Hello!'];
        $this->httpClient->expects($this->once())->method('apiPost')->with('/users/42/messages', $data);
        $this->service->create(42, $data);
    }

    public function testDelete()
    {
        $this->httpClient->expects($this->once())->method('apiDelete')->with('/users/42/messages/msg-1');
        $this->service->delete(42, 'msg-1');
    }

    public function testGetAllInvalidThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->getAll(null);
    }
}
