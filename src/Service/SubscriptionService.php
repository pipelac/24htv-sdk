<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\SubscriptionServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\Subscription;

/**
 * Управление подписками пользователей 24часаТВ
 *
 * <code>
 * $client->subscriptions()->connect($userId, [
 *     ['packet_id' => 80, 'renew' => true],
 * ]);
 * $sub = $client->subscriptions()->getById($userId, $subId);
 * echo $sub->getPacketId();  // 80
 * echo $sub->isRenew();     // true
 * </code>
 *
 * @since 1.0.0
 */
class SubscriptionService extends AbstractService implements SubscriptionServiceInterface
{
    /**
     * Получить все подписки пользователя
     *
     * @param int   $userId
     * @param array $options ['includes' => string, 'limit' => int, 'offset' => int]
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription[] Коллекция типизированных моделей подписок
     */
    public function getAll($userId, array $options = [])
    {
        $this->requireId($userId, 'userId');

        return $this->createCollection(
            Subscription::class,
            $this->api->apiGet(sprintf(ApiEndpoints::USER_SUBSCRIPTIONS, $userId), $options)
        );
    }

    /**
     * Получить текущие (активные) подписки пользователя
     *
     * @param int   $userId
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription[] Коллекция типизированных моделей подписок
     */
    public function getCurrent($userId, array $options = [])
    {
        $this->requireId($userId, 'userId');

        return $this->createCollection(
            Subscription::class,
            $this->api->apiGet(sprintf(ApiEndpoints::USER_SUBSCRIPTIONS_CURRENT, $userId), $options)
        );
    }

    /**
     * Получить будущие подписки пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription[] Коллекция типизированных моделей подписок
     */
    public function getFuture($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->createCollection(
            Subscription::class,
            $this->api->apiGet(sprintf(ApiEndpoints::USER_FUTURES, $userId))
        );
    }

    /**
     * Получить подписку по ID
     *
     * @param int    $userId
     * @param string $subscriptionId
     * @param array  $options        ['includes' => string]
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription Типизированная модель подписки
     */
    public function getById($userId, $subscriptionId, array $options = [])
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            Subscription::class,
            $this->api->apiGet(sprintf(ApiEndpoints::USER_SUBSCRIPTION_BY_ID, $userId, $subscriptionId), $options)
        );
    }

    /**
     * Подключить подписки (один или несколько пакетов)
     *
     * @param int   $userId
     * @param array $subscriptions Массив подписок [['packet_id' => int, 'renew' => bool], ...]
     *
     * @throws TwentyFourTvException
     *
     * @return array Результат подключения
     */
    public function connect($userId, array $subscriptions)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: подключение подписки', [
            'userId'  => $userId,
            'packets' => array_map(function ($s) {
                return isset($s['packet_id']) ? $s['packet_id'] : null;
            }, $subscriptions),
        ]);

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_SUBSCRIPTIONS, $userId), $subscriptions);
    }

    /**
     * Подключить один пакет (сокращённый метод)
     *
     * @param int         $userId
     * @param int         $packetId
     * @param bool        $renew    Автопродление
     * @param string|null $startAt  Дата начала (ISO 8601, опционально)
     * @param string|null $endAt    Дата окончания (ISO 8601, опционально)
     *
     * @throws TwentyFourTvException
     *
     * @return array Результат подключения
     */
    public function connectSingle($userId, $packetId, $renew = true, $startAt = null, $endAt = null)
    {
        $this->requireId($userId, 'userId');
        $this->requireId($packetId, 'packetId');

        $subscription = ['packet_id' => (int) $packetId, 'renew' => $renew];

        if ($startAt !== null) {
            $subscription['start_at'] = $startAt;
        }

        if ($endAt !== null) {
            $subscription['end_at'] = $endAt;
        }

        return $this->connect($userId, [$subscription]);
    }

    /**
     * Обновить подписку (например, изменить автопродление)
     *
     * @param int    $userId
     * @param string $subscriptionId
     * @param array  $data
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription Обновлённая модель подписки
     */
    public function update($userId, $subscriptionId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            Subscription::class,
            $this->api->apiPatch(sprintf(ApiEndpoints::USER_SUBSCRIPTION_BY_ID, $userId, $subscriptionId), $data)
        );
    }

    /**
     * Отключить подписку
     *
     * @param int    $userId
     * @param string $subscriptionId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function disconnect($userId, $subscriptionId)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: отключение подписки', [
            'userId'         => $userId,
            'subscriptionId' => $subscriptionId,
        ]);

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_SUBSCRIPTION_BY_ID, $userId, $subscriptionId));
    }

    /**
     * Отключить автопродление подписки
     *
     * @param int    $userId
     * @param string $subscriptionId
     * @param int    $packetId
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription Обновлённая модель подписки
     */
    public function disableRenew($userId, $subscriptionId, $packetId)
    {
        $this->requireId($userId, 'userId');
        $this->requireId($packetId, 'packetId');

        $this->log('info', '24HTV: отключение автопродления', [
            'userId'         => $userId,
            'subscriptionId' => $subscriptionId,
            'packetId'       => $packetId,
        ]);

        return $this->update($userId, $subscriptionId, [
            'packet_id' => (int) $packetId,
            'renew'     => false,
        ]);
    }

    /**
     * Поставить подписку на паузу
     *
     * @param int    $userId
     * @param string $subscriptionId
     *
     * @throws TwentyFourTvException
     *
     * @return array Результат постановки на паузу
     */
    public function pause($userId, $subscriptionId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_SUBSCRIPTION_PAUSES, $userId, $subscriptionId));
    }

    /**
     * Поставить на паузу все активные подписки пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return array Массив результатов постановки на паузу
     */
    public function pauseAll($userId)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: пауза всех подписок', ['userId' => $userId]);

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_SUBSCRIPTIONS_PAUSES, $userId));
    }

    /**
     * Поставить пользователя на паузу (по официально документированному эндпоинту)
     *
     * @param int   $userId
     * @param array $data   Отправка дополнительных данных (например дат старта и окончания)
     *
     * @throws TwentyFourTvException
     *
     * @return array Результат
     */
    public function pauseUser($userId, array $data = [])
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_PAUSES, $userId), $data);
    }

    /**
     * Получить список пауз подписки
     *
     * @param int    $userId
     * @param string $subscriptionId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPauses($userId, $subscriptionId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_SUBSCRIPTION_PAUSES, $userId, $subscriptionId));
    }

    /**
     * Снять паузу с подписки
     *
     * @param int    $userId
     * @param string $subscriptionId
     * @param string $pauseId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function unpause($userId, $subscriptionId, $pauseId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_SUBSCRIPTION_PAUSE_BY_ID, $userId, $subscriptionId, $pauseId));
    }

    /**
     * Снять все паузы со всех подписок пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function unpauseAll($userId)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: снятие пауз со всех подписок', ['userId' => $userId]);

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_PAUSES_DELETE, $userId));
    }

    /**
     * Обновить дату окончания паузы
     *
     * @param int    $userId
     * @param string $subscriptionId
     * @param string $pauseId
     * @param string $endAt          Дата в формате YYYY-MM-DDTHH:MM:SS+HH:MM
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function updatePauseDate($userId, $subscriptionId, $pauseId, $endAt)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPatch(
            sprintf(ApiEndpoints::USER_SUBSCRIPTION_PAUSE_BY_ID, $userId, $subscriptionId, $pauseId),
            ['end_at' => $endAt]
        );
    }

    // ==========================================
    // CONVENIENCE METHODS
    // ==========================================

    /**
     * Получить активные подписки
     *
     * @param int   $userId
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription[] Коллекция типизированных моделей подписок
     */
    public function getActive($userId, array $options = [])
    {
        $options['types'] = 'active';

        return $this->getAll($userId, $options);
    }

    /**
     * Получить подписки на паузе
     *
     * @param int   $userId
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription[] Коллекция типизированных моделей подписок
     */
    public function getPaused($userId, array $options = [])
    {
        $options['types'] = 'paused';

        return $this->getAll($userId, $options);
    }

    /**
     * Получить запланированные подписки
     *
     * @param int   $userId
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return Subscription[] Коллекция типизированных моделей подписок
     */
    public function getPlanned($userId, array $options = [])
    {
        $options['types'] = 'planned';

        return $this->getAll($userId, $options);
    }

    /**
     * Персонализировать пакет для пользователя
     *
     * @param int   $userId
     * @param array $data   ['packet_id' => int, 'name' => string, 'price' => string, 'description' => string]
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function personalize($userId, array $data)
    {
        $this->requireId($userId, 'userId');
        $this->requireFields($data, ['packet_id']);

        $this->log('info', '24HTV: персонализация пакета', [
            'userId'   => $userId,
            'packetId' => $data['packet_id'],
        ]);

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_PACKETS, $userId), $data);
    }
}
