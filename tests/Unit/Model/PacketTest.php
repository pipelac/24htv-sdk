<?php

namespace TwentyFourTv\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Model\Packet;

class PacketTest extends TestCase
{
    public function testFromArrayCreatesPacket()
    {
        $data = [
            'id'          => 80,
            'name'        => 'Базовый',
            'price'       => '199.00',
            'description' => 'Основной тариф',
            'is_base'     => true,
            'is_active'   => true,
            'channels'    => [['id' => 1, 'name' => 'Первый']],
            'availables'  => [],
            'includes'    => [],
        ];

        $packet = Packet::fromArray($data);

        $this->assertEquals(80, $packet->getId());
        $this->assertEquals('Базовый', $packet->getName());
        $this->assertEquals('199.00', $packet->getPrice());
        $this->assertEquals('Основной тариф', $packet->getDescription());
        $this->assertTrue($packet->isBase());
        $this->assertTrue($packet->isActive());
        $this->assertCount(1, $packet->getChannels());
    }

    public function testFromArrayWithMinimalData()
    {
        $packet = Packet::fromArray(['id' => 1, 'name' => 'Test']);

        $this->assertEquals(1, $packet->getId());
        $this->assertEquals('Test', $packet->getName());
        $this->assertNull($packet->getPrice());
        $this->assertFalse($packet->isBase()); // default
        $this->assertTrue($packet->isActive()); // default
        $this->assertEmpty($packet->getChannels());
    }

    public function testToArrayRoundTrip()
    {
        $data = [
            'id'          => 80,
            'name'        => 'Базовый',
            'price'       => '199.00',
            'description' => null,
            'is_base'     => true,
            'is_active'   => true,
            'channels'    => [],
            'availables'  => [],
            'includes'    => [],
        ];

        $packet = Packet::fromArray($data);
        $this->assertEquals($data, $packet->toArray());
    }

    public function testCollectionCreatesPackets()
    {
        $items = [
            ['id' => 1, 'name' => 'Базовый', 'is_base' => true],
            ['id' => 2, 'name' => 'Дополнительный', 'is_base' => false],
        ];

        $packets = Packet::collection($items);

        $this->assertCount(2, $packets);
        $this->assertTrue($packets[0]->isBase());
        $this->assertFalse($packets[1]->isBase());
    }
}
