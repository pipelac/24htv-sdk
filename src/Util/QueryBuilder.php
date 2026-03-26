<?php

namespace TwentyFourTv\Util;

/**
 * Fluent Builder для построения query-параметров API запросов
 *
 * Упрощает работу с комплексными фильтрами, пагинацией и сортировкой.
 *
 * <code>
 * $query = QueryBuilder::create()
 *     ->limit(20)
 *     ->offset(0)
 *     ->search('test')
 *     ->include('channels', 'availables')
 *     ->orderBy('name', 'asc')
 *     ->where('is_base', 'true')
 *     ->toArray();
 *
 * $packets = $client->packets()->getAll($query);
 * </code>
 *
 * @since 1.0.0
 */
class QueryBuilder
{
    /** @var array */
    private $params = [];

    /**
     * Фабричный метод для fluent API
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Установить лимит записей
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->params['limit'] = (int) $limit;

        return $this;
    }

    /**
     * Установить смещение записей
     *
     * @param int $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->params['offset'] = (int) $offset;

        return $this;
    }

    /**
     * Пагинация: страница + размер
     *
     * @param int $page     Номер страницы (1-based)
     * @param int $pageSize Размер страницы
     *
     * @return $this
     */
    public function page($page, $pageSize = 20)
    {
        $this->params['limit'] = (int) $pageSize;
        $this->params['offset'] = ((int) $page - 1) * (int) $pageSize;

        return $this;
    }

    /**
     * Текстовый поиск
     *
     * @param string $query
     *
     * @return $this
     */
    public function search($query)
    {
        $this->params['search'] = $query;

        return $this;
    }

    /**
     * Включить связанные сущности (includes)
     *
     * @param string ...$includes 'channels', 'availables', 'purchases', и т.д.
     *
     * @return $this
     */
    public function includes($includes)
    {
        $args = is_array($includes) ? $includes : func_get_args();
        $existing = isset($this->params['includes']) ? explode(',', $this->params['includes']) : [];
        $merged = array_unique(array_merge($existing, $args));
        $this->params['includes'] = implode(',', $merged);

        return $this;
    }

    /**
     * Сортировка
     *
     * @param string $field     Поле сортировки
     * @param string $direction 'asc' или 'desc'
     *
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->params['order'] = $field . ':' . $direction;

        return $this;
    }

    /**
     * Добавить произвольный параметр
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function where($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Установить типы подписок (для SubscriptionService)
     *
     * @param string $types 'active', 'paused', 'planned'
     *
     * @return $this
     */
    public function types($types)
    {
        $this->params['types'] = $types;

        return $this;
    }

    /**
     * Установить дату (для расписаний)
     *
     * @param string $date Дата в формате YYYY-MM-DD
     *
     * @return $this
     */
    public function date($date)
    {
        $this->params['date'] = $date;

        return $this;
    }

    /**
     * Установить диапазон timestamp (для расписаний)
     *
     * @param int $start Unix timestamp начала
     * @param int $end   Unix timestamp окончания
     *
     * @return $this
     */
    public function timeRange($start, $end)
    {
        $this->params['start'] = (int) $start;
        $this->params['end'] = (int) $end;

        return $this;
    }

    /**
     * Фильтр по провайдеру UID
     *
     * @param string $providerUid
     *
     * @return $this
     */
    public function providerUid($providerUid)
    {
        $this->params['provider_uid'] = $providerUid;

        return $this;
    }

    /**
     * Проверить, есть ли параметр
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Получить значение параметра
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    /**
     * Собрать массив query-параметров
     *
     * @return array
     */
    public function toArray()
    {
        return $this->params;
    }

    /**
     * Сбросить все параметры
     *
     * @return $this
     */
    public function reset()
    {
        $this->params = [];

        return $this;
    }
}
