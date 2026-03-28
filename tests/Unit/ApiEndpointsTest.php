<?php

namespace TwentyFourTv\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\ApiEndpoints;

class ApiEndpointsTest extends TestCase
{
    public function testUsersEndpoint()
    {
        $this->assertEquals('/users', ApiEndpoints::USERS);
    }

    public function testUserByIdTemplate()
    {
        $result = sprintf(ApiEndpoints::USER_BY_ID, 42);
        $this->assertEquals('/users/42', $result);
    }

    public function testUserSubscriptionsTemplate()
    {
        $result = sprintf(ApiEndpoints::USER_SUBSCRIPTIONS, 42);
        $this->assertEquals('/users/42/subscriptions', $result);
    }

    public function testUserSubscriptionByIdTemplate()
    {
        $result = sprintf(ApiEndpoints::USER_SUBSCRIPTION_BY_ID, 42, 'abc-def');
        $this->assertEquals('/users/42/subscriptions/abc-def', $result);
    }

    public function testUserSubscriptionPauseByIdTemplate()
    {
        $result = sprintf(ApiEndpoints::USER_SUBSCRIPTION_PAUSE_BY_ID, 42, 'sub-1', 'pause-2');
        $this->assertEquals('/users/42/subscriptions/sub-1/pauses/pause-2', $result);
    }

    public function testPacketsEndpoint()
    {
        $this->assertEquals('/packets', ApiEndpoints::PACKETS);
    }

    public function testPacketByIdTemplate()
    {
        $result = sprintf(ApiEndpoints::PACKET_BY_ID, 80);
        $this->assertEquals('/packets/80', $result);
    }

    public function testChannelsEndpoint()
    {
        $this->assertEquals('/channels', ApiEndpoints::CHANNELS);
    }

    public function testChannelScheduleTemplate()
    {
        $result = sprintf(ApiEndpoints::CHANNEL_SCHEDULE, 100);
        $this->assertEquals('/channels/100/schedule', $result);
    }

    public function testDevicesEndpoint()
    {
        $this->assertEquals('/devices', ApiEndpoints::DEVICES);
    }

    public function testTagsEndpoint()
    {
        $this->assertEquals('/tags', ApiEndpoints::TAGS);
    }

    public function testPromoPacketsEndpoint()
    {
        $this->assertEquals('/promopackets', ApiEndpoints::PROMO_PACKETS);
    }

    public function testAuthProviderEndpoint()
    {
        $this->assertEquals('/auth/provider', ApiEndpoints::AUTH_PROVIDER);
    }

    public function testPaymentSourcesEndpoint()
    {
        $this->assertEquals('/paymentsources', ApiEndpoints::PAYMENT_SOURCES);
    }

    public function testUserChangeProviderTemplate()
    {
        $result = sprintf(ApiEndpoints::USER_CHANGE_PROVIDER, 42);
        $this->assertEquals('/users/42/change_provider/1', $result);
    }

    public function testUserAccountTransactionsTemplate()
    {
        $result = sprintf(ApiEndpoints::USER_ACCOUNT_TRANSACTIONS, 42, 'acc-1');
        $this->assertEquals('/users/42/accounts/acc-1/transactions', $result);
    }

    public function testStaticEndpoints()
    {
        $staticEndpoints = [
            ApiEndpoints::CHANNEL_CATEGORIES,
            ApiEndpoints::CHANNEL_CATEGORY_LIST,
            ApiEndpoints::CHANNEL_LIST,
            ApiEndpoints::CHANNEL_FREE_LIST,
            ApiEndpoints::USER_SELF_CHANNEL_LIST,
            ApiEndpoints::USER_SELF_CHANNELS,
        ];

        foreach ($staticEndpoints as $endpoint) {
            $this->assertStringStartsWith('/', $endpoint);
            $this->assertStringNotContainsString('%s', $endpoint);
        }
    }
}
