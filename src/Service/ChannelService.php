<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\ChannelServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\Channel;

/**
 * Управление каналами платформы 24часаТВ
 *
 * @since 1.0.0
 */
class ChannelService extends AbstractService implements ChannelServiceInterface
{
    /**
     * Получить список всех доступных каналов
     *
     * @param array $options ['includes' => string, 'search' => string, 'order' => string, 'limit' => int, 'offset' => int]
     *
     * @throws TwentyFourTvException
     *
     * @return Channel[] Коллекция типизированных моделей каналов
     */
    public function getAll(array $options = [])
    {
        return $this->createCollection(
            Channel::class,
            $this->api->apiGet(ApiEndpoints::CHANNELS, $options)
        );
    }

    /**
     * Получить канал по ID
     *
     * @param int $channelId
     *
     * @throws TwentyFourTvException
     *
     * @return Channel Типизированная модель канала
     */
    public function getById($channelId)
    {
        $this->requireId($channelId, 'channelId');

        return $this->createModel(
            Channel::class,
            $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_BY_ID, $channelId))
        );
    }

    /**
     * Получить расписание программ на канале
     *
     * @param int   $channelId
     * @param array $options   ['date' => string, 'start' => int, 'end' => int, 'limit' => int, 'offset' => int]
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getSchedule($channelId, array $options = [])
    {
        $this->requireId($channelId, 'channelId');

        return $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_SCHEDULE, $channelId), $options);
    }

    /**
     * Получить расписание контента на канале
     *
     * @param int   $channelId
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getContentSchedule($channelId, array $options = [])
    {
        $this->requireId($channelId, 'channelId');

        return $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_CONTENT_SCHEDULE, $channelId), $options);
    }

    /**
     * Получить поток (стрим) канала
     *
     * @param int   $channelId
     * @param array $options   ['type' => string, 'ts' => int, 'access_token' => string, ...]
     *
     * @throws TwentyFourTvException
     *
     * @return array ['url' => string]
     */
    public function getStream($channelId, array $options = [])
    {
        $this->requireId($channelId, 'channelId');

        return $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_STREAM, $channelId), $options);
    }

    /**
     * Получить список категорий с каналами
     *
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getCategories(array $options = [])
    {
        return $this->api->apiGet(ApiEndpoints::CHANNEL_CATEGORIES, $options);
    }

    /**
     * Получить список категорий с ID каналов (v3)
     *
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getCategoryList(array $options = [])
    {
        return $this->api->apiGet(ApiEndpoints::CHANNEL_CATEGORY_LIST, $options);
    }

    /**
     * Получить список каналов (v3)
     *
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getChannelList(array $options = [])
    {
        return $this->api->apiGet(ApiEndpoints::CHANNEL_LIST, $options);
    }

    /**
     * Получить список бесплатных каналов
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getFreeList()
    {
        return $this->api->apiGet(ApiEndpoints::CHANNEL_FREE_LIST);
    }

    /**
     * Получить пакеты, в которые входит канал
     *
     * @param int $channelId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPackets($channelId)
    {
        $this->requireId($channelId, 'channelId');

        return $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_PACKETS, $channelId));
    }

    /**
     * Получить пакеты для быстрой продажи канала
     *
     * @param int $channelId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getQuickSalesPackets($channelId)
    {
        $this->requireId($channelId, 'channelId');

        return $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_QUICK_SALES_PACKETS, $channelId));
    }

    /**
     * Получить короткую информацию о пакетах для канала
     *
     * @param int $channelId
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getPurchasePacketShort($channelId)
    {
        $this->requireId($channelId, 'channelId');

        return $this->api->apiGet(sprintf(ApiEndpoints::CHANNEL_PURCHASE_PACKET_SHORT, $channelId));
    }

    /**
     * Получить список пользовательских каналов (v3, требует access_token)
     *
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getUserChannelList(array $options = [])
    {
        return $this->api->apiGet(ApiEndpoints::USER_SELF_CHANNEL_LIST, $options);
    }

    /**
     * Получить доступные каналы для пользователя (требует access_token)
     *
     * @param array $options
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function getUserChannels(array $options = [])
    {
        return $this->api->apiGet(ApiEndpoints::USER_SELF_CHANNELS, $options);
    }
}
