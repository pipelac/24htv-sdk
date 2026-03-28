<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Contract\LoggerInterface;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Model\AbstractModel;

/**
 * Базовый абстрактный класс для всех сервисов SDK
 *
 * Объединяет общую логику: хранение HTTP-клиента, логгера,
 * валидацию обязательных полей, логирование, гидрацию моделей.
 *
 * <code>
 * class UserService extends AbstractService
 * {
 *     public function register(array $data) {
 *         $this->requireFields($data, ['username', 'phone']);
 *         return $this->createModel(User::class,
 *             $this->api->apiPost('/users', $data));
 *     }
 * }
 * </code>
 *
 * @since 1.0.0
 */
abstract class AbstractService
{
    /** @var HttpClientInterface */
    protected $api;

    /** @var LoggerInterface|null */
    protected $logger;

    /**
     * @param HttpClientInterface  $api    HTTP-клиент для обращения к API
     * @param LoggerInterface|null $logger Логгер (необязательный)
     */
    public function __construct(HttpClientInterface $api, LoggerInterface $logger = null)
    {
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     * Логирование с проверкой наличия логгера
     *
     * @param string $level   Уровень логирования (info, warning, error, debug)
     * @param string $message Сообщение
     * @param array  $context Контекстные данные
     *
     * @return void
     */
    protected function log($level, $message, array $context = [])
    {
        if ($this->logger !== null) {
            $this->logger->{$level}($message, $context);
        }
    }

    /**
     * Проверить наличие обязательных полей в массиве данных
     *
     * @param array    $data   Данные для проверки
     * @param string[] $fields Список обязательных полей
     *
     * @throws ValidationException Если хотя бы одно обязательное поле отсутствует
     *
     * @return void
     */
    protected function requireFields(array $data, array $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new ValidationException(
                    "Обязательное поле '{$field}' не заполнено",
                    0,
                    null,
                    null
                );
            }
        }
    }

    /**
     * Проверить что значение является корректным числовым идентификатором
     *
     * @param mixed  $value Значение для проверки
     * @param string $name  Имя параметра (для сообщения об ошибке)
     *
     * @throws ValidationException Если значение не является числом
     *
     * @return void
     */
    protected function requireId($value, $name = 'id')
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            throw new ValidationException(
                "Невалидное значение для '{$name}': " . var_export($value, true),
                0,
                null,
                null
            );
        }
    }

    /**
     * Создать типизированную модель из ответа API
     *
     * @param string     $modelClass FQCN класса модели (наследника AbstractModel)
     * @param array|null $data       Данные от API
     *
     * @return AbstractModel
     */
    protected function createModel($modelClass, $data)
    {
        if (!is_array($data)) {
            $data = [];
        }

        return $modelClass::fromArray($data);
    }

    /**
     * Создать коллекцию типизированных моделей из массива ответов API
     *
     * @param string $modelClass FQCN класса модели (наследника AbstractModel)
     * @param array  $items      Массив данных от API
     *
     * @return AbstractModel[]
     */
    protected function createCollection($modelClass, $items)
    {
        if (!is_array($items)) {
            return [];
        }

        return $modelClass::collection($items);
    }
}
