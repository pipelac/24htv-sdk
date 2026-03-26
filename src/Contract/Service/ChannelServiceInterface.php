<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\Channel;

/**
 * Контракт сервиса управления каналами
 *
 * @since 1.0.0
 */
interface ChannelServiceInterface
{
    /** @return Channel[] */
    public function getAll(array $options = []);

    /** @return Channel */
    public function getById($channelId);

    /** @return array */
    public function getSchedule($channelId, array $options = []);

    /** @return array */
    public function getContentSchedule($channelId, array $options = []);

    /** @return array */
    public function getStream($channelId, array $options = []);

    /** @return array */
    public function getCategories(array $options = []);

    /** @return array */
    public function getCategoryList(array $options = []);

    /** @return array */
    public function getChannelList(array $options = []);

    /** @return array */
    public function getFreeList();

    /** @return array */
    public function getPackets($channelId);

    /** @return array */
    public function getQuickSalesPackets($channelId);

    /** @return array */
    public function getPurchasePacketShort($channelId);

    /** @return array */
    public function getUserChannelList(array $options = []);

    /** @return array */
    public function getUserChannels(array $options = []);
}
