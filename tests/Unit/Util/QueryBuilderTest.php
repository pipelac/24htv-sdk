<?php

namespace TwentyFourTv\Tests\Unit\Util;

use TwentyFourTv\Util\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testCreateReturnsInstance()
    {
        $qb = QueryBuilder::create();
        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }

    public function testEmptyBuilderReturnsEmptyArray()
    {
        $this->assertEquals([], QueryBuilder::create()->toArray());
    }

    public function testLimitAndOffset()
    {
        $query = QueryBuilder::create()
            ->limit(20)
            ->offset(40)
            ->toArray();

        $this->assertEquals(20, $query['limit']);
        $this->assertEquals(40, $query['offset']);
    }

    public function testPage()
    {
        $query = QueryBuilder::create()
            ->page(3, 10)
            ->toArray();

        $this->assertEquals(10, $query['limit']);
        $this->assertEquals(20, $query['offset']); // (3-1) * 10
    }

    public function testPageDefaultSize()
    {
        $query = QueryBuilder::create()
            ->page(2)
            ->toArray();

        $this->assertEquals(20, $query['limit']);
        $this->assertEquals(20, $query['offset']); // (2-1) * 20
    }

    public function testSearch()
    {
        $query = QueryBuilder::create()
            ->search('test query')
            ->toArray();

        $this->assertEquals('test query', $query['search']);
    }

    public function testIncludes()
    {
        $query = QueryBuilder::create()
            ->includes(['channels', 'availables'])
            ->toArray();

        $this->assertEquals('channels,availables', $query['includes']);
    }

    public function testIncludesMergesDuplicates()
    {
        $query = QueryBuilder::create()
            ->includes(['channels'])
            ->includes(['channels', 'availables'])
            ->toArray();

        $this->assertEquals('channels,availables', $query['includes']);
    }

    public function testOrderBy()
    {
        $query = QueryBuilder::create()
            ->orderBy('name', 'desc')
            ->toArray();

        $this->assertEquals('name:desc', $query['order']);
    }

    public function testOrderByDefaultAsc()
    {
        $query = QueryBuilder::create()
            ->orderBy('price')
            ->toArray();

        $this->assertEquals('price:asc', $query['order']);
    }

    public function testWhere()
    {
        $query = QueryBuilder::create()
            ->where('is_base', 'true')
            ->where('status', 'active')
            ->toArray();

        $this->assertEquals('true', $query['is_base']);
        $this->assertEquals('active', $query['status']);
    }

    public function testTypes()
    {
        $query = QueryBuilder::create()
            ->types('active')
            ->toArray();

        $this->assertEquals('active', $query['types']);
    }

    public function testDate()
    {
        $query = QueryBuilder::create()
            ->date('2026-03-21')
            ->toArray();

        $this->assertEquals('2026-03-21', $query['date']);
    }

    public function testTimeRange()
    {
        $query = QueryBuilder::create()
            ->timeRange(1000, 2000)
            ->toArray();

        $this->assertEquals(1000, $query['start']);
        $this->assertEquals(2000, $query['end']);
    }

    public function testProviderUid()
    {
        $query = QueryBuilder::create()
            ->providerUid('UID-123')
            ->toArray();

        $this->assertEquals('UID-123', $query['provider_uid']);
    }

    public function testHas()
    {
        $qb = QueryBuilder::create()->limit(10);

        $this->assertTrue($qb->has('limit'));
        $this->assertFalse($qb->has('offset'));
    }

    public function testGet()
    {
        $qb = QueryBuilder::create()->limit(10);

        $this->assertEquals(10, $qb->get('limit'));
        $this->assertNull($qb->get('offset'));
        $this->assertEquals('default', $qb->get('offset', 'default'));
    }

    public function testReset()
    {
        $qb = QueryBuilder::create()
            ->limit(10)
            ->search('test');

        $qb->reset();
        $this->assertEquals([], $qb->toArray());
    }

    public function testFluentChaining()
    {
        $query = QueryBuilder::create()
            ->limit(20)
            ->offset(0)
            ->search('test')
            ->includes(['channels'])
            ->orderBy('name', 'asc')
            ->where('is_base', 'true')
            ->toArray();

        $this->assertCount(6, $query);
        $this->assertEquals(20, $query['limit']);
        $this->assertEquals(0, $query['offset']);
        $this->assertEquals('test', $query['search']);
        $this->assertEquals('channels', $query['includes']);
        $this->assertEquals('name:asc', $query['order']);
    }
}
