<?php

namespace TwentyFourTv\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Config;
use TwentyFourTv\Exception\ConfigException;

class ConfigTest extends TestCase
{
    /** @var string */
    private $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), '24htv_config_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testLoadFromIni()
    {
        $this->writeIni([
            'api' => [
                'token'   => 'test_token_12345678',
                'base_url' => 'https://provapi.24h.tv/v2',
            ],
        ]);

        $config = new Config($this->tmpFile);

        $this->assertEquals('test_token_12345678', $config->getToken());
        $this->assertEquals('https://provapi.24h.tv/v2', $config->getBaseUrl());
    }

    public function testDefaultValues()
    {
        $this->writeIni([
            'api' => ['token' => 'test_token_12345678'],
        ]);

        $config = new Config($this->tmpFile);

        $this->assertEquals(Config::DEFAULT_BASE_URL, $config->getBaseUrl());
        $this->assertEquals(Config::DEFAULT_TIMEOUT, $config->getTimeout());
        $this->assertEquals(Config::DEFAULT_CONNECT_TIMEOUT, $config->getConnectTimeout());
        $this->assertEquals(Config::DEFAULT_MAX_RETRIES, $config->getMaxRetries());
    }

    public function testGetOrDefault()
    {
        $this->writeIni([
            'api' => ['token' => 'test_token_12345678'],
        ]);

        $config = new Config($this->tmpFile);

        $this->assertEquals('fallback', $config->getOrDefault('missing.key', 'fallback'));
    }

    public function testGetThrowsOnMissingKey()
    {
        $this->writeIni([
            'api' => ['token' => 'test_token_12345678'],
        ]);

        $config = new Config($this->tmpFile);

        $this->expectException(ConfigException::class);
        $config->get('nonexistent.key');
    }

    public function testMissingTokenThrows()
    {
        $this->writeIni([
            'api' => ['base_url' => 'https://example.com'],
        ]);

        $this->expectException(ConfigException::class);
        new Config($this->tmpFile);
    }

    public function testMissingFileThrows()
    {
        $this->expectException(ConfigException::class);
        new Config('/nonexistent/path/config.ini');
    }

    public function testTypedGetters()
    {
        $this->writeIni([
            'api' => [
                'token'   => 'test_token_12345678',
                'timeout' => '15',
            ],
            'billing' => [
                'enabled' => 'true',
                'retries' => '3',
                'name'    => 'test_billing',
            ],
        ]);

        $config = new Config($this->tmpFile);

        $this->assertEquals(15, $config->getInt('api.timeout'));
        $this->assertTrue($config->getBool('billing.enabled'));
        $this->assertEquals('test_billing', $config->getString('billing.name'));
    }

    public function testToArrayMasksToken()
    {
        $this->writeIni([
            'api' => ['token' => 'test_token_12345678'],
        ]);

        $config = new Config($this->tmpFile);
        $array = $config->toArray();

        $this->assertNotEquals('test_token_12345678', $array['api']['token']);
        $this->assertStringContainsString('***', $array['api']['token']);
    }

    public function testGetSection()
    {
        $this->writeIni([
            'api' => [
                'token'   => 'test_token_12345678',
                'timeout' => '10',
            ],
        ]);

        $config = new Config($this->tmpFile);
        $section = $config->getSection('api');

        $this->assertArrayHasKey('token', $section);
        $this->assertArrayHasKey('timeout', $section);
    }

    /**
     * @param array $data
     */
    private function writeIni(array $data)
    {
        $content = '';
        foreach ($data as $section => $values) {
            $content .= "[{$section}]\n";
            foreach ($values as $key => $value) {
                $content .= "{$key} = \"{$value}\"\n";
            }
            $content .= "\n";
        }
        file_put_contents($this->tmpFile, $content);
    }
}
