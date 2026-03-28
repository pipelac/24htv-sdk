<?php

namespace TwentyFourTv\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Util\TokenMasker;

class TokenMaskerTest extends TestCase
{
    public function testMaskLongToken()
    {
        $result = TokenMasker::mask('abcdef1234567890');

        $this->assertEquals('abcdef***7890', $result);
    }

    public function testMaskExactly11Characters()
    {
        $result = TokenMasker::mask('12345678901');

        $this->assertEquals('123456***8901', $result);
    }

    public function testMaskShortToken()
    {
        $this->assertEquals('***', TokenMasker::mask('1234567890')); // 10 chars
        $this->assertEquals('***', TokenMasker::mask('short'));
        $this->assertEquals('***', TokenMasker::mask(''));
    }

    public function testMaskInUrl()
    {
        $url = 'https://api.24htv.tv/v2/users?token=abc123xyz&limit=10';
        $result = TokenMasker::maskInUrl($url);

        $this->assertEquals('https://api.24htv.tv/v2/users?token=***&limit=10', $result);
    }

    public function testMaskInUrlNoToken()
    {
        $url = 'https://api.24htv.tv/v2/users?limit=10';
        $result = TokenMasker::maskInUrl($url);

        $this->assertEquals($url, $result);
    }

    public function testMaskInUrlTokenOnly()
    {
        $url = 'https://api.24htv.tv/v2/users?token=secretvalue123';
        $result = TokenMasker::maskInUrl($url);

        $this->assertEquals('https://api.24htv.tv/v2/users?token=***', $result);
    }

    public function testMaskInUrlCaseInsensitive()
    {
        $url = 'https://api.24htv.tv/v2/users?TOKEN=ABC123&limit=10';
        $result = TokenMasker::maskInUrl($url);

        $this->assertEquals('https://api.24htv.tv/v2/users?token=***&limit=10', $result);
    }
}
