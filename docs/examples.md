# Расширенные примеры работы с SDK

Подробные примеры для типичных сценариев интеграции.

## Содержание

1. [Полный жизненный цикл абонента](#1-полный-жизненный-цикл-абонента)
2. [Работа с пакетами](#2-работа-с-пакетами)
3. [Управление подписками](#3-управление-подписками)
4. [Баланс и платежи](#4-баланс-и-платежи)
5. [Каналы и устройства](#5-каналы-и-устройства)
6. [Callback-обработчики](#6-callback-обработчики)
7. [Обработка ошибок](#7-обработка-ошибок)
8. [Best Practices](#8-best-practices)

---

## 1. Полный жизненный цикл абонента

### Регистрация → Подключение → Управление → Расторжение

```php
<?php
require_once 'vendor/autoload.php';

use TwentyFourTv\ClientFactory;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\ValidationException;

$client = ClientFactory::create(__DIR__ . '/24htv.ini', $logger);

try {
    // === 1. Регистрация пользователя ===
    $user = $client->users()->register([
        'username'     => 'user_' . time(),
        'phone'        => '+79001234567',
        'provider_uid' => '32240',   // ID из вашего биллинга
        'first_name'   => 'Иван',
        'last_name'    => 'Петров',
    ]);

    $userId = $user->getId();
    echo "Создан пользователь: ID={$userId}, login={$user->getUsername()}\n";

    // === 2. Подключение базового пакета ===
    $basePackets = $client->packets()->getBase();
    if (count($basePackets) > 0) {
        $packet = $basePackets[0];
        $client->subscriptions()->connectSingle(
            $userId,
            $packet->getId(),
            true  // автопродление
        );
        echo "Подключён пакет: {$packet->getName()} ({$packet->getPrice()} руб.)\n";
    }

    // === 3. Проверка подписок ===
    $subs = $client->subscriptions()->getCurrent($userId);
    echo "Активных подписок: " . count($subs) . "\n";
    foreach ($subs as $sub) {
        echo "  Пакет #{$sub->getPacketId()}, до {$sub->getEndAt()}\n";
    }

    // === 4. Обновление данных ===
    $client->users()->update($userId, [
        'phone'    => '+79009876543',
        'password' => 'securePassword123',
    ]);

    // === 5. Блокировка (мягкое отключение) ===
    // Сначала отключаем подписки!
    foreach ($subs as $sub) {
        $client->subscriptions()->disconnect($userId, $sub->getId());
    }
    // Затем блокируем
    $client->users()->block($userId);

    // === 6. Расторжение договора (необратимо!) ===
    // $client->contracts()->terminate($userId);

} catch (ValidationException $e) {
    echo "Ошибка валидации: " . $e->getMessage() . "\n";
} catch (TwentyFourTvException $e) {
    echo "Ошибка API: " . $e->getMessage() . "\n";
    echo "HTTP: " . $e->getHttpCode() . "\n";
}
```

### Быстрая регистрация + подключение (через registerAndConnect)

```php
$result = $client->registerAndConnect(
    [
        'username'     => 'user_' . time(),
        'phone'        => '+79001234567',
        'provider_uid' => '32240',
    ],
    $basePacketId  // ID пакета
);

$user = $result['user'];          // User DTO
$subs = $result['subscriptions']; // данные подписок
echo "Пользователь {$user->getUsername()} подключён к пакету\n";
```

---

## 2. Работа с пакетами

### Получение и фильтрация пакетов

```php
// Все пакеты (Packet[] DTO)
$all = $client->packets()->getAll();

// Только базовые
$base = $client->packets()->getBase();

// Только дополнительные
$additional = $client->packets()->getAdditional();

// Плоский список с фильтром
$flat = $client->packets()->getFlat(true);  // true = базовые

// Иерархический список с включениями
$hierarchy = $client->packets()->getHierarchical(['channels', 'availables']);

// Конкретный пакет по ID (Packet DTO)
$packet = $client->packets()->getById(123, ['includes' => 'channels']);
echo "Пакет: {$packet->getName()} — {$packet->getPrice()} руб.\n";
echo "Базовый: " . ($packet->getIsBase() ? 'да' : 'нет') . "\n";

// Покупки и периоды
$purchases = $client->packets()->getPurchases(123);
$periods   = $client->packets()->getPurchasePeriods(123);
```

### Персональные пакеты пользователя

```php
// Получить персональные пакеты пользователя
$userPackets = $client->packets()->getUserPackets($userId);

// Создать персональный пакет (например, VIP для конкретного абонента)
$custom = $client->packets()->createUserPacket($userId, [
    'packet_id'   => 123,
    'name'        => 'VIP-пакет для Иванова',
    'price'       => '99.00',
    'description' => 'Персональная скидка',
]);
echo "Персональный пакет создан: #{$custom->getId()}\n";

// Изменить персональный пакет
$client->packets()->updateUserPacket($userId, $custom->getId(), [
    'price' => '149.00',
]);

// Удалить персональный пакет
$client->packets()->deleteUserPacket($userId, $custom->getId());
```

---

## 3. Управление подписками

### Подключение и отключение

```php
// Подключить один пакет с автопродлением
$client->subscriptions()->connectSingle($userId, $packetId, true);

// Подключить с конкретными датами
$client->subscriptions()->connectSingle(
    $userId,
    $packetId,
    true,                          // автопродление
    '2026-04-01T00:00:00.000Z',   // start_at
    '2026-04-30T23:59:59.000Z'    // end_at
);

// Подключить несколько пакетов
$client->subscriptions()->connect($userId, [
    array('packet_id' => 80, 'renew' => true),
    array('packet_id' => 91, 'renew' => false),
]);

// Отключить подписку
$client->subscriptions()->disconnect($userId, $subscriptionId);
```

### Получение подписок

```php
// Текущие активные подписки (Subscription[])
$current = $client->subscriptions()->getCurrent($userId);

// Все подписки с фильтрацией
$active  = $client->subscriptions()->getAll($userId, ['types' => 'active']);
$paused  = $client->subscriptions()->getAll($userId, ['types' => 'paused']);
$planned = $client->subscriptions()->getAll($userId, ['types' => 'planned']);

// С включением данных о пакете
$detailed = $client->subscriptions()->getAll($userId, [
    'types'    => 'active',
    'includes' => 'packet.channels',
]);

// Конкретная подписка (Subscription DTO)
$sub = $client->subscriptions()->getById($userId, $subId, [
    'includes' => 'packet',
]);
echo "Пакет #{$sub->getPacketId()}, статус: {$sub->getStatus()}\n";
echo "С {$sub->getStartAt()} по {$sub->getEndAt()}\n";
echo "Автопродление: " . ($sub->isRenew() ? 'да' : 'нет') . "\n";
echo "На паузе: " . ($sub->isPaused() ? 'да' : 'нет') . "\n";

// Будущие (запланированные) подписки
$future = $client->subscriptions()->getFuture($userId);

// Удобные методы (алиасы с фильтром types)
$active  = $client->subscriptions()->getActive($userId);
$paused  = $client->subscriptions()->getPaused($userId);
$planned = $client->subscriptions()->getPlanned($userId);
```

### Паузы

```php
// Поставить подписку на паузу
$pause = $client->subscriptions()->pause($userId, $subId);

// Поставить ВСЕ подписки на паузу
$client->subscriptions()->pauseAll($userId);

// Получить список пауз подписки
$pauses = $client->subscriptions()->getPauses($userId, $subId);

// Снять паузу
$client->subscriptions()->unpause($userId, $subId, $pauseId);

// Снять ВСЕ паузы у пользователя
$client->subscriptions()->unpauseAll($userId);

// Изменить дату снятия паузы
$client->subscriptions()->updatePauseDate(
    $userId,
    $subId,
    $pauseId,
    '2026-04-15T00:00:00+03:00'
);
```

### Автопродление и upgrade/downgrade

```php
// Отключить автопродление (подписка доживёт до конца текущего периода)
$client->subscriptions()->disableRenew($userId, $subId, $packetId);

// Upgrade (немедленная замена пакета):
// 1. Отключить текущий
$client->subscriptions()->disconnect($userId, $currentSubId);
// 2. Подключить новый с текущей датой
$client->subscriptions()->connectSingle($userId, $newPacketId, true);

// Downgrade (замена в конце периода):
// 1. Отключить автопродление текущего
$client->subscriptions()->disableRenew($userId, $currentSubId, $currentPacketId);
// 2. Подключить новый с start_at = дата конца текущего
$currentSub = $client->subscriptions()->getById($userId, $currentSubId);
$client->subscriptions()->connectSingle(
    $userId,
    $cheaperPacketId,
    true,
    $currentSub->getEndAt()
);
```

---

## 4. Баланс и платежи

### Установка и получение баланса

```php
// Установить отображаемый баланс (для конкретного лицевого счёта)
$client->balance()->set($userId, '12345', '500.00');

// Получить текущий баланс (возвращает массив)
$balance = $client->balance()->get($userId);
```

### Платёжные аккаунты

```php
// Множественные аккаунты провайдера
$client->balance()->setProviderAccounts($userId, [
    array('id' => '12345', 'amount' => '500.00'),
    array('id' => '67890', 'amount' => '200.00'),
]);

$accounts = $client->balance()->getProviderAccounts($userId);

// Платёжные аккаунты пользователя
$userAccounts = $client->balance()->getAccounts($userId);
$client->balance()->createAccount($userId, [
    'payment_source_id' => 10414,
]);

// Транзакции
$transactions = $client->balance()->getTransactions($userId, $accountId);
$client->balance()->createTransaction($userId, $accountId, [
    'amount'      => '-300.00',
    'description' => 'Списание за пакет Базовый HD',
]);

// Платёжные источники
$sources = $client->balance()->getPaymentSources();
// Пример: [{"id": 10414, "name": "Биллинг", "type": "billing"}]
```

### Лицензии

```php
// Получить лицензии пользователя
$licenses = $client->balance()->getEntityLicenses($userId);

// Добавить лицензию
$client->balance()->addEntityLicense($userId, [
    'entity_license_id' => 42,
]);

// Удалить лицензию
$client->balance()->removeEntityLicense($userId, $licenseId);
```

---

## 5. Каналы и устройства

### Каналы

```php
// Все каналы (Channel[] DTO)
$channels = $client->channels()->getAll(['limit' => 50]);
foreach ($channels as $ch) {
    echo "#{$ch->getId()} {$ch->getName()}\n";
}

// Канал по ID (Channel DTO)
$channel = $client->channels()->getById($channelId);

// Бесплатные каналы
$free = $client->channels()->getFreeList();

// Категории с каналами
$categories = $client->channels()->getCategories();

// Программа передач
$schedule = $client->channels()->getSchedule($channelId);

// Пакеты, в которые входит канал
$packets = $client->channels()->getPackets($channelId);

// Стрим (требует device-bound access_token!)
// $stream = $client->channels()->getStream($channelId);
```

### Устройства

```php
// Все устройства провайдера
$allDevices = $client->devices()->getAll(['limit' => 100]);

// Создать устройство
$device = $client->devices()->create([
    'serial' => 'STB-12345',
    'type'   => 'stb',
]);

// Устройства пользователя (Device[] DTO)
$userDevices = $client->devices()->getUserDevices($userId);
foreach ($userDevices as $dev) {
    echo "#{$dev->getId()} {$dev->getType()}\n";
}

// Устройство по ID (Device DTO)
$device = $client->devices()->getUserDevice($userId, $deviceId);

// Устройство по access_token
$device = $client->devices()->getUserDeviceByToken($userId, $accessToken);

// Удалить устройство
$client->devices()->deleteUserDevice($userId, $deviceId);
```

### Теги, промо, сообщения

```php
// --- Теги ---
$tags = $client->tags()->getAll();
$tag  = $client->tags()->create(['name' => 'VIP', 'shortname' => 'vip']);
$client->tags()->addToUser($userId, ['tag_id' => $tag->getId()]);
$client->tags()->removeFromUser($userId, $tag->getId());

// --- Промо ---
$promos = $client->promo()->getPackets();
$promo  = $client->promo()->getPacketById($promoId);
$client->promo()->activateUserKey($userId, ['key' => 'PROMO-123']);
$keys = $client->promo()->getUserKeys($userId);
$client->promo()->deactivateKey($keyId);

// --- Сообщения ---
$messages = $client->messages()->getAll($userId);
$msg = $client->messages()->create($userId, ['text' => 'Уведомление']);
$client->messages()->getById($userId, $msg->getId());
$client->messages()->delete($userId, $msg->getId());
```

---

## 6. Callback-обработчики

### Полный пример webhook.php

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use TwentyFourTv\ClientFactory;

$client = ClientFactory::create(__DIR__ . '/../24htv.ini', $logger);

// === AUTH: определение абонента по IP ===
$client->callbacks()->setAuthResolver(function ($params) use ($db) {
    $ip = isset($params['ip']) ? $params['ip'] : null;
    if ($ip === null) {
        return ['result' => 'error', 'errmsg' => 'IP not provided'];
    }

    // Поиск абонента по IP в биллинге
    $stmt = $db->prepare("
        SELECT u.account_id FROM users u
        JOIN service_links sl ON sl.account_id = u.account_id
        JOIN ip_groups ig ON ig.ip_group_id = sl.ip_group_id
        WHERE ig.ip = INET_ATON(:ip) AND sl.is_deleted = 0
        LIMIT 1
    ");
    $stmt->execute([':ip' => $ip]);
    $row = $stmt->fetch();

    if ($row) {
        return ['result' => 'success', 'provider_uid' => (string) $row['account_id']];
    }

    return ['result' => 'error', 'errmsg' => 'User not found by IP'];
});

// === BALANCE: запрос баланса из биллинга ===
$client->callbacks()->setBalanceResolver(function ($params) use ($db) {
    $providerUid = isset($params['provider_uid']) ? $params['provider_uid'] : null;
    if ($providerUid === null) {
        return ['result' => 'error', 'errmsg' => 'provider_uid required'];
    }

    $stmt = $db->prepare("SELECT balance FROM accounts WHERE id = :id");
    $stmt->execute([':id' => $providerUid]);
    $row = $stmt->fetch();

    if ($row) {
        return ['result' => 'success', 'balance' => number_format($row['balance'], 2, '.', '')];
    }

    return ['result' => 'error', 'errmsg' => 'Account not found'];
});

// === PACKET: подключение пакета из приложения ===
$client->callbacks()->setPacketHandler(function ($params, $body) use ($client, $db) {
    $providerUid = $params['user_id'];
    $packetId    = $params['trf_id'];
    $packetPrice = (float) $body['packet']['price'];
    $userId      = $body['user']['id'];

    // 1. Проверить баланс
    $stmt = $db->prepare("SELECT balance FROM accounts WHERE id = :id");
    $stmt->execute([':id' => $providerUid]);
    $account = $stmt->fetch();

    if (!$account || $account['balance'] < $packetPrice) {
        return ['status' => -1, 'errmsg' => 'Недостаточно средств'];
    }

    // 2. Списать средства
    $db->prepare("UPDATE accounts SET balance = balance - :price WHERE id = :id")
       ->execute([':price' => $packetPrice, ':id' => $providerUid]);

    // 3. Подключить пакет в 24ТВ
    $client->subscriptions()->connectSingle($userId, $packetId, true);

    return ['status' => 1];
});

// === DELETE_SUBSCRIPTION: отключение подписки из приложения ===
$client->callbacks()->setDeleteSubscriptionHandler(function ($params, $body) use ($client) {
    $userId   = $body['user']['id'];
    $subId    = $params['sub_id'];
    $packetId = $body['subscription']['packet']['id'];

    // Отключить автопродление в 24ТВ
    $client->subscriptions()->disableRenew($userId, $subId, $packetId);

    return ['status' => 1];
});

// Обработать запрос и отправить ответ
use TwentyFourTv\Http\ResponseEmitter;
$response = $client->handleCallback();
ResponseEmitter::emit($response);
```

---

## 7. Обработка ошибок

### Полная иерархия catch-блоков

```php
use TwentyFourTv\Exception\AuthenticationException;
use TwentyFourTv\Exception\ConfigException;
use TwentyFourTv\Exception\ConflictException;
use TwentyFourTv\Exception\ConnectionException;
use TwentyFourTv\Exception\ForbiddenException;
use TwentyFourTv\Exception\NotFoundException;
use TwentyFourTv\Exception\RateLimitException;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\ValidationException;

try {
    $user = $client->users()->register($data);

} catch (ValidationException $e) {
    // 422 — неправильные данные (email уже есть, телефон занят и т.д.)
    echo "Ошибка валидации: " . $e->getMessage() . "\n";

} catch (AuthenticationException $e) {
    // 401 — невалидный API-токен
    echo "Проверьте api.token в конфигурации\n";

} catch (ForbiddenException $e) {
    // 403 — нет доступа к ресурсу
    echo "Доступ запрещён\n";

} catch (NotFoundException $e) {
    // 404 — ресурс не найден
    echo "Не найден\n";

} catch (ConflictException $e) {
    // 409 — конфликт (например, дублирование)
    echo "Конфликт: " . $e->getMessage() . "\n";

} catch (RateLimitException $e) {
    // 429 — превышен лимит запросов (SDK повторяет автоматически)
    echo "Лимит запросов исчерпан после retry\n";

} catch (ConnectionException $e) {
    // Ошибка сети (таймаут, DNS и т.д.)
    echo "Проблема с сетью: " . $e->getMessage() . "\n";

} catch (TwentyFourTvException $e) {
    // Все остальные ошибки API
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "HTTP: " . $e->getHttpCode() . "\n";
    echo "Endpoint: " . $e->getEndpoint() . "\n";
    echo "Response: " . print_r($e->getResponseBody(), true) . "\n";
}
```

### Инициализация клиента с проверкой конфигурации

```php
use TwentyFourTv\Exception\ConfigException;

try {
    $client = ClientFactory::create('/path/to/24htv.ini');
} catch (ConfigException $e) {
    // INI-файл не найден или token не указан
    error_log("SDK Config Error: " . $e->getMessage());
    exit(1);
}
```

---

## 8. Best Practices

### Используйте ENV-переменные для секретов

```ini
; 24htv.ini — НЕ коммитьте реальные токены в Git!
[api]
token = "${TWENTYFOURTV_TOKEN}"
base_url = "${TWENTYFOURTV_URL}"

[provider]
id = "${TWENTYFOURTV_PROVIDER_ID}"
```

### Передавайте Logger для отладки

```php
// PSR-3 совместимый логгер
$client = ClientFactory::create('24htv.ini', $logger);

// Все API-вызовы, callback-запросы и ошибки будут логироваться
```

### Используйте block() вместо delete()

```php
// ❌ НЕ рекомендуется — может вернуть 404
$client->users()->delete($userId);

// ✅ Рекомендуется — мягкое отключение через PATCH
$client->users()->block($userId);

// ✅ Для семантики «архивирования»
$client->users()->archive($userId);

// ✅ Для восстановления
$client->users()->unblock($userId);
```

### Всегда отключайте подписки перед блокировкой

```php
// 1. Сначала отключить все подписки
$subs = $client->subscriptions()->getCurrent($userId);
foreach ($subs as $sub) {
    $client->subscriptions()->disconnect($userId, $sub->getId());
}

// 2. Затем заблокировать
$client->users()->block($userId);
```

### Retry и Circuit Breaker работают автоматически

SDK автоматически повторяет запросы при ошибках 429, 500, 502, 503, 504 с экспоненциальным backoff и jitter. Поддерживается заголовок `Retry-After`.

**Circuit Breaker** защищает от каскадных отказов: после `max_retries` неудачных попыток контур размыкается и последующие запросы будут возвращать ошибку без реального HTTP-вызова (на протяжении таймаута восстановления).

```ini
[api]
max_retries = 2   ; количество повторов (по умолчанию 2)
timeout = 10      ; таймаут запроса (по умолчанию 10 сек)
```
