<?php

namespace TwentyFourTv\Tests\Unit\Resolver;

use TwentyFourTv\Resolver\UtmAuthResolver;
use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\DatabaseInterface;
use TwentyFourTv\Contract\LoggerInterface;

class UtmAuthResolverTest extends TestCase
{
    private $db;
    private $logger;

    protected function setUp(): void
    {
        $this->db = $this->getMockBuilder(DatabaseInterface::class)->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    public function testAuthByIpSuccess()
    {
        $this->db->method('queryOne')->willReturn([
            'account_id' => 32240,
            'login'      => 'test_user',
        ]);

        $resolver = new UtmAuthResolver($this->db, $this->logger);
        $result = $resolver(['ip' => '10.0.0.1']);

        $this->assertEquals('success', $result['result']);
        $this->assertEquals('32240', $result['provider_uid']);
    }

    public function testAuthByIpNotFound()
    {
        $this->db->method('queryOne')->willReturn(null);

        $resolver = new UtmAuthResolver($this->db, $this->logger);
        $result = $resolver(['ip' => '10.0.0.99']);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('User not found', $result['errmsg']);
    }

    public function testAuthWithEmptyIp()
    {
        $resolver = new UtmAuthResolver($this->db);
        $result = $resolver(['ip' => '']);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('IP address is required', $result['errmsg']);
    }

    public function testAuthWithoutIp()
    {
        $resolver = new UtmAuthResolver($this->db);
        $result = $resolver([]);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('IP address is required', $result['errmsg']);
    }

    public function testAuthDatabaseError()
    {
        $this->db->method('queryOne')->willThrowException(new \RuntimeException('Connection lost'));

        $resolver = new UtmAuthResolver($this->db, $this->logger);
        $result = $resolver(['ip' => '10.0.0.1']);

        $this->assertEquals('error', $result['result']);
        $this->assertEquals('Database error', $result['errmsg']);
    }
}
