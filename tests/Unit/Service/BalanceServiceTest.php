<?php

namespace TwentyFourTv\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Service\BalanceService;

class BalanceServiceTest extends TestCase
{
    private $httpClient;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new BalanceService($this->httpClient);
    }

    public function testSet()
    {
        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users/42/provider/account', ['id' => 'acc1', 'amount' => '500.00']);

        $this->service->set(42, 'acc1', '500.00');
    }

    public function testGet()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/provider/account');

        $this->service->get(42);
    }

    public function testGetPaymentSources()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/paymentsources');

        $this->service->getPaymentSources();
    }

    public function testGetTransactions()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/accounts/acc1/transactions');

        $this->service->getTransactions(42, 'acc1');
    }

    public function testGetEntityLicenses()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42/entity_licenses');

        $this->service->getEntityLicenses(42);
    }

    public function testRemoveEntityLicense()
    {
        $this->httpClient->expects($this->once())
            ->method('apiDelete')
            ->with('/users/42/entity_licenses/5');

        $this->service->removeEntityLicense(42, 5);
    }

    public function testSetInvalidUserIdThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->set(null, 'acc1', '500');
    }
}
