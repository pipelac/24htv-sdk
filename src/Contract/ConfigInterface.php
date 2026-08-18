<?php

namespace TwentyFourTv\Contract;

/**
 * Контракт конфигурации SDK
 *
 * @since 1.0.0
 */
interface ConfigInterface
{
    /**
     * Получить значение по ключу вида "section.key"
     *
     * @param string $key
     *
     * @throws \RuntimeException Если ключ не найден
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Получить значение с дефолтом
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOrDefault($key, $default = null);

    /**
     * @return string API token
     */
    public function getToken();

    /**
     * @return string Base API URL (без trailing slash)
     */
    public function getBaseUrl();

    /**
     * @return int Таймаут запроса в секундах
     */
    public function getTimeout();

    /**
     * @return int Таймаут подключения в секундах
     */
    public function getConnectTimeout();

    /**
     * @return string ID провайдера
     */
    public function getProviderId();
}
