<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\BalanceServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;

/**
 * Управление балансом и платёжными аккаунтами 24часаТВ
 *
 * @since 1.0.0
 */
class BalanceService extends AbstractService implements BalanceServiceInterface
{
    /**
     * Установить отображаемый баланс пользователя
     *
     * @param int    $userId    ID пользователя в 24ТВ
     * @param string $billingId ID лицевого счёта в биллинге провайдера
     * @param string $amount    Значение баланса в рублях
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function set($userId, $billingId, $amount)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: установка баланса', [
            'userId'    => $userId,
            'billingId' => $billingId,
            'amount'    => $amount,
        ]);

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_PROVIDER_ACCOUNT, $userId), [
            'id'     => $billingId,
            'amount' => $amount,
        ]);
    }

    /**
     * Получить текущий отображаемый баланс
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function get($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_PROVIDER_ACCOUNT, $userId));
    }

    /**
     * Получить платёжные аккаунты провайдера для пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getProviderAccounts($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_PROVIDER_ACCOUNTS, $userId));
    }

    /**
     * Создать платёжные аккаунты провайдера
     *
     * @param int   $userId
     * @param array $accounts Массив аккаунтов [['id' => string, 'amount' => string], ...]
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function setProviderAccounts($userId, array $accounts)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_PROVIDER_ACCOUNTS, $userId), $accounts);
    }

    /**
     * Получить платёжные аккаунты пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getAccounts($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_ACCOUNTS, $userId));
    }

    /**
     * Создать платёжный аккаунт пользователя
     *
     * @param int   $userId
     * @param array $data
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function createAccount($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_ACCOUNTS, $userId), $data);
    }

    /**
     * Получить транзакции по аккаунту
     *
     * @param int    $userId
     * @param string $accountId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getTransactions($userId, $accountId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_ACCOUNT_TRANSACTIONS, $userId, $accountId));
    }

    /**
     * Создать транзакцию
     *
     * @param int    $userId
     * @param string $accountId
     * @param array  $data
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function createTransaction($userId, $accountId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_ACCOUNT_TRANSACTIONS, $userId, $accountId), $data);
    }

    /**
     * Получить платёжные источники
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPaymentSources()
    {
        return $this->api->apiGet(ApiEndpoints::PAYMENT_SOURCES);
    }

    /**
     * Получить список лицензий пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getEntityLicenses($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_ENTITY_LICENSES, $userId));
    }

    /**
     * Добавить лицензию пользователю
     *
     * @param int   $userId
     * @param array $data   ['entity_license_id' => int]
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function addEntityLicense($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_ENTITY_LICENSES, $userId), $data);
    }

    /**
     * Удалить лицензию пользователя
     *
     * @param int $userId
     * @param int $licenseId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function removeEntityLicense($userId, $licenseId)
    {
        $this->requireId($userId, 'userId');
        $this->requireId($licenseId, 'licenseId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_ENTITY_LICENSE_BY_ID, $userId, $licenseId));
    }
}
