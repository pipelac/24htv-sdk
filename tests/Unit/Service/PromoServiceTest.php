<?php

namespace TwentyFourTv\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Service\PromoService;

class PromoServiceTest extends TestCase
{
    /** @var HttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpClient;

    /** @var PromoService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new PromoService($this->httpClient);
    }

    public function testGetPacketsCallsApiGet()
    {
        $apiResponse = [
            ['id' => 1, 'name' => 'Promo 1'],
            ['id' => 2, 'name' => 'Promo 2'],
        ];

        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/promopackets', [])
            ->willReturn($apiResponse);

        $result = $this->service->getPackets();

        $this->assertCount(2, $result);
        $this->assertEquals('Promo 1', $result[0]['name']);
    }

    public function testGetPacketsWithOptions()
    {
        $options = ['includes' => 'packets'];
        $apiResponse = [['id' => 1]];

        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/promopackets', $options)
            ->willReturn($apiResponse);

        $result = $this->service->getPackets($options);

        $this->assertCount(1, $result);
    }

    public function testGetPacketByIdCallsApiGet()
    {
        $apiResponse = ['id' => 42, 'name' => 'VIP Promo'];

        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/promopackets/42')
            ->willReturn($apiResponse);

        $result = $this->service->getPacketById(42);

        $this->assertEquals(42, $result['id']);
        $this->assertEquals('VIP Promo', $result['name']);
    }

    public function testDeactivateKeyCallsApiDelete()
    {
        $this->httpClient->expects($this->once())
            ->method('apiDelete')
            ->with('/promokeys/abc123')
            ->willReturn(null);

        $result = $this->service->deactivateKey('abc123');

        $this->assertNull($result);
    }

    public function testGetUserKeysCallsApiGet()
    {
        $apiResponse = [
            ['id' => 'key1', 'code' => 'PROMO2026'],
        ];

        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/promokeys')
            ->willReturn($apiResponse);

        $result = $this->service->getUserKeys(42);

        $this->assertCount(1, $result);
        $this->assertEquals('PROMO2026', $result[0]['code']);
    }

    public function testGetUserKeysBuildsCorrectEndpoint()
    {
        $apiResponse = [];

        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/999/promokeys')
            ->willReturn($apiResponse);

        $result = $this->service->getUserKeys(999);

        $this->assertCount(0, $result);
    }

    public function testActivateUserKeyCallsApiPost()
    {
        $data = ['code' => 'PROMO2026'];
        $apiResponse = ['id' => 'key1', 'code' => 'PROMO2026', 'activated' => true];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/42/promokeys', $data)
            ->willReturn($apiResponse);

        $result = $this->service->activateUserKey(42, $data);

        $this->assertTrue($result['activated']);
    }

    public function testActivateUserKeyBuildsCorrectEndpoint()
    {
        $data = ['code' => 'ANOTHER'];
        $apiResponse = ['id' => 'key2', 'code' => 'ANOTHER', 'activated' => true];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/999/promokeys', $data)
            ->willReturn($apiResponse);

        $result = $this->service->activateUserKey(999, $data);

        $this->assertEquals('key2', $result['id']);
    }
}
