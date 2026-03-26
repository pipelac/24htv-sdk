<?php

namespace TwentyFourTv\Tests\Unit;

use TwentyFourTv\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private $httpClient;
    private $config;
    private $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock('TwentyFourTv\Contract\HttpClientInterface');
        $this->config = $this->createMock('TwentyFourTv\Contract\ConfigInterface');
        $this->client = new Client($this->httpClient, $this->config);
    }

    public function testUsersReturnsUserService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\UserService', $this->client->users());
    }

    public function testPacketsReturnsPacketService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\PacketService', $this->client->packets());
    }

    public function testSubscriptionsReturnsSubscriptionService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\SubscriptionService', $this->client->subscriptions());
    }

    public function testBalanceReturnsBalanceService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\BalanceService', $this->client->balance());
    }

    public function testChannelsReturnsChannelService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\ChannelService', $this->client->channels());
    }

    public function testDevicesReturnsDeviceService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\DeviceService', $this->client->devices());
    }

    public function testAuthReturnsAuthService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\AuthService', $this->client->auth());
    }

    public function testContractsReturnsContractService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\ContractService', $this->client->contracts());
    }

    public function testTagsReturnsTagService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\TagService', $this->client->tags());
    }

    public function testPromoReturnsPromoService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\PromoService', $this->client->promo());
    }

    public function testMessagesReturnsMessageService()
    {
        $this->assertInstanceOf('TwentyFourTv\Service\MessageService', $this->client->messages());
    }

    public function testLazyLoadingReturnsSameInstance()
    {
        $users1 = $this->client->users();
        $users2 = $this->client->users();
        $this->assertSame($users1, $users2);
    }

    public function testGetConfig()
    {
        $this->assertSame($this->config, $this->client->getConfig());
    }

    public function testGetHttpClient()
    {
        $this->assertSame($this->httpClient, $this->client->getHttpClient());
    }

    public function testRegisterAndConnect()
    {
        $userData = ['username' => 'test', 'phone' => '+71234567890'];
        $user = ['id' => 42, 'username' => 'test'];
        $subs = [['id' => 'sub-1']];

        $callIndex = 0;
        $this->httpClient->expects($this->exactly(2))
            ->method('apiPost')
            ->willReturnCallback(function ($endpoint, $data) use (&$callIndex, $userData, $user, $subs) {
                if ($callIndex === 0) {
                    $this->assertEquals('/users', $endpoint);
                    $this->assertEquals($userData, $data);
                    $callIndex++;
                    return $user;
                }
                $this->assertEquals('/users/42/subscriptions', $endpoint);
                $this->assertEquals([['packet_id' => 80, 'renew' => true]], $data);
                $callIndex++;
                return $subs;
            });

        $result = $this->client->registerAndConnect($userData, 80);
        $this->assertInstanceOf('TwentyFourTv\Model\User', $result['user']);
        $this->assertEquals(42, $result['user']->getId());
        $this->assertEquals('sub-1', $result['subscriptions'][0]['id']);
    }
}
