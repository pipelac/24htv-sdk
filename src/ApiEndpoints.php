<?php

namespace TwentyFourTv;

/**
 * Реестр всех API endpoint-ов платформы 24часаТВ
 *
 * Единая точка определения путей — исключает «магические строки» в сервисах.
 * Шаблоны с %s / %d используются через sprintf().
 *
 * <code>
 * $url = sprintf(ApiEndpoints::USER_BY_ID, $userId);  // "/users/42"
 * </code>
 *
 * @since 1.0.0
 */
final class ApiEndpoints
{
    // ==========================================
    // USERS
    // ==========================================

    /** @var string */
    const USERS = '/users';

    /** @var string Шаблон: sprintf(USER_BY_ID, $userId) */
    const USER_BY_ID = '/users/%s';

    /** @var string Шаблон: sprintf(USER_ARCHIVE, $userId) */
    const USER_ARCHIVE = '/users/%s/archive';

    // ==========================================
    // SUBSCRIPTIONS
    // ==========================================

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTIONS, $userId) */
    const USER_SUBSCRIPTIONS = '/users/%s/subscriptions';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTIONS_CURRENT, $userId) */
    const USER_SUBSCRIPTIONS_CURRENT = '/users/%s/subscriptions/current';

    /** @var string Шаблон: sprintf(USER_FUTURES, $userId) */
    const USER_FUTURES = '/users/%s/futures';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTION_BY_ID, $userId, $subscriptionId) */
    const USER_SUBSCRIPTION_BY_ID = '/users/%s/subscriptions/%s';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTION_PAUSES, $userId, $subscriptionId) */
    const USER_SUBSCRIPTION_PAUSES = '/users/%s/subscriptions/%s/pauses';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTIONS_PAUSES, $userId) */
    const USER_SUBSCRIPTIONS_PAUSES = '/users/%s/subscriptions/pauses';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTION_PAUSE_BY_ID, $userId, $subscriptionId, $pauseId) */
    const USER_SUBSCRIPTION_PAUSE_BY_ID = '/users/%s/subscriptions/%s/pauses/%s';

    /** @var string Шаблон: sprintf(USER_PAUSES, $userId) */
    const USER_PAUSES = '/users/%s/pauses';

    /** @var string Шаблон: sprintf(USER_PAUSES_DELETE, $userId) */
    const USER_PAUSES_DELETE = '/users/%s/pauses/delete';

    // ==========================================
    // PACKETS
    // ==========================================

    /** @var string */
    const PACKETS = '/packets';

    /** @var string */
    const PACKETS_FLAT = '/packets/flat';

    /** @var string Шаблон: sprintf(PACKET_BY_ID, $packetId) */
    const PACKET_BY_ID = '/packets/%s';

    /** @var string Шаблон: sprintf(PACKET_PURCHASES, $packetId) */
    const PACKET_PURCHASES = '/packets/%s/purchases';

    /** @var string Шаблон: sprintf(PACKET_PURCHASE_PERIODS, $packetId) */
    const PACKET_PURCHASE_PERIODS = '/packets/%s/purchaseperiods';

    /** @var string Шаблон: sprintf(USER_PACKETS, $userId) */
    const USER_PACKETS = '/users/%s/packets';

    /** @var string Шаблон: sprintf(USER_PACKET_BY_ID, $userId, $packetId) */
    const USER_PACKET_BY_ID = '/users/%s/packets/%s';

    // ==========================================
    // BALANCE & ACCOUNTS
    // ==========================================

    /** @var string Шаблон: sprintf(USER_PROVIDER_ACCOUNT, $userId) */
    const USER_PROVIDER_ACCOUNT = '/users/%s/provider/account';

    /** @var string Шаблон: sprintf(USER_PROVIDER_ACCOUNTS, $userId) */
    const USER_PROVIDER_ACCOUNTS = '/users/%s/provider/accounts';

    /** @var string Шаблон: sprintf(USER_ACCOUNTS, $userId) */
    const USER_ACCOUNTS = '/users/%s/accounts';

    /** @var string Шаблон: sprintf(USER_ACCOUNT_TRANSACTIONS, $userId, $accountId) */
    const USER_ACCOUNT_TRANSACTIONS = '/users/%s/accounts/%s/transactions';

    /** @var string */
    const PAYMENT_SOURCES = '/paymentsources';

    /** @var string Шаблон: sprintf(USER_ENTITY_LICENSES, $userId) */
    const USER_ENTITY_LICENSES = '/users/%s/entity_licenses';

    /** @var string Шаблон: sprintf(USER_ENTITY_LICENSE_BY_ID, $userId, $licenseId) */
    const USER_ENTITY_LICENSE_BY_ID = '/users/%s/entity_licenses/%s';

    // ==========================================
    // CHANNELS
    // ==========================================

    /** @var string */
    const CHANNELS = '/channels';

    /** @var string Шаблон: sprintf(CHANNEL_BY_ID, $channelId) */
    const CHANNEL_BY_ID = '/channels/%s';

    /** @var string Шаблон: sprintf(CHANNEL_SCHEDULE, $channelId) */
    const CHANNEL_SCHEDULE = '/channels/%s/schedule';

    /** @var string Шаблон: sprintf(CHANNEL_CONTENT_SCHEDULE, $channelId) */
    const CHANNEL_CONTENT_SCHEDULE = '/channels/%s/content_schedule';

    /** @var string Шаблон: sprintf(CHANNEL_STREAM, $channelId) */
    const CHANNEL_STREAM = '/channels/%s/stream';

    /** @var string */
    const CHANNEL_CATEGORIES = '/channels/categories';

    /** @var string */
    const CHANNEL_CATEGORY_LIST = '/channels/category_list';

    /** @var string */
    const CHANNEL_LIST = '/channels/channel_list';

    /** @var string */
    const CHANNEL_FREE_LIST = '/channels/free_list';

    /** @var string Шаблон: sprintf(CHANNEL_PACKETS, $channelId) */
    const CHANNEL_PACKETS = '/channels/%s/packets';

    /** @var string Шаблон: sprintf(CHANNEL_QUICK_SALES_PACKETS, $channelId) */
    const CHANNEL_QUICK_SALES_PACKETS = '/channels/%s/quick_sales_packets';

    /** @var string Шаблон: sprintf(CHANNEL_PURCHASE_PACKET_SHORT, $channelId) */
    const CHANNEL_PURCHASE_PACKET_SHORT = '/channels/%s/purchasepacket_short';

    /** @var string */
    const USER_SELF_CHANNEL_LIST = '/users/self/channel_list';

    /** @var string */
    const USER_SELF_CHANNELS = '/users/self/channels';

    // ==========================================
    // DEVICES
    // ==========================================

    /** @var string */
    const DEVICES = '/devices';

    /** @var string Шаблон: sprintf(USER_DEVICES, $userId) */
    const USER_DEVICES = '/users/%s/devices';

    /** @var string Шаблон: sprintf(USER_DEVICE_BY_ID, $userId, $deviceId) */
    const USER_DEVICE_BY_ID = '/users/%s/devices/%s';

    /** @var string Шаблон: sprintf(USER_DEVICE_BY_TOKEN, $userId) */
    const USER_DEVICE_BY_TOKEN = '/users/%s/devices/device';

    // ==========================================
    // TAGS
    // ==========================================

    /** @var string */
    const TAGS = '/tags';

    /** @var string Шаблон: sprintf(TAG_BY_ID, $tagId) */
    const TAG_BY_ID = '/tags/%s';

    /** @var string Шаблон: sprintf(USER_TAGS, $userId) */
    const USER_TAGS = '/users/%s/tags';

    /** @var string Шаблон: sprintf(USER_TAG_BY_ID, $userId, $tagId) */
    const USER_TAG_BY_ID = '/users/%s/tags/%s';

    // ==========================================
    // PROMO
    // ==========================================

    /** @var string */
    const PROMO_PACKETS = '/promopackets';

    /** @var string Шаблон: sprintf(PROMO_PACKET_BY_ID, $packetId) */
    const PROMO_PACKET_BY_ID = '/promopackets/%s';

    /** @var string Шаблон: sprintf(PROMO_KEY_BY_ID, $keyId) */
    const PROMO_KEY_BY_ID = '/promokeys/%s';

    /** @var string Шаблон: sprintf(USER_PROMO_KEYS, $userId) */
    const USER_PROMO_KEYS = '/users/%s/promokeys';

    // ==========================================
    // MESSAGES
    // ==========================================

    /** @var string Шаблон: sprintf(USER_MESSAGES, $userId) */
    const USER_MESSAGES = '/users/%s/messages';

    /** @var string Шаблон: sprintf(USER_MESSAGE_BY_ID, $userId, $messageId) */
    const USER_MESSAGE_BY_ID = '/users/%s/messages/%s';

    // ==========================================
    // AUTH
    // ==========================================

    /** @var string */
    const AUTH_PROVIDER = '/auth/provider';

    // ==========================================
    // CONTRACT
    // ==========================================

    /** @var string Шаблон: sprintf(USER_CHANGE_PROVIDER, $userId) */
    const USER_CHANGE_PROVIDER = '/users/%s/change_provider/1';
}
