<?php

namespace TwentyFourTv\Contract;

/**
 * Контракт резолвера авторизации абонента
 *
 * Реализуйте этот интерфейс для интеграции с вашим биллингом.
 * SDK включает готовую реализацию для UTM5: {@see \TwentyFourTv\Resolver\UtmAuthResolver}
 *
 * <code>
 * class MyBillingAuthResolver implements AuthResolverInterface
 * {
 *     public function __invoke(array $params) {
 *         $ip = isset($params['ip']) ? $params['ip'] : null;
 *         $user = $this->billing->findByIp($ip);
 *         if ($user) {
 *             return ['result' => 'success', 'provider_uid' => $user['id']];
 *         }
 *         return ['result' => 'error', 'errmsg' => 'User not found'];
 *     }
 * }
 * </code>
 *
 * @since 1.1.0
 */
interface AuthResolverInterface
{
    /**
     * Определить абонента по входящим параметрам callback AUTH
     *
     * @param array $params GET-параметры запроса (ip, phone, provider_uid)
     *
     * @return array ['result' => 'success', 'provider_uid' => '...']
     *               или ['result' => 'error', 'errmsg' => '...']
     */
    public function __invoke(array $params);
}
