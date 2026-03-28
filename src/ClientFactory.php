<?php

namespace TwentyFourTv;

use TwentyFourTv\Contract\LoggerInterface;

/**
 * Фабрика для создания полностью настроенного Client
 *
 * <code>
 * $client = ClientFactory::create('/path/to/24htv.ini', $logger);
 * $user   = $client->users()->getById(42);
 * </code>
 *
 * @since 1.0.0
 */
class ClientFactory
{
    /**
     * Создать Client из конфигурационного файла
     *
     * @param string               $configPath Путь к INI-файлу конфигурации
     * @param LoggerInterface|null $logger     Логгер (опционально)
     *
     * @throws \TwentyFourTv\Exception\ConfigException При ошибке загрузки конфигурации
     *
     * @return Client Полностью настроенный SDK-клиент
     */
    public static function create($configPath = null, LoggerInterface $logger = null)
    {
        $config = new Config($configPath);
        $httpClient = new HttpClient($config, $logger);

        return new Client($httpClient, $config, $logger);
    }

}
