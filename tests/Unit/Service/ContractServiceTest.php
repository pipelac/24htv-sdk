<?php

namespace TwentyFourTv\Tests\Unit\Service;

use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Service\ContractService;
use PHPUnit\Framework\TestCase;

class ContractServiceTest extends TestCase
{
    /** @var HttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpClient;

    /** @var ContractService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new ContractService($this->httpClient);
    }

    public function testTerminateCallsApiPut()
    {
        $this->httpClient->expects($this->once())
            ->method('apiPut')
            ->with('/users/42/change_provider/1')
            ->willReturn(['status' => 'ok']);

        $result = $this->service->terminate(42);

        $this->assertNotNull($result);
    }

    public function testTerminateBuildsCorrectEndpointForLargeId()
    {
        $this->httpClient->expects($this->once())
            ->method('apiPut')
            ->with('/users/99999/change_provider/1')
            ->willReturn(['status' => 'ok']);

        $result = $this->service->terminate(99999);

        $this->assertNotNull($result);
    }

    public function testTerminateBuildsCorrectEndpoint()
    {
        $this->httpClient->expects($this->once())
            ->method('apiPut')
            ->with('/users/999/change_provider/1')
            ->willReturn(null);

        $this->service->terminate(999);
    }
}
