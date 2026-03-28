<?php

namespace TwentyFourTv;

use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\LoggerInterface;
use TwentyFourTv\Exception\ConfigException;
use TwentyFourTv\Util\TokenMasker;

/**
 * Конфигурация SDK 24часаТВ
 *
 * Загружает параметры из INI-файла с поддержкой секций.
 * Поддерживает подстановку ENV-переменных в формате ${ENV_NAME}.
 *
 * <code>
 * $config = new Config('/path/to/24htv.ini');
 * $token  = $config->getToken();
 * $url    = $config->getBaseUrl();
 * $custom = $config->getOrDefault('billing.timeout', 30);
 * </code>
 *
 * @see docs/configuration.md
 * @since 1.0.0
 */
class Config implements ConfigInterface
{
    /** @var string Путь по умолчанию к INI-файлу */
    public const DEFAULT_CONFIG_PATH = 'cfg/24htv.ini';

    /** @var string Base URL API по умолчанию */
    public const DEFAULT_BASE_URL = 'https://provapi.24h.tv/v2';

    /** @var int Таймаут запроса по умолчанию (сек) */
    public const DEFAULT_TIMEOUT = 10;

    /** @var int Таймаут соединения по умолчанию (сек) */
    public const DEFAULT_CONNECT_TIMEOUT = 5;

    /** @var int Макс. количество повторов запроса по умолчанию */
    public const DEFAULT_MAX_RETRIES = 2;

    /** @var array Загруженные данные конфигурации */
    private $data = [];

    /** @var LoggerInterface|null Логгер */
    private $logger;

    /**
     * @param string|null          $configPath Путь к INI-файлу (null — путь по умолчанию)
     * @param LoggerInterface|null $logger     Логгер
     *
     * @throws ConfigException Если файл не найден или содержит ошибки
     */
    public function __construct($configPath = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        if ($configPath === null) {
            $configPath = self::DEFAULT_CONFIG_PATH;
        }

        if (!file_exists($configPath)) {
            throw new ConfigException(
                "Файл конфигурации не найден: {$configPath}"
            );
        }

        $parsed = parse_ini_file($configPath, true);
        if ($parsed === false) {
            throw new ConfigException(
                "Ошибка парсинга файла конфигурации: {$configPath}"
            );
        }

        $this->data = $this->resolveEnvVariables($parsed);
        $this->validate();

        if ($this->logger) {
            $this->logger->info('24HTV: конфигурация загружена', [
                'path' => $configPath,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $parts = explode('.', $key, 2);

        if (count($parts) === 2) {
            $section = $parts[0];
            $param = $parts[1];
            if (isset($this->data[$section][$param])) {
                return $this->data[$section][$param];
            }
        } elseif (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new ConfigException(
            "Параметр конфигурации не найден: {$key}"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOrDefault($key, $default = null)
    {
        try {
            return $this->get($key);
        } catch (ConfigException $e) {
            return $default;
        }
    }

    /**
     * Получить целый раздел конфигурации
     *
     * @param string $section Имя секции
     *
     * @throws ConfigException Если секция не найдена
     *
     * @return array
     */
    public function getSection($section)
    {
        if (isset($this->data[$section]) && is_array($this->data[$section])) {
            return $this->data[$section];
        }

        throw new ConfigException(
            "Секция конфигурации не найдена: {$section}"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->get('api.token');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->getOrDefault('api.base_url', self::DEFAULT_BASE_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout()
    {
        return (int) $this->getOrDefault('api.timeout', self::DEFAULT_TIMEOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectTimeout()
    {
        return (int) $this->getOrDefault('api.connect_timeout', self::DEFAULT_CONNECT_TIMEOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderId()
    {
        return $this->getOrDefault('provider.id', '');
    }

    /**
     * Получить максимальное количество повторов запроса
     *
     * @return int
     */
    public function getMaxRetries()
    {
        return (int) $this->getOrDefault('api.max_retries', self::DEFAULT_MAX_RETRIES);
    }

    /**
     * Получить целое число из конфигурации
     *
     * @param string $key
     * @param int    $default
     *
     * @return int
     */
    public function getInt($key, $default = 0)
    {
        return (int) $this->getOrDefault($key, $default);
    }

    /**
     * Получить строковое значение из конфигурации
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function getString($key, $default = '')
    {
        return (string) $this->getOrDefault($key, $default);
    }

    /**
     * Получить булево значение из конфигурации
     *
     * @param string $key
     * @param bool   $default
     *
     * @return bool
     */
    public function getBool($key, $default = false)
    {
        $value = $this->getOrDefault($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Подставить ENV-переменные вида ${VAR_NAME} в значения конфигурации
     *
     * @param array $data
     *
     * @return array
     */
    private function resolveEnvVariables(array $data)
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->resolveEnvVariables($value);
            } elseif (is_string($value) && preg_match('/^\$\{([A-Z0-9_]+)\}$/', $value, $matches)) {
                $envValue = getenv($matches[1]);
                $result[$key] = ($envValue !== false) ? $envValue : $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Валидация обязательных параметров конфигурации
     *
     * @throws ConfigException Если обязательные параметры отсутствуют
     */
    private function validate()
    {
        $token = $this->getOrDefault('api.token', null);
        if ($token === null || $token === '') {
            throw new ConfigException(
                "Обязательный параметр 'api.token' не указан в конфигурации"
            );
        }
    }

    /**
     * Получить всю конфигурацию в виде массива (для отладки)
     *
     * Токен API маскируется для безопасности вывода.
     *
     * @return array
     */
    public function toArray()
    {
        $safe = $this->data;

        // Маскирование токена через единую утилиту
        if (isset($safe['api']['token'])) {
            $safe['api']['token'] = TokenMasker::mask($safe['api']['token']);
        }

        return $safe;
    }
}
