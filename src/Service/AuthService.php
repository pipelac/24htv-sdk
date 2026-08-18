<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\AuthServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;

/**
 * Аутентификация пользователей по токену провайдера
 *
 * @since 1.0.0
 */
class AuthService extends AbstractService implements AuthServiceInterface
{
    /**
     * Получить access_token пользователя
     *
     * @param array $data ['user_id' => int, 'username' => string, 'phone' => string, 'all_providers' => bool]
     *
     * @throws TwentyFourTvException
     *
     * @return array ['access_token' => string, 'expires' => string]
     */
    public function getProviderToken(array $data)
    {
        $this->log('info', '24HTV: аутентификация по токену провайдера', [
            'user_id'  => isset($data['user_id']) ? $data['user_id'] : null,
            'username' => isset($data['username']) ? $data['username'] : null,
        ]);

        return $this->api->apiPost(ApiEndpoints::AUTH_PROVIDER, $data);
    }
}
