<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function testFromArray()
    {
        $data = ['id' => 'tag-5', 'name' => 'VIP', 'shortname' => 'vip'];

        $tag = Tag::fromArray($data);

        $this->assertEquals('tag-5', $tag->getId());
        $this->assertEquals('VIP', $tag->getName());
        $this->assertEquals('vip', $tag->getShortname());
    }

    public function testToArray()
    {
        $data = ['id' => 'tag-5', 'name' => 'VIP', 'shortname' => 'vip'];

        $tag = Tag::fromArray($data);
        $this->assertEquals($data, $tag->toArray());
    }

    public function testEmptyData()
    {
        $tag = Tag::fromArray([]);

        $this->assertNull($tag->getId());
        $this->assertNull($tag->getName());
        $this->assertNull($tag->getShortname());
    }

    public function testCollection()
    {
        $items = [
            ['id' => 1, 'name' => 'VIP'],
            ['id' => 2, 'name' => 'Staff'],
        ];

        $collection = Tag::collection($items);

        $this->assertCount(2, $collection);
        $this->assertInstanceOf('TwentyFourTv\Model\Tag', $collection[0]);
    }
}
