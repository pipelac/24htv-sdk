<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\Device;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    public function testFromArray()
    {
        $data = [
            'id'            => 'dev-001',
            'serial'        => 'ABC123',
            'device_type'   => 'stb',
            'interface_mac' => '00:11:22:33:44:55',
            'provider_uid'  => 'provider-42',
            'created_at'    => '2024-01-15T10:30:00+03:00',
            'last_login_at' => '2024-03-01T12:00:00+03:00',
        ];

        $device = Device::fromArray($data);

        $this->assertEquals('dev-001', $device->getId());
        $this->assertEquals('ABC123', $device->getSerial());
        $this->assertEquals('stb', $device->getDeviceType());
        $this->assertEquals('00:11:22:33:44:55', $device->getInterfaceMac());
        $this->assertEquals('provider-42', $device->getProviderUid());
        $this->assertEquals('2024-01-15T10:30:00+03:00', $device->getCreatedAt());
        $this->assertEquals('2024-03-01T12:00:00+03:00', $device->getLastLoginAt());
    }

    public function testToArray()
    {
        $data = [
            'id'            => 'dev-001',
            'serial'        => 'ABC123',
            'device_type'   => 'stb',
            'interface_mac' => '00:11:22:33:44:55',
            'provider_uid'  => 'provider-42',
            'created_at'    => '2024-01-15T10:30:00+03:00',
            'last_login_at' => '2024-03-01T12:00:00+03:00',
        ];

        $device = Device::fromArray($data);
        $this->assertEquals($data, $device->toArray());
    }

    public function testEmptyData()
    {
        $device = Device::fromArray([]);

        $this->assertNull($device->getId());
        $this->assertNull($device->getSerial());
        $this->assertNull($device->getDeviceType());
        $this->assertNull($device->getInterfaceMac());
    }

    public function testCollection()
    {
        $items = [
            ['id' => 'dev-1', 'serial' => 'S1'],
            ['id' => 'dev-2', 'serial' => 'S2'],
        ];

        $collection = Device::collection($items);

        $this->assertCount(2, $collection);
        $this->assertInstanceOf(Device::class, $collection[0]);
        $this->assertEquals('dev-1', $collection[0]->getId());
    }
}
