<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testFromArrayCreatesSubscription()
    {
        $data = [
            'id'        => 'sub-uuid-123',
            'packet_id' => 80,
            'start_at'  => '2026-03-01T00:00:00.000Z',
            'end_at'    => '2026-04-01T00:00:00.000Z',
            'renew'     => true,
            'is_paused' => false,
            'status'    => 'active',
            'packet'    => ['id' => 80, 'name' => 'Базовый'],
        ];

        $sub = Subscription::fromArray($data);

        $this->assertEquals('sub-uuid-123', $sub->getId());
        $this->assertEquals(80, $sub->getPacketId());
        $this->assertEquals('2026-03-01T00:00:00.000Z', $sub->getStartAt());
        $this->assertEquals('2026-04-01T00:00:00.000Z', $sub->getEndAt());
        $this->assertTrue($sub->isRenew());
        $this->assertFalse($sub->isPaused());
        $this->assertEquals('active', $sub->getStatus());
        $this->assertNotNull($sub->getPacket());
    }

    public function testFromArrayPausedSubscription()
    {
        $sub = Subscription::fromArray([
            'id'        => 'sub-2',
            'packet_id' => 90,
            'renew'     => false,
            'is_paused' => true,
            'status'    => 'paused',
        ]);

        $this->assertFalse($sub->isRenew());
        $this->assertTrue($sub->isPaused());
        $this->assertEquals('paused', $sub->getStatus());
    }

    public function testToArrayRoundTrip()
    {
        $data = [
            'id'        => 'sub-1',
            'packet_id' => 80,
            'start_at'  => '2026-03-01T00:00:00.000Z',
            'end_at'    => null,
            'renew'     => true,
            'is_paused' => false,
            'status'    => 'active',
            'packet'    => null,
        ];

        $sub = Subscription::fromArray($data);
        $this->assertEquals($data, $sub->toArray());
    }

    public function testCollectionCreatesSubscriptions()
    {
        $items = [
            ['id' => 'sub-1', 'packet_id' => 80],
            ['id' => 'sub-2', 'packet_id' => 90],
        ];

        $subs = Subscription::collection($items);

        $this->assertCount(2, $subs);
        $this->assertEquals('sub-1', $subs[0]->getId());
        $this->assertEquals(90, $subs[1]->getPacketId());
    }
}
