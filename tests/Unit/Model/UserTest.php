<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testFromArrayCreatesUser()
    {
        $data = [
            'id'               => 42,
            'username'         => 'ivan',
            'phone'            => '+79001234567',
            'email'            => 'ivan@mail.ru',
            'first_name'       => 'Иван',
            'last_name'        => 'Петров',
            'provider_uid'     => '12345',
            'is_active'        => true,
            'is_provider_free' => false,
            'created_at'       => '2026-01-01T00:00:00.000Z',
        ];

        $user = User::fromArray($data);

        $this->assertInstanceOf('TwentyFourTv\Model\User', $user);
        $this->assertEquals(42, $user->getId());
        $this->assertEquals('ivan', $user->getUsername());
        $this->assertEquals('+79001234567', $user->getPhone());
        $this->assertEquals('ivan@mail.ru', $user->getEmail());
        $this->assertEquals('Иван', $user->getFirstName());
        $this->assertEquals('Петров', $user->getLastName());
        $this->assertEquals('12345', $user->getProviderUid());
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isProviderFree());
        $this->assertEquals('2026-01-01T00:00:00.000Z', $user->getCreatedAt());
    }

    public function testFromArrayWithMinimalData()
    {
        $user = User::fromArray(['id' => 1]);

        $this->assertEquals(1, $user->getId());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getEmail());
        $this->assertTrue($user->isActive()); // default
    }

    public function testFromArrayWithEmptyData()
    {
        $user = User::fromArray([]);

        $this->assertNull($user->getId());
        $this->assertNull($user->getUsername());
    }

    public function testToArrayRoundTrip()
    {
        $data = [
            'id'               => 42,
            'username'         => 'test',
            'phone'            => '+79001234567',
            'email'            => null,
            'first_name'       => null,
            'last_name'        => null,
            'provider_uid'     => '999',
            'is_active'        => true,
            'is_provider_free' => false,
            'created_at'       => null,
        ];

        $user = User::fromArray($data);
        $output = $user->toArray();

        $this->assertEquals($data, $output);
    }

    public function testCollectionCreatesMultipleUsers()
    {
        $items = [
            ['id' => 1, 'username' => 'user1'],
            ['id' => 2, 'username' => 'user2'],
            ['id' => 3, 'username' => 'user3'],
        ];

        $users = User::collection($items);

        $this->assertCount(3, $users);
        $this->assertEquals(1, $users[0]->getId());
        $this->assertEquals('user2', $users[1]->getUsername());
        $this->assertEquals(3, $users[2]->getId());
    }

    public function testCollectionSkipsNonArrayItems()
    {
        $items = [
            ['id' => 1],
            'not_an_array',
            ['id' => 2],
        ];

        $users = User::collection($items);
        $this->assertCount(2, $users);
    }

    public function testGetRawDataReturnsOriginal()
    {
        $data = ['id' => 42, 'username' => 'test', 'extra_field' => 'value'];
        $user = User::fromArray($data);

        $raw = $user->getRawData();
        $this->assertEquals($data, $raw);
        $this->assertEquals('value', $raw['extra_field']);
    }

    public function testInactiveUser()
    {
        $user = User::fromArray(['id' => 1, 'is_active' => false]);
        $this->assertFalse($user->isActive());
    }

    public function testProviderFreeUser()
    {
        $user = User::fromArray(['id' => 1, 'is_provider_free' => true]);
        $this->assertTrue($user->isProviderFree());
    }
}
