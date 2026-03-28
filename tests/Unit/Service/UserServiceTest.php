<?php

namespace TwentyFourTv\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Model\User;
use TwentyFourTv\Service\UserService;

class UserServiceTest extends TestCase
{
    /** @var HttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpClient;

    /** @var UserService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new UserService($this->httpClient);
    }

    public function testRegisterReturnsUserDto()
    {
        $data = ['username' => 'test', 'phone' => '+71234567890'];
        $apiResponse = ['id' => 42, 'username' => 'test', 'phone' => '+71234567890', 'is_active' => true];

        $this->httpClient->expects($this->once())
            ->method('apiPost')
            ->with('/users', $data)
            ->willReturn($apiResponse);

        $result = $this->service->register($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(42, $result->getId());
        $this->assertEquals('test', $result->getUsername());
        $this->assertEquals('+71234567890', $result->getPhone());
        $this->assertTrue($result->isActive());
    }

    public function testRegisterMissingUsernameThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->register(['phone' => '+71234567890']);
    }

    public function testRegisterMissingPhoneThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->register(['username' => 'test']);
    }

    public function testGetByIdReturnsUserDto()
    {
        $apiResponse = ['id' => 42, 'username' => 'test', 'email' => 'test@mail.ru'];

        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users/42')
            ->willReturn($apiResponse);

        $result = $this->service->getById(42);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(42, $result->getId());
        $this->assertEquals('test', $result->getUsername());
        $this->assertEquals('test@mail.ru', $result->getEmail());
    }

    public function testGetByIdInvalidThrows()
    {
        $this->expectException(ValidationException::class);
        $this->service->getById(null);
    }

    public function testGetAllReturnsArray()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users', ['limit' => 10])
            ->willReturn([]);

        $result = $this->service->getAll(['limit' => 10]);
        $this->assertIsArray($result);
    }

    public function testUpdateReturnsUserDto()
    {
        $apiResponse = ['id' => 42, 'first_name' => 'Updated'];

        $this->httpClient->expects($this->once())
            ->method('apiPatch')
            ->with('/users/42', ['first_name' => 'Updated'])
            ->willReturn($apiResponse);

        $result = $this->service->update(42, ['first_name' => 'Updated']);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Updated', $result->getFirstName());
    }

    public function testBlockReturnsUserDto()
    {
        $apiResponse = ['id' => 42, 'is_active' => false];

        $this->httpClient->expects($this->once())
            ->method('apiPatch')
            ->with('/users/42', ['is_active' => false])
            ->willReturn($apiResponse);

        $result = $this->service->block(42);

        $this->assertInstanceOf(User::class, $result);
        $this->assertFalse($result->isActive());
    }

    public function testUnblockReturnsUserDto()
    {
        $apiResponse = ['id' => 42, 'is_active' => true];

        $this->httpClient->expects($this->once())
            ->method('apiPatch')
            ->with('/users/42', ['is_active' => true])
            ->willReturn($apiResponse);

        $result = $this->service->unblock(42);

        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($result->isActive());
    }

    public function testDelete()
    {
        $this->httpClient->expects($this->once())
            ->method('apiDelete')
            ->with('/users/42');

        $this->service->delete(42);
    }

    public function testFindByPhone()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users', ['phone' => '+71234567890'])
            ->willReturn([]);

        $this->service->findByPhone('+71234567890');
    }

    public function testFindByProviderUid()
    {
        $this->httpClient->expects($this->once())
            ->method('apiGet')
            ->with('/users', ['provider_uid' => 'abc123'])
            ->willReturn([]);

        $this->service->findByProviderUid('abc123');
    }

    public function testArchiveCallsApiDelete()
    {
        $this->httpClient->expects($this->once())
            ->method('apiDelete')
            ->with('/users/42/archive');

        $this->service->archive(42);
    }
}
