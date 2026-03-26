<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\Balance;
use PHPUnit\Framework\TestCase;

class BalanceTest extends TestCase
{
    public function testFromArray()
    {
        $data = ['id' => 'acc-100', 'amount' => '1500.50'];

        $balance = Balance::fromArray($data);

        $this->assertEquals('acc-100', $balance->getId());
        $this->assertEquals('1500.50', $balance->getAmount());
    }

    public function testToArray()
    {
        $data = ['id' => 'acc-100', 'amount' => '1500.50'];

        $balance = Balance::fromArray($data);
        $this->assertEquals($data, $balance->toArray());
    }

    public function testEmptyData()
    {
        $balance = Balance::fromArray([]);

        $this->assertNull($balance->getId());
        $this->assertNull($balance->getAmount());
    }

    public function testCollection()
    {
        $items = [
            ['id' => '1', 'amount' => '100'],
            ['id' => '2', 'amount' => '200'],
        ];

        $collection = Balance::collection($items);

        $this->assertCount(2, $collection);
        $this->assertInstanceOf('TwentyFourTv\Model\Balance', $collection[0]);
        $this->assertEquals('100', $collection[0]->getAmount());
    }
}
