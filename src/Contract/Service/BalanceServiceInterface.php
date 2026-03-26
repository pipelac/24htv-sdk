<?php

namespace TwentyFourTv\Contract\Service;

/**
 * Контракт сервиса управления балансом
 *
 * @since 1.0.0
 */
interface BalanceServiceInterface
{
    /** @return array */
    public function set($userId, $billingId, $amount);

    /** @return array */
    public function get($userId);

    /** @return array */
    public function getProviderAccounts($userId);

    /** @return array */
    public function setProviderAccounts($userId, array $accounts);

    /** @return array */
    public function getAccounts($userId);

    /** @return array */
    public function createAccount($userId, array $data);

    /** @return array */
    public function getTransactions($userId, $accountId);

    /** @return array */
    public function createTransaction($userId, $accountId, array $data);

    /** @return array */
    public function getPaymentSources();

    /** @return array */
    public function getEntityLicenses($userId);

    /** @return array */
    public function addEntityLicense($userId, array $data);

    /** @return mixed */
    public function removeEntityLicense($userId, $licenseId);
}
