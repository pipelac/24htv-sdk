<?php

namespace TwentyFourTv\Tests\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\DatabaseInterface;
use TwentyFourTv\Contract\LoggerInterface;
use TwentyFourTv\Resolver\UtmBalanceResolver;

class UtmBalanceResolverTest extends TestCase
{
    private $db;
    private $logger;

    protected function setUp(): void
    {
        $this->db = $this->getMockBuilder(DatabaseInterface::class)->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    public function testBalanceFound()
    {
        $this->db->method('queryOne')->willReturn(['balance' => '500.123']);

        $resolver = new UtmBalanceResolver($this->db, $this->logger);
        $result = $resolver(['provider_uid' => '32240']);

        $this->assertEquals('success', $result['result']);
        $this->assertEquals('500.12', $result['balance']);
    }

    public function testAccountNotFound()
    {
        $this->db->method('queryOne')->willReturn(null);

        $resolver = new UtmBalanceResolver($this->db, $this->logger);
        $result = $resolver(['provider_uid' => '99999']);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('Account not found', $result['errmsg']);
    }

    public function testMissingProviderUid()
    {
        $resolver = new UtmBalanceResolver($this->db);
        $result = $resolver([]);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('provider_uid is required', $result['errmsg']);
    }

    public function testDatabaseError()
    {
        $this->db->method('queryOne')->willThrowException(new \RuntimeException('Connection lost'));

        $resolver = new UtmBalanceResolver($this->db, $this->logger);
        $result = $resolver(['provider_uid' => '32240']);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('Database error', $result['errmsg']);
    }
}
