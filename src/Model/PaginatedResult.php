<?php

namespace TwentyFourTv\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * DTO результата с пагинацией
 *
 * <code>
 * $result = PaginatedResult::fromResponse($apiResponse, 10, 0);
 * foreach ($result->getItems() as $item) { ... }
 * if ($result->hasMore()) {
 *     $next = $result->getNextOffset();
 * }
 * </code>
 *
 * @since 1.0.0
 */
final class PaginatedResult implements Countable, IteratorAggregate
{
    /** @var array Элементы текущей страницы */
    private $items;

    /** @var int|null Общее количество элементов */
    private $total;

    /** @var int Лимит на страницу */
    private $limit;

    /** @var int Текущее смещение */
    private $offset;

    /**
     * @param array    $items  Элементы
     * @param int|null $total  Общее количество (если известно)
     * @param int      $limit  Лимит
     * @param int      $offset Смещение
     */
    public function __construct(array $items, $total = null, $limit = 100, $offset = 0)
    {
        $this->items = $items;
        $this->total = $total;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Фабрика: создать из ответа API
     *
     * @param array $response Ответ API (может содержать items, total, или быть просто массивом)
     * @param int   $limit
     * @param int   $offset
     *
     * @return self
     */
    public static function fromResponse(array $response, $limit = 100, $offset = 0)
    {
        // API может вернуть {'items': [...], 'total': N} или просто массив
        if (isset($response['items']) && is_array($response['items'])) {
            $items = $response['items'];
            $total = isset($response['total']) ? (int) $response['total'] : null;
        } else {
            $items = $response;
            $total = null;
        }

        return new self($items, $total, $limit, $offset);
    }

    /**
     * @return array Элементы текущей страницы
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int|null Общее количество элементов
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int Лимит на страницу
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int Текущее смещение
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Есть ли ещё элементы для загрузки
     *
     * @return bool
     */
    public function hasMore()
    {
        if ($this->total !== null) {
            return ($this->offset + count($this->items)) < $this->total;
        }

        // Если total неизвестен — проверяем что получили полную страницу
        return count($this->items) >= $this->limit;
    }

    /**
     * Получить offset для следующей страницы
     *
     * @return int
     */
    public function getNextOffset()
    {
        return $this->offset + count($this->items);
    }

    /**
     * Количество элементов на текущей странице
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->items);
    }

    /**
     * Пустой ли результат
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Получить итератор элементов (для foreach)
     *
     * @return ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
