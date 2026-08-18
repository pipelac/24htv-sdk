<?php

namespace TwentyFourTv\Contract;

/**
 * Контракт резолвера баланса абонента
 *
 * Реализуйте этот интерфейс для интеграции с вашим биллингом.
 * SDK включает готовую реализацию для UTM5: {@see \TwentyFourTv\Resolver\UtmBalanceResolver}
 *
 * <code>
 * class MyBillingBalanceResolver implements BalanceResolverInterface
 * {
 *     public function __invoke(array $params) {
 *         $uid = isset($params['provider_uid']) ? $params['provider_uid'] : null;
 *         $balance = $this->billing->getBalance($uid);
 *         if ($balance !== null) {
 *             return ['result' => 'success', 'balance' => (string) $balance];
 *         }
 *         return ['result' => 'error', 'errmsg' => 'Account not found'];
 *     }
 * }
 * </code>
 *
 * @since 1.1.0
 */
interface BalanceResolverInterface
{
    /**
     * Получить баланс абонента по входящим параметрам callback BALANCE
     *
     * @param array $params Объединённые GET-параметры и тело запроса
     *
     * @return array ['result' => 'success', 'balance' => '...']
     *               или ['result' => 'error', 'errmsg' => '...']
     */
    public function __invoke(array $params);
}
