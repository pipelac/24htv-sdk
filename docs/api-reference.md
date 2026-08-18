# Полный справочник API методов

Все методы доступны через фасад `Client`:

```php
$client = \TwentyFourTv\ClientFactory::create('/path/to/24htv.ini', $logger);
```

---

## Содержание

1. [UserService — Пользователи](#1-userservice--пользователи)
2. [PacketService — Пакеты](#2-packetservice--пакеты)
3. [SubscriptionService — Подписки](#3-subscriptionservice--подписки)
4. [BalanceService — Баланс и аккаунты](#4-balanceservice--баланс-и-аккаунты)
5. [ContractService — Расторжение](#5-contractservice--расторжение)
6. [AuthService — Аутентификация](#6-authservice--аутентификация)
7. [ChannelService — Каналы](#7-channelservice--каналы)
8. [DeviceService — Устройства](#8-deviceservice--устройства)
9. [TagService — Теги](#9-tagservice--теги)
10. [PromoService — Промо](#10-promoservice--промо)
11. [MessageService — Сообщения](#11-messageservice--сообщения)
12. [Обработка ошибок](#12-обработка-ошибок)
13. [Сводная таблица эндпоинтов](#13-сводная-таблица-эндпоинтов)

---

## 1. UserService — Пользователи

Доступ: `$client->users()`

### `register(array $data): User`

Регистрация нового пользователя на платформе 24ТВ.

**API:** `POST /v2/users?token=<TOKEN>`

**Параметры:**

- `username` (string, ✅) — Уникальный логин
- `phone` (string, ✅) — Телефон (уникальный)
- `first_name` (string) — Имя
- `last_name` (string) — Фамилия
- `email` (string) — Email (не передавайте пустым!)
- `provider_uid` (string) — ID в биллинге провайдера
- `is_active` (bool) — Активен ли (по умолчанию `true`)

**Возвращает:** DTO-объект `User` с доступом через геттеры.

```php
$user = $client->users()->register([
    'username'     => 'user_12345',
    'phone'        => '+79001234567',
    'provider_uid' => '12345',
]);
// $user->getId() — ID в 24ТВ
// $user->getUsername() — логин
```

> **Ошибки 400:** `email already exists`, `phone already exists`, `username already exists`

---

### `update(int $userId, array $data): User`

Изменение информации о пользователе.

**API:** `PATCH /v2/users/<id>?token=<TOKEN>`

**Параметры:**

- `username` (string) — Новый логин
- `first_name` (string) — Имя
- `last_name` (string) — Фамилия
- `provider_uid` (string) — ID в биллинге
- `password` (string) — Пароль для входа
- `is_provider_free` (bool) — Бесплатный абонент
- `is_active` (bool) — Активен/заблокирован

```php
$client->users()->update($userId, [
    'first_name' => 'Новое имя',
    'is_active'  => false,
]);
```

> **Внимание:** При `is_active=false` подписки **не отключаются автоматически!** Сначала отключите подписки.

---

### `getAll(array $options = []): User[]`

Получение списка всех пользователей с пагинацией и фильтрацией.

**API:** `GET /v2/users?token=<TOKEN>&limit=<N>&offset=<N>`

```php
$users = $client->users()->getAll(['limit' => 50, 'offset' => 0]);
```

---
**Опции:** `phone`, `username`, `provider_uid`, `search`, `limit`, `offset`

### `getById(int $userId): User`

**API:** `GET /v2/users/<id>?token=<TOKEN>`

При отсутствии — `TwentyFourTvException` с HTTP 404.

---

### `findByPhone(string $phone): array`

**API:** `GET /v2/users?phone=<phone>&token=<TOKEN>`

Возвращает массив (пустой, если не найден).

---

### `findByProviderUid(string $providerUid): array`

**API:** `GET /v2/users?provider_uid=<uid>&token=<TOKEN>`

Возвращает массив (пустой, если не найден).

---

### `findByEmail(string $email): array`

**API:** `GET /v2/users?email=<email>&token=<TOKEN>`

Возвращает массив (пустой, если не найден).

---

### `findByUsername(string $username): array`

**API:** `GET /v2/users?username=<username>&token=<TOKEN>`

Возвращает массив (пустой, если не найден).

---

### `search(string $query, int $limit = 20, int $offset = 0): User[]`

Полнотекстовый поиск пользователей (по имени, телефону, email и т.д.).

**API:** `GET /v2/users?search=<query>&token=<TOKEN>`

```php
$results = $client->users()->search('Иванов', 10, 0);
```

---

### `delete(int $userId): mixed`

Деактивация пользователя через HTTP DELETE (альтернатива `block()`).

**API:** `DELETE /v2/users/<id>?token=<TOKEN>`

> **Примечание:** при тестировании этот эндпоинт вернул HTTP 404. Рекомендуется использовать `block()` через `PATCH is_active=false`.

---

### `archive(int $userId): mixed`

Архивирование пользователя.

**API:** `DELETE /v2/users/<id>/archive?token=<TOKEN>`

---

### `block(int $userId): array`

Блокировка пользователя (`is_active = false`) через PATCH.

### `unblock(int $userId): array`

Разблокировка пользователя (`is_active = true`).

---

## 2. PacketService — Пакеты

Доступ: `$client->packets()`

### Типы пакетов

- **Базовые** (`is_base = true`) — основной пакет (одновременно только один базовый)
- **Дополнительные** (`is_base = false`) — доп. пакеты (можно несколько одновременно)

### `getHierarchical(array $includes = []): array`

Иерархичный список пакетов с дополнительными данными.

**API:** `GET /v2/packets?token=<TOKEN>&includes=<params>`

**Дополнительные параметры `includes`:**

- `channels` — список каналов в пакете
- `availables` — доступные покупки

```php
$packets = $client->packets()->getHierarchical(['availables', 'channels']);
```

---

### `getById(int $packetId, array $includes = []): array`

Получение одного пакета по ID.

**API:** `GET /v2/packets/<id>?token=<TOKEN>`

```php
$packet = $client->packets()->getById(123, ['channels']);
```

---

### `getFlat(bool $isBase = null): array`

Плоский список пакетов.

```php
$all      = $client->packets()->getFlat();        // все
$base     = $client->packets()->getFlat(true);     // только базовые
$addons   = $client->packets()->getFlat(false);    // только доп.
```

---

### Удобные методы

- `getAll()` — все пакеты (Packet[])
- `getBase()` — только базовые (алиас `getFlat(true)`)
- `getAdditional()` — только дополнительные (алиас `getFlat(false)`)
- `getUserPackets($userId)` — персональные пакеты пользователя

---

### 2.1 Покупки

#### `getPurchases(int $packetId): array`

Получить доступные покупки для пакета.

**API:** `GET /v2/packets/<id>/purchases?token=<TOKEN>`

#### `getPurchasePeriods(int $packetId): array`

Получить покупки, сгруппированные по периоду действия.

**API:** `GET /v2/packets/<id>/purchaseperiods?token=<TOKEN>`

---

### 2.2 Персональные пакеты пользователя

#### `getUserPackets(int $userId): array`

Получить список персональных пакетов пользователя.

**API:** `GET /v2/users/<id>/packets?token=<TOKEN>`

#### `getUserPacketById(int $userId, string $packetId): array`

Получить персональный пакет пользователя по ID.

**API:** `GET /v2/users/<id>/packets/<packetId>?token=<TOKEN>`

#### `createUserPacket(int $userId, array $data): array`

Создать персональный пакет пользователю.

**API:** `POST /v2/users/<id>/packets?token=<TOKEN>`

```php
$client->packets()->createUserPacket($userId, [
    'packet_id'   => 123,
    'name'        => 'VIP-пакет для Иванова',
    'price'       => '199.00',
    'description' => 'Персональные условия',
]);
```

#### `updateUserPacket(int $userId, string $packetId, array $data): array`

Изменить данные пользовательского пакета.

**API:** `PATCH /v2/users/<id>/packets/<packetId>?token=<TOKEN>`

#### `deleteUserPacket(int $userId, string $packetId): mixed`

Удалить персональный пакет пользователя.

**API:** `DELETE /v2/users/<id>/packets/<packetId>?token=<TOKEN>`

---

## 3. SubscriptionService — Подписки

Доступ: `$client->subscriptions()`

### 3.1 Подключение / Отключение

#### `connect(int $userId, array $subscriptions): array`

Подключение одного или нескольких пакетов.

**API:** `POST /v2/users/<id>/subscriptions?token=<TOKEN>`

```php
$subs = $client->subscriptions()->connect($userId, [
    [
        'packet_id' => 123,
        'renew'     => true,
        'start_at'  => '2026-03-01T00:00:00.000Z',  // опционально
        'end_at'    => '2026-03-31T23:59:59.000Z',   // опционально
    ],
]);
```

**Поддерживаемые поля подписки:**

- `packet_id` (int, ✅) — ID пакета
- `renew` (bool) — автопродление (по умолчанию `true`)
- `start_at` (string) — дата начала (ISO 8601)
- `end_at` (string) — дата окончания (ISO 8601)

> **Правило `end_at`:** должно быть на **секунду меньше** начала следующего периода.
> Пример: `start_at = 20 января` → `end_at = 20 февраля - 1 сек`

---

#### `connectSingle(int $userId, int $packetId, bool $renew, string $startAt = null, string $endAt = null): array`

Быстрое подключение одного пакета:

```php
$sub = $client->subscriptions()->connectSingle($userId, 123, true);
```

---

#### `disconnect(int $userId, string $subscriptionId): array`

**API:** `DELETE /v2/users/<id>/subscriptions/<subId>?token=<TOKEN>`

```php
$client->subscriptions()->disconnect($userId, $subId);
```

---

#### `update(int $userId, string $subscriptionId, array $data): mixed`

Обновление подписки (изменение автопродления).

**API:** `PATCH /v2/users/<id>/subscriptions/<subId>?token=<TOKEN>`

```php
$client->subscriptions()->update($userId, $subId, [
    'packet_id' => 123,
    'renew'     => false,
]);
```

---

#### `disableRenew(int $userId, string $subscriptionId, int $packetId): mixed`

Отключение автопродления (без удаления подписки):

```php
$client->subscriptions()->disableRenew($userId, $subId, $packetId);
```

---

### 3.2 Получение подписок

#### `getCurrent(int $userId): array`

Текущие активные подписки.

**API:** `GET /v2/users/<id>/subscriptions/current?token=<TOKEN>`

#### `getAll(int $userId, array $options = []): Subscription[]`

Все подписки с поддержкой фильтрации.

**API:** `GET /v2/users/<id>/subscriptions?token=<TOKEN>`

**Поддерживаемые фильтры (через `$options`):**

- `types` — `active`, `expired`, `paused`, `future`
- `includes` — `packet`, `packet.channels`

```php
$active = $client->subscriptions()->getAll($userId, ['types' => 'active']);
$paused = $client->subscriptions()->getAll($userId, ['types' => 'paused', 'includes' => 'packet.channels']);
```

#### `getById(int $userId, string $subscriptionId, array $options = []): Subscription`

Получение одной подписки по ID.

**API:** `GET /v2/users/<id>/subscriptions/<subId>?token=<TOKEN>`

```php
$sub = $client->subscriptions()->getById($userId, $subId, ['includes' => 'packet.channels']);
```

#### `getFuture(int $userId): array`

Получение будущих (запланированных) подписок.

**API:** `GET /v2/users/<id>/futures?token=<TOKEN>`

```php
$future = $client->subscriptions()->getFuture($userId);
```

---

### 3.3 Персонализация пакета

#### `personalize(int $userId, array $data): array`

Создание персональных условий пакета для конкретного пользователя.

**API:** `POST /v2/users/<id>/packets?token=<TOKEN>`

```php
$client->subscriptions()->personalize($userId, [
    'packet_id'   => 123,
    'name'        => 'VIP-пакет для Иванова',
    'price'       => '199.00',
    'description' => 'Персональные условия',
]);
```

> **Примечание:** аналогичный функционал доступен через `packets()->createUserPacket()`.

---

### 3.4 Управление паузами

- `pause($userId, $subId)` — поставить подписку на паузу
- `unpause($userId, $subId, $pauseId)` — снять с паузы
- `updatePauseDate($userId, $subId, $pauseId, $date)` — изменить дату снятия
- `pauseAll($userId)` — поставить все подписки на паузу
- `unpauseAll($userId)` — снять все паузы

```php
// Поставить на паузу
$pause = $client->subscriptions()->pause($userId, $subId);

// Снять с паузы
$client->subscriptions()->unpause($userId, $subId, $pause['id']);

// Изменить дату снятия
$client->subscriptions()->updatePauseDate(
    $userId, $subId, $pauseId,
    '2026-04-01T00:00:00+03:00'
);
```

> **На паузе:** период действия подписки не считается и пересчитывается после снятия.
> Для любых операций с подпиской сначала необходимо снять паузу.

---

## 4. BalanceService — Баланс и аккаунты

Доступ: `$client->balance()`

### 4.1 Баланс провайдера

#### `set(int $userId, string $billingId, string $amount): array`

Установка отображаемого баланса в приложении 24ТВ.

**API:** `POST /v2/users/<id>/provider/account?token=<TOKEN>`

Тело запроса: `{"id": "<billingId>", "amount": "<amount>"}`

> **Важно:** поле `id` передаётся как строка (`string`), а не число.

```php
$client->balance()->set($userId, '12345', '500.00');
```

---

#### `get(int $userId): array`

Просмотр текущего значения баланса.

**API:** `GET /v2/users/<id>/provider/account?token=<TOKEN>`

---

### 4.2 Аккаунты провайдера (множественные)

#### `getProviderAccounts(int $userId): array`

Получить платёжные аккаунты провайдера для пользователя.

**API:** `GET /v2/users/<id>/provider/accounts?token=<TOKEN>`

#### `setProviderAccounts(int $userId, array $accounts): array`

Создать платёжные аккаунты провайдера (массив).

**API:** `POST /v2/users/<id>/provider/accounts?token=<TOKEN>`

```php
$client->balance()->setProviderAccounts($userId, [
    ['id' => '12345', 'amount' => '500.00'],
    ['id' => '67890', 'amount' => '200.00'],
]);
```

---

### 4.3 Платёжные аккаунты пользователя

#### `getAccounts(int $userId): array`

Получить платёжные аккаунты пользователя.

**API:** `GET /v2/users/<id>/accounts?token=<TOKEN>`

#### `createAccount(int $userId, array $data): array`

Создать платёжный аккаунт пользователя.

**API:** `POST /v2/users/<id>/accounts?token=<TOKEN>`

---

### 4.4 Транзакции

#### `getTransactions(int $userId, string $accountId): array`

Получить транзакции по аккаунту пользователя.

**API:** `GET /v2/users/<id>/accounts/<accountId>/transactions?token=<TOKEN>`

#### `createTransaction(int $userId, string $accountId, array $data): array`

Создать транзакцию.

**API:** `POST /v2/users/<id>/accounts/<accountId>/transactions?token=<TOKEN>`

---

### 4.5 Платёжные источники

#### `getPaymentSources(): array`

Получить платёжные источники.

**API:** `GET /v2/paymentsources?token=<TOKEN>`

```php
$sources = $client->balance()->getPaymentSources();
// [{"id": 10414, "name": "Биллинг", "type": "billing"}]
```

---

### 4.6 Лицензии

#### `getEntityLicenses(int $userId): array`

Получить список лицензий пользователя.

**API:** `GET /v2/users/<id>/entity_licenses?token=<TOKEN>`

#### `addEntityLicense(int $userId, array $data): array`

Добавить лицензию пользователю.

**API:** `POST /v2/users/<id>/entity_licenses?token=<TOKEN>`

#### `removeEntityLicense(int $userId, int $licenseId): mixed`

Удалить лицензию пользователя.

**API:** `DELETE /v2/users/<id>/entity_licenses/<licenseId>?token=<TOKEN>`

---

## 5. ContractService — Расторжение

Доступ: `$client->contracts()`

### `terminate(int $userId): mixed`

Расторжение договора — перевод абонента к провайдеру «24ТВ» (id=1) для прямой оплаты картой.

**API:** `PUT /v2/users/<id>/change_provider/1?token=<TOKEN>`

```php
$client->contracts()->terminate($userId);
```

> **Необратимо!** После расторжения абонент переходит на оплату через 24ТВ напрямую.

---

## 6. AuthService — Аутентификация

Доступ: `$client->auth()`

### `getProviderToken(array $data): array`

Получить `access_token` пользователя для выполнения запросов от его имени.

**API:** `POST /v2/auth/provider?token=<TOKEN>`

**Параметры:**

- `user_id` (int) — ID пользователя в 24ТВ

```php
// $result['access_token'] — токен для действий от имени пользователя
// $result['expired'] — время истечения (null = бессрочный)
```

---

## 7. ChannelService — Каналы

Доступ: `$client->channels()`

### `getAll(array $options = []): array`

Получить список всех доступных каналов.

**API:** `GET /v2/channels?token=<TOKEN>`


### `getById(int $channelId): array`

**API:** `GET /v2/channels/<id>?token=<TOKEN>`

### `getSchedule(int $channelId, array $options = []): array`

Получить расписание программ на канале.

**API:** `GET /v2/channels/<id>/schedule?token=<TOKEN>`

### `getContentSchedule(int $channelId, array $options = []): array`

Получить расписание контента на канале.

**API:** `GET /v2/channels/<id>/content_schedule?token=<TOKEN>`

### `getStream(int $channelId, array $options = []): array`

Получить поток (стрим) канала.

**API:** `GET /v2/channels/<id>/stream?token=<TOKEN>`

> **⚠️ Важно:** Метод требует `access_token` пользователя, привязанный к устройству (device-bound сессия).
> Токен, полученный через `auth.getProviderToken()`, не имеет привязки к устройству и вернёт HTTP 401.
> Стриминг работает только с клиентских устройств (STB, Smart TV, мобильное приложение).


**Схемы аутентификации** (Swagger `securitySchemes`):
- `providerTokenAuthentication` — `token` в query (провайдерский токен)
- `userAccessToken` — `access_token` в query (пользовательский токен)
- `userAccessTokenHeader` — `Authorization: Token <access_token>` в заголовке

### `getCategories(array $options = []): array`

Получить список категорий с каналами.

**API:** `GET /v2/channels/categories?token=<TOKEN>`

### `getCategoryList(array $options = []): array`

Получить список категорий с ID каналов (v3).

**API:** `GET /v2/channels/category_list?token=<TOKEN>`

### `getChannelList(array $options = []): array`

Получить список каналов (v3).

**API:** `GET /v2/channels/channel_list?token=<TOKEN>`

### `getFreeList(): array`

Получить список бесплатных каналов.

**API:** `GET /v2/channels/free_list?token=<TOKEN>`

### `getPackets(int $channelId): array`

Получить пакеты, в которые входит канал.

**API:** `GET /v2/channels/<id>/packets?token=<TOKEN>`

### `getQuickSalesPackets(int $channelId): array`

Получить пакеты для быстрой продажи канала.

**API:** `GET /v2/channels/<id>/quick_sales_packets?token=<TOKEN>`

### `getPurchasePacketShort(int $channelId): array`

Получить короткую информацию о пакетах через покупки.

**API:** `GET /v2/channels/<id>/purchasepacket_short?token=<TOKEN>`

### `getUserChannelList(array $options = []): array`

Получить список атрибутов пользовательских каналов (v3). Требует `access_token`.

**API:** `GET /v2/users/self/channel_list?token=<TOKEN>`

### `getUserChannels(array $options = []): array`

Получить список доступных каналов для пользователя. Требует `access_token`.

**API:** `GET /v2/users/self/channels?token=<TOKEN>`

---

## 8. DeviceService — Устройства

Доступ: `$client->devices()`

### 8.1 Устройства провайдера

#### `getAll(array $options = []): array`

Получить список устройств провайдера.

**API:** `GET /v2/devices?token=<TOKEN>`

**Опции:** `search`, `limit`, `offset`, `type`

#### `create(array $data): Device`

Создать устройство.

**API:** `POST /v2/devices?token=<TOKEN>`

---

### 8.2 Устройства пользователя

#### `getUserDevices(int $userId): array`

Получить список устройств пользователя.

**API:** `GET /v2/users/<id>/devices?token=<TOKEN>`

#### `getUserDevice(int $userId, string $deviceId): array`

Получить устройство пользователя по ID.

**API:** `GET /v2/users/<id>/devices/<deviceId>?token=<TOKEN>`

#### `getUserDeviceByToken(int $userId, string $accessToken): array`

Получить устройство пользователя по `access_token`.

**API:** `GET /v2/users/<id>/devices/device?access_token=<TOKEN>`

#### `deleteUserDevice(int $userId, string $deviceId): mixed`

Удалить устройство пользователя.

**API:** `DELETE /v2/users/<id>/devices/<deviceId>?token=<TOKEN>`

---

## 9. TagService — Теги

Доступ: `$client->tags()`

### 9.1 Глобальные теги

#### `getAll(array $options = []): array`

Получить список тегов.

**API:** `GET /v2/tags?token=<TOKEN>`

**Опции:** `search`, `limit`, `offset`

#### `create(array $data): Tag`

Создать тег.

**API:** `POST /v2/tags?token=<TOKEN>`

#### `getById(string $tagId): array`

**API:** `GET /v2/tags/<id>?token=<TOKEN>`

#### `update(string $tagId, array $data): array`

**API:** `PATCH /v2/tags/<id>?token=<TOKEN>`

#### `delete(string $tagId): mixed`

**API:** `DELETE /v2/tags/<id>?token=<TOKEN>`

---

### 9.2 Теги пользователя

#### `addToUser(int $userId, array $data): array`

Добавить тег к пользователю.

**API:** `POST /v2/users/<id>/tags?token=<TOKEN>`

#### `removeFromUser(int $userId, string $tagId): array`

Удалить тег у пользователя.

**API:** `DELETE /v2/users/<id>/tags/<tagId>?token=<TOKEN>`

---

## 10. PromoService — Промо

Доступ: `$client->promo()`

### 10.1 Промо-пакеты

#### `getPackets(array $options = []): array`

Получить список промо-пакетов.

**API:** `GET /v2/promopackets?token=<TOKEN>`

#### `getPacketById(string $packetId): array`

Получить промо-пакет по ID.

**API:** `GET /v2/promopackets/<id>?token=<TOKEN>`

---

### 10.2 Промо-ключи

#### `deactivateKey(string $keyId): mixed`

Деактивировать промо-ключ.

**API:** `DELETE /v2/promokeys/<id>?token=<TOKEN>`

#### `getUserKeys(int $userId): array`

Получить активированные промо-ключи пользователя.

**API:** `GET /v2/users/<id>/promokeys?token=<TOKEN>`

#### `activateUserKey(int $userId, array $data): array`

Активировать промо-ключ для пользователя.

**API:** `POST /v2/users/<id>/promokeys?token=<TOKEN>`

---

## 11. MessageService — Сообщения

Доступ: `$client->messages()`

### `getAll(int $userId): array`

Получить список сообщений пользователя.

**API:** `GET /v2/users/<id>/messages?token=<TOKEN>`

### `getById(int $userId, string $messageId): array`

Получить сообщение по ID.

**API:** `GET /v2/users/<id>/messages/<messageId>?token=<TOKEN>`

### `create(int $userId, array $data): array`

Создать сообщение для пользователя.

**API:** `POST /v2/users/<id>/messages?token=<TOKEN>`

### `delete(int $userId, string $messageId): mixed`

Удалить сообщение пользователя.

**API:** `DELETE /v2/users/<id>/messages/<messageId>?token=<TOKEN>`

---

## 12. Обработка ошибок

Все ошибки API генерируют `TwentyFourTvException`:

```php
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\AuthenticationException;
use TwentyFourTv\Exception\NotFoundException;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Exception\RateLimitException;
use TwentyFourTv\Exception\ConnectionException;
use TwentyFourTv\Exception\ForbiddenException;
use TwentyFourTv\Exception\ConflictException;

try {
    $user = $client->users()->register($data);
} catch (NotFoundException $e) {
    echo "Пользователь не найден";
} catch (AuthenticationException $e) {
    echo "Невалидный токен";
} catch (ValidationException $e) {
    echo "Ошибка валидации: " . $e->getMessage();
} catch (TwentyFourTvException $e) {
    echo "Ошибка: " . $e->getMessage();
    echo "HTTP код: " . $e->getHttpCode();
    echo "Тело ответа: " . print_r($e->getResponseBody(), true);
}
```


---

## 13. Сводная таблица эндпоинтов

### Провайдер → 24ТВ (`provapi.24h.tv`)

#### Пользователи

- `POST /v2/users` — регистрация
- `PATCH /v2/users/<id>` — обновление
- `GET /v2/users` — список / поиск
- `GET /v2/users/<id>` — по ID
- `DELETE /v2/users/<id>` — удаление
- `DELETE /v2/users/<id>/archive` — архивация

#### Пакеты

- `GET /v2/packets` — список пакетов
- `GET /v2/packets/<id>` — по ID
- `GET /v2/packets/<id>/purchases` — покупки
- `GET /v2/packets/<id>/purchaseperiods` — периоды
- `POST /v2/users/<id>/packets` — персональный пакет

#### Подписки

- `POST /v2/users/<id>/subscriptions` — подключение
- `DELETE /v2/users/<id>/subscriptions/<subId>` — отключение
- `PATCH /v2/users/<id>/subscriptions/<subId>` — обновление
- `GET /v2/users/<id>/subscriptions` — все подписки
- `GET /v2/users/<id>/subscriptions/current` — текущие
- `POST /v2/users/<id>/subscriptions/<subId>/pause` — пауза

#### Баланс и аккаунты

- `POST /v2/users/<id>/provider/account` — установка баланса
- `GET /v2/users/<id>/provider/account` — получение баланса
- `GET /v2/users/<id>/accounts` — платёжные аккаунты
- `GET /v2/paymentsources` — платёжные источники

#### Каналы

- `GET /v2/channels` — список
- `GET /v2/channels/<id>` — по ID
- `GET /v2/channels/<id>/schedule` — расписание
- `GET /v2/channels/<id>/stream` — поток
- `GET /v2/channels/categories` — категории
- `GET /v2/channels/free_list` — бесплатные

#### Устройства

- `GET /v2/devices` — устройства провайдера
- `GET /v2/users/<id>/devices` — устройства пользователя
- `DELETE /v2/users/<id>/devices/<deviceId>` — удаление

#### Аутентификация, теги, промо, сообщения

- `POST /v2/auth/provider` — получение access_token
- `GET /v2/tags` / `POST` / `PATCH` / `DELETE` — CRUD тегов
- `GET /v2/promopackets` — промо-пакеты
- `GET /v2/users/<id>/messages` — сообщения

#### Расторжение

- `PUT /v2/users/<id>/change_provider/1` — расторжение договора

### 24ТВ → Провайдер (обратная интеграция)

- `POST <API_URL>/auth` — авторизация абонента
- `POST <API_URL>/packet` — подключение пакета
- `POST <API_URL>/delete_subscription` — отключение подписки
- `POST <API_URL>/balance` — запрос баланса

Подробнее об обратной интеграции — см. [callbacks.md](callbacks.md).
