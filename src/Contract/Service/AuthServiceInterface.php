<?php

namespace TwentyFourTv\Contract\Service;

/**
 * Контракт сервиса аутентификации
 *
 * @since 1.0.0
 */
interface AuthServiceInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function getProviderToken(array $data);
}
