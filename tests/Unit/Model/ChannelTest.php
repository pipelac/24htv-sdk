<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\Channel;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    public function testFromArray()
    {
        $data = [
            'id'        => 15,
            'name'      => 'Первый канал',
            'number'    => 1,
            'logo_url'  => 'https://example.com/logo.png',
            'category'  => 'Общие',
            'is_active' => true,
        ];

        $channel = Channel::fromArray($data);

        $this->assertInstanceOf('TwentyFourTv\Model\Channel', $channel);
        $this->assertEquals(15, $channel->getId());
        $this->assertEquals('Первый канал', $channel->getName());
        $this->assertEquals(1, $channel->getNumber());
        $this->assertEquals('https://example.com/logo.png', $channel->getLogoUrl());
        $this->assertEquals('Общие', $channel->getCategory());
        $this->assertTrue($channel->isActive());
    }

    public function testToArray()
    {
        $data = [
            'id'        => 15,
            'name'      => 'Первый канал',
            'number'    => 1,
            'logo_url'  => 'https://example.com/logo.png',
            'category'  => 'Общие',
            'is_active' => true,
        ];

        $channel = Channel::fromArray($data);
        $result = $channel->toArray();

        $this->assertEquals($data, $result);
    }

    public function testEmptyData()
    {
        $channel = Channel::fromArray([]);

        $this->assertNull($channel->getId());
        $this->assertNull($channel->getName());
        $this->assertNull($channel->getNumber());
        $this->assertNull($channel->getLogoUrl());
        $this->assertNull($channel->getCategory());
        $this->assertTrue($channel->isActive()); // default true
    }

    public function testCollection()
    {
        $items = [
            ['id' => 1, 'name' => 'CH1'],
            ['id' => 2, 'name' => 'CH2'],
        ];

        $collection = Channel::collection($items);

        $this->assertCount(2, $collection);
        $this->assertInstanceOf('TwentyFourTv\Model\Channel', $collection[0]);
        $this->assertEquals(1, $collection[0]->getId());
        $this->assertEquals(2, $collection[1]->getId());
    }
}
