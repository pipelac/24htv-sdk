<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\Packet;

/**
 * Контракт сервиса управления пакетами
 *
 * @since 1.0.0
 */
interface PacketServiceInterface
{
    /** @return Packet[] */
    public function getAll(array $options = []);

    /** @return Packet */
    public function getById($packetId, array $options = []);

    /** @return Packet[] */
    public function getFlat($isBase = null);

    /** @return Packet[] */
    public function getBase();

    /** @return Packet[] */
    public function getAdditional();

    /** @return array */
    public function getHierarchical(array $includes = []);

    /** @return array */
    public function getAllWithAvailables();

    /** @return array */
    public function getAllWithChannels();

    /** @return array */
    public function getPurchases($packetId);

    /** @return array */
    public function getPurchasePeriods($packetId);

    /** @return Packet[] */
    public function getUserPackets($userId);

    /** @return Packet */
    public function getUserPacketById($userId, $packetId);

    /** @return Packet */
    public function createUserPacket($userId, array $data);

    /** @return Packet */
    public function updateUserPacket($userId, $packetId, array $data);

    /** @return mixed */
    public function deleteUserPacket($userId, $packetId);
}
