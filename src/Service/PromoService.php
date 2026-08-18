<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\PromoServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;

/**
 * Управление промо-пакетами и промо-ключами 24часаТВ
 *
 * @since 1.0.0
 */
class PromoService extends AbstractService implements PromoServiceInterface
{
    /**
     * Получить список промо-пакетов
     *
     * @param array $options ['includes' => string]
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPackets(array $options = [])
    {
        return $this->api->apiGet(ApiEndpoints::PROMO_PACKETS, $options);
    }

    /**
     * Получить промо-пакет по ID
     *
     * @param string $packetId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPacketById($packetId)
    {
        return $this->api->apiGet(sprintf(ApiEndpoints::PROMO_PACKET_BY_ID, $packetId));
    }

    /**
     * Деактивировать промо-ключ
     *
     * @param string $keyId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function deactivateKey($keyId)
    {
        return $this->api->apiDelete(sprintf(ApiEndpoints::PROMO_KEY_BY_ID, $keyId));
    }

    /**
     * Получить активированные промо-ключи пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getUserKeys($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiGet(sprintf(ApiEndpoints::USER_PROMO_KEYS, $userId));
    }

    /**
     * Активировать промо-ключ для пользователя
     *
     * @param int   $userId
     * @param array $data
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function activateUserKey($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_PROMO_KEYS, $userId), $data);
    }
}
