<?php

namespace TwentyFourTv\Model;

/**
 * Базовый абстрактный DTO
 *
 * Все модели SDK наследуют от этого класса.
 * Предоставляет общую логику создания из ассоциативного массива (fromArray)
 * и обратной сериализации (toArray).
 *
 * @internal Не предназначен для наследования пользователями SDK
 *
 * <code>
 * $user = User::fromArray(['id' => 42, 'username' => 'test']);
 * echo $user->getId();        // 42
 * print_r($user->toArray());  // ['id' => 42, 'username' => 'test', ...]
 * </code>
 *
 * @since 1.0.0
 */
abstract class AbstractModel
{
    /** @var array Исходные данные от API */
    protected $rawData;

    /**
     * @param array $data Данные от API
     */
    public function __construct(array $data = [])
    {
        $this->rawData = $data;
        $this->hydrate($data);
    }

    /**
     * Фабрика: создать модель из ассоциативного массива
     *
     * @param array $data
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        return new static($data);
    }

    /**
     * Фабрика: создать коллекцию моделей из массива массивов
     *
     * @param array $items Массив ассоциативных массивов
     *
     * @return static[]
     */
    public static function collection(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $result[] = static::fromArray($item);
            }
        }

        return $result;
    }

    /**
     * Сериализовать модель обратно в массив
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Получить исходные данные от API
     *
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Заполнить свойства из массива данных
     *
     * @param array $data
     *
     * @return void
     */
    abstract protected function hydrate(array $data);

    /**
     * Безопасно извлечь значение из массива
     *
     * @param array  $data
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function get(array $data, $key, $default = null)
    {
        return isset($data[$key]) ? $data[$key] : $default;
    }
}
