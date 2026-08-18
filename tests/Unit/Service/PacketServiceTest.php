<?php

namespace TwentyFourTv\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Model\Packet;
use TwentyFourTv\Service\PacketService;

class PacketServiceTest extends TestCase
{
    /** @var HttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpClient;

    /** @var PacketService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new PacketService($this->httpClient);
    }

    public function testGetAll()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/packets', [])
            ->willReturn([['id' => 80, 'name' => 'Бандл 2']]);

        $result = $this->service->getAll();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Packet::class, $result[0]);
        $this->assertEquals(80, $result[0]->getId());
    }

    public function testGetById()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/packets/80', ['includes' => 'channels']);

        $this->service->getById(80, ['includes' => 'channels']);
    }

    public function testGetByIdInvalidThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->getById(null);
    }

    public function testGetFlat()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/packets', ['is_base' => 'true']);

        $this->service->getFlat(true);
    }

    public function testGetPurchases()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/packets/80/purchases');

        $this->service->getPurchases(80);
    }

    public function testGetUserPackets()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/packets');

        $this->service->getUserPackets(42);
    }

    public function testCreateUserPacket()
    {
        $data = ['packet_id' => 80];
        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/42/packets', $data);

        $this->service->createUserPacket(42, $data);
    }
}
