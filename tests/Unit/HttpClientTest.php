<?php

namespace TwentyFourTv\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\HttpClient;

class HttpClientTest extends TestCase
{
    /** @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->config->method('getToken')->willReturn('test_token_12345');
        $this->config->method('getBaseUrl')->willReturn('https://provapi.24h.tv/v2');
        $this->config->method('getTimeout')->willReturn(10);
        $this->config->method('getConnectTimeout')->willReturn(5);
        $this->config->method('getOrDefault')->willReturn(2);
    }

    public function testConstructor()
    {
        $client = new HttpClient($this->config);

        $this->assertNotNull($client);
    }

    public function testSdkVersionConstant()
    {
        $this->assertNotEmpty(\TwentyFourTv\SdkVersion::VERSION);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', \TwentyFourTv\SdkVersion::VERSION);
    }

    public function testAddBeforeMiddleware()
    {
        $client = new HttpClient($this->config);
        $called = false;

        $result = $client->addBeforeMiddleware(function () use (&$called) {
            $called = true;
        });

        // Fluent interface
        $this->assertSame($client, $result);
    }

    public function testAddAfterMiddleware()
    {
        $client = new HttpClient($this->config);

        $result = $client->addAfterMiddleware(function () {
            // no-op
        });

        $this->assertSame($client, $result);
    }
}
