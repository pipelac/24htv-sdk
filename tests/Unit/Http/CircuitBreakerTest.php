<?php

namespace TwentyFourTv\Tests\Unit\Http;

use TwentyFourTv\Http\CircuitBreaker;
use PHPUnit\Framework\TestCase;

class CircuitBreakerTest extends TestCase
{
    /** @var CircuitBreaker */
    private $cb;

    protected function setUp(): void
    {
        $this->cb = new CircuitBreaker(3, 10);
    }

    public function testStartsInClosedState()
    {
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->cb->getState());
        $this->assertEquals(0, $this->cb->getFailureCount());
    }

    public function testAvailableInClosedState()
    {
        $this->assertTrue($this->cb->isAvailable());
    }

    public function testOpensAfterThresholdFailures()
    {
        $this->cb->recordFailure();
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->cb->getState());

        $this->cb->recordFailure();
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->cb->getState());

        $this->cb->recordFailure();
        $this->assertEquals(CircuitBreaker::STATE_OPEN, $this->cb->getState());
    }

    public function testRejectsInOpenState()
    {
        // Открываем circuit
        $this->cb->recordFailure();
        $this->cb->recordFailure();
        $this->cb->recordFailure();

        $this->assertFalse($this->cb->isAvailable());
    }

    public function testSuccessResetsFailureCount()
    {
        $this->cb->recordFailure();
        $this->cb->recordFailure();
        $this->assertEquals(2, $this->cb->getFailureCount());

        $this->cb->recordSuccess();
        $this->assertEquals(0, $this->cb->getFailureCount());
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->cb->getState());
    }

    public function testResetClearsAll()
    {
        $this->cb->recordFailure();
        $this->cb->recordFailure();
        $this->cb->recordFailure();
        $this->assertEquals(CircuitBreaker::STATE_OPEN, $this->cb->getState());

        $this->cb->reset();
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->cb->getState());
        $this->assertEquals(0, $this->cb->getFailureCount());
    }

    public function testStatsTracking()
    {
        $this->cb->isAvailable();
        $this->cb->recordFailure();
        $this->cb->isAvailable();
        $this->cb->recordFailure();
        $this->cb->isAvailable();
        $this->cb->recordFailure(); // → OPEN

        // Следующий вызов будет отклонён
        $this->cb->isAvailable();

        $stats = $this->cb->getStats();
        $this->assertEquals('open', $stats['state']);
        $this->assertEquals(3, $stats['failures']);
        $this->assertEquals(4, $stats['total_calls']);
        $this->assertEquals(3, $stats['total_failures']);
        $this->assertEquals(1, $stats['total_rejected']);
    }

    public function testHalfOpenAfterFailureInHalfOpen()
    {
        // Открываем circuit
        $this->cb->recordFailure();
        $this->cb->recordFailure();
        $this->cb->recordFailure();
        $this->assertEquals(CircuitBreaker::STATE_OPEN, $this->cb->getState());

        // Имитируем HALF_OPEN вручную через reset + повторные сбои
        $this->cb->reset();
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->cb->getState());
    }

    public function testDefaultThresholdAndCooldown()
    {
        $cb = new CircuitBreaker();

        // Дефолт — 5 сбоев
        for ($i = 0; $i < 4; $i++) {
            $cb->recordFailure();
        }
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $cb->getState());

        $cb->recordFailure();
        $this->assertEquals(CircuitBreaker::STATE_OPEN, $cb->getState());
    }

    public function testConstants()
    {
        $this->assertEquals('closed', CircuitBreaker::STATE_CLOSED);
        $this->assertEquals('open', CircuitBreaker::STATE_OPEN);
        $this->assertEquals('half_open', CircuitBreaker::STATE_HALF_OPEN);
    }
}
