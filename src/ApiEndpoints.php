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
    public const USERS = '/users';

    /** @var string Шаблон: sprintf(USER_BY_ID, $userId) */
    public const USER_BY_ID = '/users/%s';

    /** @var string Шаблон: sprintf(USER_ARCHIVE, $userId) */
    public const USER_ARCHIVE = '/users/%s/archive';

    // ==========================================
    // SUBSCRIPTIONS
    // ==========================================

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTIONS, $userId) */
    public const USER_SUBSCRIPTIONS = '/users/%s/subscriptions';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTIONS_CURRENT, $userId) */
    public const USER_SUBSCRIPTIONS_CURRENT = '/users/%s/subscriptions/current';

    /** @var string Шаблон: sprintf(USER_FUTURES, $userId) */
    public const USER_FUTURES = '/users/%s/futures';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTION_BY_ID, $userId, $subscriptionId) */
    public const USER_SUBSCRIPTION_BY_ID = '/users/%s/subscriptions/%s';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTION_PAUSES, $userId, $subscriptionId) */
    public const USER_SUBSCRIPTION_PAUSES = '/users/%s/subscriptions/%s/pauses';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTIONS_PAUSES, $userId) */
    public const USER_SUBSCRIPTIONS_PAUSES = '/users/%s/subscriptions/pauses';

    /** @var string Шаблон: sprintf(USER_SUBSCRIPTION_PAUSE_BY_ID, $userId, $subscriptionId, $pauseId) */
    public const USER_SUBSCRIPTION_PAUSE_BY_ID = '/users/%s/subscriptions/%s/pauses/%s';

    /** @var string Шаблон: sprintf(USER_PAUSES, $userId) */
    public const USER_PAUSES = '/users/%s/pauses';

    /** @var string Шаблон: sprintf(USER_PAUSES_DELETE, $userId) */
    public const USER_PAUSES_DELETE = '/users/%s/pauses/delete';

    // ==========================================
    // PACKETS
    // ==========================================

    /** @var string */
    public const PACKETS = '/packets';

    /** @var string */
    public const PACKETS_FLAT = '/packets/flat';

    /** @var string Шаблон: sprintf(PACKET_BY_ID, $packetId) */
    public const PACKET_BY_ID = '/packets/%s';

    /** @var string Шаблон: sprintf(PACKET_PURCHASES, $packetId) */
    public const PACKET_PURCHASES = '/packets/%s/purchases';

    /** @var string Шаблон: sprintf(PACKET_PURCHASE_PERIODS, $packetId) */
    public const PACKET_PURCHASE_PERIODS = '/packets/%s/purchaseperiods';

    /** @var string Шаблон: sprintf(USER_PACKETS, $userId) */
    public const USER_PACKETS = '/users/%s/packets';

    /** @var string Шаблон: sprintf(USER_PACKET_BY_ID, $userId, $packetId) */
    public const USER_PACKET_BY_ID = '/users/%s/packets/%s';

    // ==========================================
    // BALANCE & ACCOUNTS
    // ==========================================

    /** @var string Шаблон: sprintf(USER_PROVIDER_ACCOUNT, $userId) */
    public const USER_PROVIDER_ACCOUNT = '/users/%s/provider/account';

    /** @var string Шаблон: sprintf(USER_PROVIDER_ACCOUNTS, $userId) */
    public const USER_PROVIDER_ACCOUNTS = '/users/%s/provider/accounts';

    /** @var string Шаблон: sprintf(USER_ACCOUNTS, $userId) */
    public const USER_ACCOUNTS = '/users/%s/accounts';

    /** @var string Шаблон: sprintf(USER_ACCOUNT_TRANSACTIONS, $userId, $accountId) */
    public const USER_ACCOUNT_TRANSACTIONS = '/users/%s/accounts/%s/transactions';

    /** @var string */
    public const PAYMENT_SOURCES = '/paymentsources';

    /** @var string Шаблон: sprintf(USER_ENTITY_LICENSES, $userId) */
    public const USER_ENTITY_LICENSES = '/users/%s/entity_licenses';

    /** @var string Шаблон: sprintf(USER_ENTITY_LICENSE_BY_ID, $userId, $licenseId) */
    public const USER_ENTITY_LICENSE_BY_ID = '/users/%s/entity_licenses/%s';

    // ==========================================
    // CHANNELS
    // ==========================================

    /** @var string */
    public const CHANNELS = '/channels';

    /** @var string Шаблон: sprintf(CHANNEL_BY_ID, $channelId) */
    public const CHANNEL_BY_ID = '/channels/%s';

    /** @var string Шаблон: sprintf(CHANNEL_SCHEDULE, $channelId) */
    public const CHANNEL_SCHEDULE = '/channels/%s/schedule';

    /** @var string Шаблон: sprintf(CHANNEL_CONTENT_SCHEDULE, $channelId) */
    public const CHANNEL_CONTENT_SCHEDULE = '/channels/%s/content_schedule';

    /** @var string Шаблон: sprintf(CHANNEL_STREAM, $channelId) */
    public const CHANNEL_STREAM = '/channels/%s/stream';

    /** @var string */
    public const CHANNEL_CATEGORIES = '/channels/categories';

    /** @var string */
    public const CHANNEL_CATEGORY_LIST = '/channels/category_list';

    /** @var string */
    public const CHANNEL_LIST = '/channels/channel_list';

    /** @var string */
    public const CHANNEL_FREE_LIST = '/channels/free_list';

    /** @var string Шаблон: sprintf(CHANNEL_PACKETS, $channelId) */
    public const CHANNEL_PACKETS = '/channels/%s/packets';

    /** @var string Шаблон: sprintf(CHANNEL_QUICK_SALES_PACKETS, $channelId) */
    public const CHANNEL_QUICK_SALES_PACKETS = '/channels/%s/quick_sales_packets';

    /** @var string Шаблон: sprintf(CHANNEL_PURCHASE_PACKET_SHORT, $channelId) */
    public const CHANNEL_PURCHASE_PACKET_SHORT = '/channels/%s/purchasepacket_short';

    /** @var string */
    public const USER_SELF_CHANNEL_LIST = '/users/self/channel_list';

    /** @var string */
    public const USER_SELF_CHANNELS = '/users/self/channels';

    // ==========================================
    // DEVICES
    // ==========================================

    /** @var string */
    public const DEVICES = '/devices';

    /** @var string Шаблон: sprintf(USER_DEVICES, $userId) */
    public const USER_DEVICES = '/users/%s/devices';

    /** @var string Шаблон: sprintf(USER_DEVICE_BY_ID, $userId, $deviceId) */
    public const USER_DEVICE_BY_ID = '/users/%s/devices/%s';

    /** @var string Шаблон: sprintf(USER_DEVICE_BY_TOKEN, $userId) */
    public const USER_DEVICE_BY_TOKEN = '/users/%s/devices/device';

    // ==========================================
    // TAGS
    // ==========================================

    /** @var string */
    public const TAGS = '/tags';

    /** @var string Шаблон: sprintf(TAG_BY_ID, $tagId) */
    public const TAG_BY_ID = '/tags/%s';

    /** @var string Шаблон: sprintf(USER_TAGS, $userId) */
    public const USER_TAGS = '/users/%s/tags';

    /** @var string Шаблон: sprintf(USER_TAG_BY_ID, $userId, $tagId) */
    public const USER_TAG_BY_ID = '/users/%s/tags/%s';

    // ==========================================
    // PROMO
    // ==========================================

    /** @var string */
    public const PROMO_PACKETS = '/promopackets';

    /** @var string Шаблон: sprintf(PROMO_PACKET_BY_ID, $packetId) */
    public const PROMO_PACKET_BY_ID = '/promopackets/%s';

    /** @var string Шаблон: sprintf(PROMO_KEY_BY_ID, $keyId) */
    public const PROMO_KEY_BY_ID = '/promokeys/%s';

    /** @var string Шаблон: sprintf(USER_PROMO_KEYS, $userId) */
    public const USER_PROMO_KEYS = '/users/%s/promokeys';

    // ==========================================
    // MESSAGES
    // ==========================================

    /** @var string Шаблон: sprintf(USER_MESSAGES, $userId) */
    public const USER_MESSAGES = '/users/%s/messages';

    /** @var string Шаблон: sprintf(USER_MESSAGE_BY_ID, $userId, $messageId) */
    public const USER_MESSAGE_BY_ID = '/users/%s/messages/%s';

    // ==========================================
    // AUTH
    // ==========================================

    /** @var string */
    public const AUTH_PROVIDER = '/auth/provider';

    // ==========================================
    // CONTRACT
    // ==========================================

    /** @var string Шаблон: sprintf(USER_CHANGE_PROVIDER, $userId) */
    public const USER_CHANGE_PROVIDER = '/users/%s/change_provider/1';
}
