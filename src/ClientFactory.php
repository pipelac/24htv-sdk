<?php

namespace TwentyFourTv;

use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\DatabaseInterface;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Contract\LoggerInterface;

/**
 * Фабрика для создания клиента SDK
 *
 * Предоставляет удобные статические методы для создания настроенного клиента.
 * По умолчанию использует встроенный HttpClient, но принимает любой HttpClientInterface.
 *
 * <code>
 * // Из INI-файла
 * $client = ClientFactory::create('/path/to/24htv.ini');
 *
 * // С логгером и БД
 * $client = ClientFactory::create('/path/to/24htv.ini', $logger, $db);
 *
 * // С кастомным HTTP-клиентом (Guzzle и др.)
 * $client = ClientFactory::create('/path/to/24htv.ini', $logger, null, $myHttpClient);
 *
 * // Из существующего Config
 * $client = ClientFactory::createFromConfig($config, $logger);
 * </code>
 *
 * @since 1.0.0
 */
class ClientFactory
{
    /**
     * Создать клиент из INI-файла
     *
     * @param string|null              $configPath Путь к INI-файлу (null — путь по умолчанию)
     * @param LoggerInterface|null     $logger     Логгер
     * @param DatabaseInterface|null   $db         Соединение с БД (для CallbackHandler)
     * @param HttpClientInterface|null $httpClient Кастомный HTTP-клиент (null = встроенный HttpClient)
     *
     * @return Client
     */
    public static function create($configPath = null, LoggerInterface $logger = null, DatabaseInterface $db = null, HttpClientInterface $httpClient = null)
    {
        $config = new Config($configPath, $logger);

        return self::buildClient($config, $logger, $db, $httpClient);
    }

    /**
     * Создать клиент из готового объекта конфигурации
     *
     * @param ConfigInterface          $config
     * @param LoggerInterface|null     $logger
     * @param DatabaseInterface|null   $db
     * @param HttpClientInterface|null $httpClient Кастомный HTTP-клиент (null = встроенный HttpClient)
     *
     * @return Client
     */
    public static function createFromConfig(ConfigInterface $config, LoggerInterface $logger = null, DatabaseInterface $db = null, HttpClientInterface $httpClient = null)
    {
        return self::buildClient($config, $logger, $db, $httpClient);
    }

    /**
     * Собирает зависимости и создаёт Client
     *
     * Если $httpClient не передан, создаёт встроенный HttpClient.
     *
     * @param ConfigInterface          $config     Конфигурация
     * @param LoggerInterface|null     $logger     Логгер
     * @param DatabaseInterface|null   $db         БД
     * @param HttpClientInterface|null $httpClient Кастомный HTTP-клиент
     *
     * @return Client
     */
    private static function buildClient(ConfigInterface $config, LoggerInterface $logger = null, DatabaseInterface $db = null, HttpClientInterface $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = new HttpClient($config, $logger);
        }

        return new Client($httpClient, $config, $logger, $db);
    }
}
