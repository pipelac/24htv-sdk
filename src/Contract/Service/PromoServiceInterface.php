<?php

namespace TwentyFourTv\Contract\Service;

/**
 * Контракт сервиса управления промо-пакетами
 *
 * @since 1.0.0
 */
interface PromoServiceInterface
{
    /** @return array */
    public function getPackets(array $options = []);

    /** @return array */
    public function getPacketById($packetId);

    /** @return mixed */
    public function deactivateKey($keyId);

    /** @return array */
    public function getUserKeys($userId);

    /** @return array */
    public function activateUserKey($userId, array $data);
}
