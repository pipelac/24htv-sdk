<?php

namespace TwentyFourTv\Tests\Unit\Model;

use TwentyFourTv\Model\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testFromArray()
    {
        $data = [
            'id'          => 'txn-001',
            'amount'      => '-199.00',
            'type'        => 'payment',
            'description' => 'Оплата подписки',
            'created_at'  => '2024-03-15T14:30:00+03:00',
        ];

        $txn = Transaction::fromArray($data);

        $this->assertEquals('txn-001', $txn->getId());
        $this->assertEquals('-199.00', $txn->getAmount());
        $this->assertEquals('payment', $txn->getType());
        $this->assertEquals('Оплата подписки', $txn->getDescription());
        $this->assertEquals('2024-03-15T14:30:00+03:00', $txn->getCreatedAt());
    }

    public function testToArray()
    {
        $data = [
            'id'          => 'txn-001',
            'amount'      => '-199.00',
            'type'        => 'payment',
            'description' => 'Оплата подписки',
            'created_at'  => '2024-03-15T14:30:00+03:00',
        ];

        $txn = Transaction::fromArray($data);
        $this->assertEquals($data, $txn->toArray());
    }

    public function testEmptyData()
    {
        $txn = Transaction::fromArray([]);

        $this->assertNull($txn->getId());
        $this->assertNull($txn->getAmount());
        $this->assertNull($txn->getType());
        $this->assertNull($txn->getDescription());
        $this->assertNull($txn->getCreatedAt());
    }

    public function testCollection()
    {
        $items = [
            ['id' => '1', 'amount' => '100', 'type' => 'credit'],
            ['id' => '2', 'amount' => '-50', 'type' => 'debit'],
        ];

        $collection = Transaction::collection($items);

        $this->assertCount(2, $collection);
        $this->assertInstanceOf('TwentyFourTv\Model\Transaction', $collection[0]);
        $this->assertEquals('credit', $collection[0]->getType());
        $this->assertEquals('debit', $collection[1]->getType());
    }
}
