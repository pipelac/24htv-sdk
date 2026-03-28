<?php

namespace TwentyFourTv\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use TwentyFourTv\Model\Message;

class MessageTest extends TestCase
{
    public function testFromArray()
    {
        $data = [
            'id'         => 'msg-42',
            'title'      => 'Добро пожаловать',
            'text'       => 'Спасибо за регистрацию!',
            'created_at' => '2024-03-01T12:00:00+03:00',
            'is_read'    => false,
        ];

        $message = Message::fromArray($data);

        $this->assertEquals('msg-42', $message->getId());
        $this->assertEquals('Добро пожаловать', $message->getTitle());
        $this->assertEquals('Спасибо за регистрацию!', $message->getText());
        $this->assertEquals('2024-03-01T12:00:00+03:00', $message->getCreatedAt());
        $this->assertFalse($message->isRead());
    }

    public function testToArray()
    {
        $data = [
            'id'         => 'msg-42',
            'title'      => 'Test',
            'text'       => 'Body',
            'created_at' => '2024-01-01',
            'is_read'    => true,
        ];

        $message = Message::fromArray($data);
        $this->assertEquals($data, $message->toArray());
    }

    public function testEmptyData()
    {
        $message = Message::fromArray([]);

        $this->assertNull($message->getId());
        $this->assertNull($message->getTitle());
        $this->assertNull($message->getText());
        $this->assertNull($message->getCreatedAt());
        $this->assertFalse($message->isRead()); // default false
    }

    public function testIsReadTrue()
    {
        $message = Message::fromArray(['is_read' => true]);
        $this->assertTrue($message->isRead());
    }
}
