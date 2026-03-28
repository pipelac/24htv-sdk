<?php

namespace TwentyFourTv\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Service\AuthService;

class AuthServiceTest extends TestCase
{
    /** @var HttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpClient;

    /** @var AuthService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AuthService($this->httpClient);
    }

    public function testGetProviderTokenCallsApiPost()
    {
        $data = ['user_id' => 12680];
        $apiResponse = [
            'access_token' => 'eyJhbGciOiJIUzI1...',
            'expired' => null,
        ];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/auth/provider', $data)
            ->willReturn($apiResponse);

        $result = $this->service->getProviderToken($data);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals('eyJhbGciOiJIUzI1...', $result['access_token']);
    }

    public function testGetProviderTokenWithUsername()
    {
        $data = ['username' => 'test_user'];
        $apiResponse = ['access_token' => 'token123', 'expired' => '2026-12-31'];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/auth/provider', $data)
            ->willReturn($apiResponse);

        $result = $this->service->getProviderToken($data);

        $this->assertEquals('token123', $result['access_token']);
        $this->assertEquals('2026-12-31', $result['expired']);
    }

    public function testGetProviderTokenWithPhone()
    {
        $data = ['phone' => '+79001234567'];
        $apiResponse = ['access_token' => 'phone_token', 'expired' => null];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/auth/provider', $data)
            ->willReturn($apiResponse);

        $result = $this->service->getProviderToken($data);

        $this->assertEquals('phone_token', $result['access_token']);
        $this->assertNull($result['expired']);
    }

    public function testGetProviderTokenWithAllProviders()
    {
        $data = ['user_id' => 42, 'all_providers' => true];
        $apiResponse = ['access_token' => 'multi_token', 'expired' => null];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/auth/provider', $data)
            ->willReturn($apiResponse);

        $result = $this->service->getProviderToken($data);

        $this->assertEquals('multi_token', $result['access_token']);
    }
}
