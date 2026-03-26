<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\PacketServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\Packet;

/**
 * Управление пакетами (тарифами) платформы 24часаТВ
 *
 * <code>
 * $packets = $client->packets()->getAll();
 * $base    = $client->packets()->getBase();
 * $packet  = $client->packets()->getById(80);
 * echo $packet->getName();   // 'Базовый'
 * echo $packet->getPrice();  // '199.00'
 * </code>
 *
 * @since 1.0.0
 */
class PacketService extends AbstractService implements PacketServiceInterface
{
    /**
     * Получить все пакеты (иерархия)
     *
     * @param array $options ['includes' => string, 'is_base' => string]
     *
     * @throws TwentyFourTvException
     *
     * @return Packet[] Коллекция типизированных моделей пакетов
     */
    public function getAll(array $options = [])
    {
        return $this->createCollection(
            'TwentyFourTv\Model\Packet',
            $this->api->apiGet(ApiEndpoints::PACKETS, $options)
        );
    }

    /**
     * Получить пакет по ID
     *
     * @param int   $packetId
     * @param array $options  ['includes' => string]
     *
     * @throws TwentyFourTvException
     *
     * @return Packet Типизированная модель пакета
     */
    public function getById($packetId, array $options = [])
    {
        $this->requireId($packetId, 'packetId');

        return $this->createModel(
            'TwentyFourTv\Model\Packet',
            $this->api->apiGet(sprintf(ApiEndpoints::PACKET_BY_ID, $packetId), $options)
        );
    }

    /**
     * Получить плоский список пакетов
     *
     * @param bool|null $isBase Фильтр: true — базовые, false — дополнительные, null — все
     *
     * @throws TwentyFourTvException
     *
     * @return Packet[] Коллекция типизированных моделей пакетов
     */
    public function getFlat($isBase = null)
    {
        $options = [];
        if ($isBase !== null) {
            $options['is_base'] = $isBase ? 'true' : 'false';
        }

        return $this->createCollection(
            'TwentyFourTv\Model\Packet',
            $this->api->apiGet(ApiEndpoints::PACKETS, $options)
        );
    }

    /**
     * Получить плоский список пакетов (через отдельный эндпоинт /packets/flat)
     *
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return Packet[] Коллекция типизированных моделей пакетов
     */
    public function getFlatPackets(array $options = [])
    {
        return $this->createCollection(
            'TwentyFourTv\Model\Packet',
            $this->api->apiGet(ApiEndpoints::PACKETS_FLAT, $options)
        );
    }

    /**
     * Получить только базовые пакеты
     *
     * @throws TwentyFourTvException
     *
     * @return Packet[] Коллекция типизированных моделей пакетов
     */
    public function getBase()
    {
        return $this->getFlat(true);
    }

    /**
     * Получить только дополнительные пакеты
     *
     * @throws TwentyFourTvException
     *
     * @return Packet[] Коллекция типизированных моделей пакетов
     */
    public function getAdditional()
    {
        return $this->getFlat(false);
    }

    /**
     * Получить иерархический список пакетов
     *
     * @param array $includes Дополнительные включения ['channels', 'availables', ...]
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getHierarchical(array $includes = [])
    {
        $options = ['view' => 'hierarchy'];
        if (!empty($includes)) {
            $options['includes'] = implode(',', $includes);
        }

        return $this->api->apiGet(ApiEndpoints::PACKETS, $options);
    }

    /**
     * Получить все пакеты с доступными для подключения
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getAllWithAvailables()
    {
        return $this->api->apiGet(ApiEndpoints::PACKETS, ['includes' => 'availables']);
    }

    /**
     * Получить все пакеты с каналами
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getAllWithChannels()
    {
        return $this->api->apiGet(ApiEndpoints::PACKETS, ['includes' => 'channels']);
    }

    /**
     * Получить покупки для пакета
     *
     * @param int $packetId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPurchases($packetId)
    {
        $this->requireId($packetId, 'packetId');

        return $this->api->apiGet(sprintf(ApiEndpoints::PACKET_PURCHASES, $packetId));
    }

    /**
     * Получить периоды покупок для пакета
     *
     * @param int $packetId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPurchasePeriods($packetId)
    {
        $this->requireId($packetId, 'packetId');

        return $this->api->apiGet(sprintf(ApiEndpoints::PACKET_PURCHASE_PERIODS, $packetId));
    }

    /**
     * Получить персональные пакеты пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return Packet[] Коллекция типизированных моделей пакетов
     */
    public function getUserPackets($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->createCollection(
            'TwentyFourTv\Model\Packet',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_PACKETS, $userId))
        );
    }

    /**
     * Получить персональный пакет пользователя по ID
     *
     * @param int $userId
     * @param int $packetId
     *
     * @throws TwentyFourTvException
     *
     * @return Packet Типизированная модель пакета
     */
    public function getUserPacketById($userId, $packetId)
    {
        $this->requireId($userId, 'userId');
        $this->requireId($packetId, 'packetId');

        return $this->createModel(
            'TwentyFourTv\Model\Packet',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_PACKET_BY_ID, $userId, $packetId))
        );
    }

    /**
     * Создать персональный пакет для пользователя
     *
     * @param int   $userId
     * @param array $data   Данные пакета
     *
     * @throws TwentyFourTvException
     *
     * @return Packet Типизированная модель созданного пакета
     */
    public function createUserPacket($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: создание персонального пакета', [
            'userId' => $userId,
        ]);

        return $this->createModel(
            'TwentyFourTv\Model\Packet',
            $this->api->apiPost(sprintf(ApiEndpoints::USER_PACKETS, $userId), $data)
        );
    }

    /**
     * Обновить персональный пакет пользователя
     *
     * @param int   $userId
     * @param int   $packetId
     * @param array $data
     *
     * @throws TwentyFourTvException
     *
     * @return Packet Типизированная модель обновлённого пакета
     */
    public function updateUserPacket($userId, $packetId, array $data)
    {
        $this->requireId($userId, 'userId');
        $this->requireId($packetId, 'packetId');

        return $this->createModel(
            'TwentyFourTv\Model\Packet',
            $this->api->apiPatch(sprintf(ApiEndpoints::USER_PACKET_BY_ID, $userId, $packetId), $data)
        );
    }

    /**
     * Удалить персональный пакет пользователя
     *
     * @param int $userId
     * @param int $packetId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function deleteUserPacket($userId, $packetId)
    {
        $this->requireId($userId, 'userId');
        $this->requireId($packetId, 'packetId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_PACKET_BY_ID, $userId, $packetId));
    }
}
