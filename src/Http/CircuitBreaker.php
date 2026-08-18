<?php

namespace TwentyFourTv\Http;

/**
 * Circuit Breaker — защита от каскадных сбоев при недоступности API
 *
 * Реализует три состояния:
 * - CLOSED: запросы проходят нормально, сбои считаются
 * - OPEN: запросы сразу отклоняются, ожидание cooldown
 * - HALF_OPEN: пробный запрос — при успехе → CLOSED, при ошибке → OPEN
 *
 * <code>
 * $cb = new CircuitBreaker(5, 30); // 5 сбоев → открытие на 30 сек
 * if ($cb->isAvailable()) {
 *     try {
 *         $result = makeRequest();
 *         $cb->recordSuccess();
 *     } catch (\Exception $e) {
 *         $cb->recordFailure();
 *         throw $e;
 *     }
 * }
 * </code>
 *
 * @since 1.0.0
 */
class CircuitBreaker
{
    /** @var string Состояние: запросы проходят */
    const STATE_CLOSED = 'closed';

    /** @var string Состояние: запросы блокируются */
    const STATE_OPEN = 'open';

    /** @var string Состояние: пробный запрос */
    const STATE_HALF_OPEN = 'half_open';

    /** @var int Порог количества сбоев до OPEN */
    private $failureThreshold;

    /** @var int Время ожидания в секундах перед HALF_OPEN */
    private $cooldownSeconds;

    /** @var string Текущее состояние */
    private $state;

    /** @var int Счётчик последовательных сбоев */
    private $failureCount = 0;

    /** @var int|null Timestamp последнего сбоя */
    private $lastFailureTime;

    /** @var int Общее количество вызовов */
    private $totalCalls = 0;

    /** @var int Общее количество сбоев */
    private $totalFailures = 0;

    /** @var int Общее количество отклонённых вызовов (OPEN) */
    private $totalRejected = 0;

    /**
     * @param int $failureThreshold Количество последовательных сбоев до OPEN (по умолчанию 5)
     * @param int $cooldownSeconds  Время ожидания перед HALF_OPEN (по умолчанию 30)
     */
    public function __construct($failureThreshold = 5, $cooldownSeconds = 30)
    {
        $this->failureThreshold = (int) $failureThreshold;
        $this->cooldownSeconds = (int) $cooldownSeconds;
        $this->state = self::STATE_CLOSED;
        $this->lastFailureTime = null;
    }

    /**
     * Проверить, доступен ли Circuit Breaker для запроса
     *
     * @return bool true если запрос можно выполнить
     */
    public function isAvailable()
    {
        $this->totalCalls++;

        if ($this->state === self::STATE_CLOSED) {
            return true;
        }

        if ($this->state === self::STATE_OPEN) {
            // Проверяем, прошёл ли cooldown
            if ($this->lastFailureTime !== null && (time() - $this->lastFailureTime) >= $this->cooldownSeconds) {
                $this->state = self::STATE_HALF_OPEN;

                return true;
            }

            $this->totalRejected++;

            return false;
        }

        // HALF_OPEN — разрешаем один пробный запрос
        return true;
    }

    /**
     * Зарегистрировать успешный запрос → сброс в CLOSED
     *
     * @return void
     */
    public function recordSuccess()
    {
        $this->failureCount = 0;
        $this->state = self::STATE_CLOSED;
    }

    /**
     * Зарегистрировать неудачный запрос → инкремент счётчика
     *
     * @return void
     */
    public function recordFailure()
    {
        $this->failureCount++;
        $this->totalFailures++;
        $this->lastFailureTime = time();

        if ($this->state === self::STATE_HALF_OPEN) {
            // Пробный запрос не удался → обратно в OPEN
            $this->state = self::STATE_OPEN;

            return;
        }

        if ($this->failureCount >= $this->failureThreshold) {
            $this->state = self::STATE_OPEN;
        }
    }

    /**
     * Получить текущее состояние
     *
     * @return string 'closed', 'open' или 'half_open'
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Получить текущий счётчик последовательных сбоев
     *
     * @return int
     */
    public function getFailureCount()
    {
        return $this->failureCount;
    }

    /**
     * Получить статистику
     *
     * @return array ['state' => string, 'failures' => int, 'total_calls' => int, 'total_failures' => int, 'total_rejected' => int]
     */
    public function getStats()
    {
        return [
            'state'          => $this->state,
            'failures'       => $this->failureCount,
            'total_calls'    => $this->totalCalls,
            'total_failures' => $this->totalFailures,
            'total_rejected' => $this->totalRejected,
        ];
    }

    /**
     * Сбросить Circuit Breaker в начальное состояние
     *
     * @return void
     */
    public function reset()
    {
        $this->state = self::STATE_CLOSED;
        $this->failureCount = 0;
        $this->lastFailureTime = null;
        $this->totalCalls = 0;
        $this->totalFailures = 0;
        $this->totalRejected = 0;
    }
}
