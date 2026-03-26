<?php

namespace TwentyFourTv\Tests\Unit\Service;

use TwentyFourTv\Service\TagService;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
    private $httpClient;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock('TwentyFourTv\Contract\HttpClientInterface');
        $this->service = new TagService($this->httpClient);
    }

    public function testGetAll()
    {
        $this->httpClient->expects($this->once())->method('apiGet')->with('/tags', []);
        $this->service->getAll();
    }

    public function testCreate()
    {
        $this->httpClient->expects($this->once())->method('apiPost')->with('/tags', ['name' => 'VIP']);
        $this->service->create(['name' => 'VIP']);
    }

    public function testGetById()
    {
        $this->httpClient->expects($this->once())->method('apiGet')->with('/tags/5');
        $this->service->getById(5);
    }

    public function testDelete()
    {
        $this->httpClient->expects($this->once())->method('apiDelete')->with('/tags/5');
        $this->service->delete(5);
    }

    public function testAddToUser()
    {
        $this->httpClient->expects($this->once())->method('apiPost')->with('/users/42/tags', ['tag_id' => 'tag-1']);
        $this->service->addToUser(42, ['tag_id' => 'tag-1']);
    }

    public function testRemoveFromUser()
    {
        $this->httpClient->expects($this->once())->method('apiDelete')->with('/users/42/tags/tag-1');
        $this->service->removeFromUser(42, 'tag-1');
    }
}
