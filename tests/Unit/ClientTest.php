<?php

namespace TwentyFourTv\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Client;
use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Contract\Service\UserServiceInterface;
use TwentyFourTv\Model\User;
use TwentyFourTv\Service\AuthService;
use TwentyFourTv\Service\BalanceService;
use TwentyFourTv\Service\ChannelService;
use TwentyFourTv\Service\ContractService;
use TwentyFourTv\Service\DeviceService;
use TwentyFourTv\Service\MessageService;
use TwentyFourTv\Service\PacketService;
use TwentyFourTv\Service\PromoService;
use TwentyFourTv\Service\SubscriptionService;
use TwentyFourTv\Service\TagService;
use TwentyFourTv\Service\UserService;

class ClientTest extends TestCase
{
    private $httpClient;
    private $config;
    private $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->client = new Client($this->httpClient, $this->config);
    }

    public function testUsersReturnsUserService()
    {
        $this->assertInstanceOf(UserService::class, $this->client->users());
    }

    public function testPacketsReturnsPacketService()
    {
        $this->assertInstanceOf(PacketService::class, $this->client->packets());
    }

    public function testSubscriptionsReturnsSubscriptionService()
    {
        $this->assertInstanceOf(SubscriptionService::class, $this->client->subscriptions());
    }

    public function testBalanceReturnsBalanceService()
    {
        $this->assertInstanceOf(BalanceService::class, $this->client->balance());
    }

    public function testChannelsReturnsChannelService()
    {
        $this->assertInstanceOf(ChannelService::class, $this->client->channels());
    }

    public function testDevicesReturnsDeviceService()
    {
        $this->assertInstanceOf(DeviceService::class, $this->client->devices());
    }

    public function testAuthReturnsAuthService()
    {
        $this->assertInstanceOf(AuthService::class, $this->client->auth());
    }

    public function testContractsReturnsContractService()
    {
        $this->assertInstanceOf(ContractService::class, $this->client->contracts());
    }

    public function testTagsReturnsTagService()
    {
        $this->assertInstanceOf(TagService::class, $this->client->tags());
    }

    public function testPromoReturnsPromoService()
    {
        $this->assertInstanceOf(PromoService::class, $this->client->promo());
    }

    public function testMessagesReturnsMessageService()
    {
        $this->assertInstanceOf(MessageService::class, $this->client->messages());
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

    public function testRegisterService()
    {
        $customService = $this->createMock(UserServiceInterface::class);

        $this->client->registerService(UserServiceInterface::class, function () use ($customService) {
            return $customService;
        });

        $this->assertSame($customService, $this->client->users());
    }

    public function testRegisterServiceOverridesExisting()
    {
        // Первый вызов — дефолтный сервис
        $default = $this->client->users();
        $this->assertInstanceOf(UserService::class, $default);

        // Подмена
        $customService = $this->createMock(UserServiceInterface::class);
        $this->client->registerService(UserServiceInterface::class, function () use ($customService) {
            return $customService;
        });

        // Теперь возвращает кастомный
        $this->assertSame($customService, $this->client->users());
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
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals(42, $result['user']->getId());
        $this->assertEquals('sub-1', $result['subscriptions'][0]['id']);
    }
}
