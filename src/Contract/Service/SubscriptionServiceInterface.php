<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\Subscription;

/**
 * Контракт сервиса управления подписками
 *
 * @since 1.0.0
 */
interface SubscriptionServiceInterface
{
    /** @return Subscription[] */
    public function getAll($userId, array $options = []);

    /** @return Subscription[] */
    public function getCurrent($userId, array $options = []);

    /** @return Subscription[] */
    public function getFuture($userId);

    /** @return Subscription */
    public function getById($userId, $subscriptionId, array $options = []);

    /** @return array */
    public function connect($userId, array $subscriptions);

    /** @return array */
    public function connectSingle($userId, $packetId, $renew = true);

    /** @return Subscription */
    public function update($userId, $subscriptionId, array $data);

    /** @return mixed */
    public function disconnect($userId, $subscriptionId);

    /** @return Subscription */
    public function disableRenew($userId, $subscriptionId, $packetId);

    /** @return array */
    public function pause($userId, $subscriptionId);

    /** @return array */
    public function pauseAll($userId);

    /** @return array */
    public function getPauses($userId, $subscriptionId);

    /** @return mixed */
    public function unpause($userId, $subscriptionId, $pauseId);

    /** @return mixed */
    public function unpauseAll($userId);

    /** @return array */
    public function updatePauseDate($userId, $subscriptionId, $pauseId, $endAt);
}
