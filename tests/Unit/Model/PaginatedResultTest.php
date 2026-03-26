<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\PaginatedResult;
use PHPUnit\Framework\TestCase;

class PaginatedResultTest extends TestCase
{
    public function testFromResponseWithItemsAndTotal()
    {
        $response = [
            'items' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
            'total' => 10,
        ];

        $result = PaginatedResult::fromResponse($response, 3, 0);

        $this->assertCount(3, $result->getItems());
        $this->assertEquals(10, $result->getTotal());
        $this->assertEquals(3, $result->getLimit());
        $this->assertEquals(0, $result->getOffset());
        $this->assertTrue($result->hasMore());
        $this->assertEquals(3, $result->getNextOffset());
    }

    public function testFromResponseWithPlainArray()
    {
        $response = [
            ['id' => 1],
            ['id' => 2],
        ];

        $result = PaginatedResult::fromResponse($response, 10, 0);

        $this->assertCount(2, $result->getItems());
        $this->assertNull($result->getTotal());
        $this->assertFalse($result->hasMore()); // 2 < 10
    }

    public function testHasMoreWhenFullPage()
    {
        $items = array_fill(0, 10, ['id' => 1]);
        $result = PaginatedResult::fromResponse($items, 10, 0);

        // Без total, полная страница → hasMore = true
        $this->assertTrue($result->hasMore());
    }

    public function testHasMoreWithTotalComplete()
    {
        $response = [
            'items' => [['id' => 1], ['id' => 2]],
            'total' => 2,
        ];

        $result = PaginatedResult::fromResponse($response, 10, 0);

        $this->assertFalse($result->hasMore()); // total(2) == offset(0) + count(2)
    }

    public function testGetNextOffset()
    {
        $response = [
            'items' => [['id' => 1], ['id' => 2], ['id' => 3]],
            'total' => 15,
        ];

        $result = PaginatedResult::fromResponse($response, 3, 6);

        $this->assertEquals(9, $result->getNextOffset()); // 6 + 3
        $this->assertTrue($result->hasMore()); // 9 < 15
    }

    public function testCount()
    {
        $result = new PaginatedResult(['a', 'b', 'c'], 10, 3, 0);

        $this->assertEquals(3, $result->count());
    }

    public function testIsEmpty()
    {
        $empty = new PaginatedResult([], null, 10, 0);
        $this->assertTrue($empty->isEmpty());

        $nonEmpty = new PaginatedResult(['a'], null, 10, 0);
        $this->assertFalse($nonEmpty->isEmpty());
    }

    public function testEmptyResponse()
    {
        $result = PaginatedResult::fromResponse([], 10, 0);

        $this->assertTrue($result->isEmpty());
        $this->assertEquals(0, $result->count());
        $this->assertFalse($result->hasMore());
        $this->assertEquals(0, $result->getNextOffset());
    }
}
