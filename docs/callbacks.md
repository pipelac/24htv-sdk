# Обратная интеграция (Callbacks)

Обратная интеграция — это входящие POST-запросы от платформы 24ТВ к серверу провайдера. Платформа вызывает API провайдера при определённых событиях.

## Схема работы

```
┌──────────────┐     POST /auth         ┌───────────────────┐
│              │ ──────────────────────>│                   │
│  Платформа   │     POST /packet       │   Ваш сервер      │
│  24часаТВ    │ ──────────────────────>│   (webhook.php)   │
│              │     POST /delete_sub   │                   │
│              │ ──────────────────────>│  CallbackHandler  │
│              │     POST /balance      │                   │
│              │ ──────────────────────>│                   │
└──────────────┘                        └───────────────────┘
```

Все запросы отправляются на `<API_URL>` провайдера (указывается в `24htv.ini` → `provider.api_url`).

---

## Настройка

### 1. Точка входа (webhook)

```php
<?php
// webhook.php
require_once __DIR__ . '/../vendor/autoload.php';

use TwentyFourTv\ClientFactory;
use TwentyFourTv\Resolver\UtmAuthResolver;
use TwentyFourTv\Resolver\UtmBalanceResolver;

$client = ClientFactory::create(__DIR__ . '/../cfg/24htv.ini', $logger);

// Настройте резолверы для вашего биллинга (см. раздел «Резолверы»)
$client->callbacks()
    ->setAuthResolver(new UtmAuthResolver($db, $logger))
    ->setBalanceResolver(new UtmBalanceResolver($db, $logger));

$response = $client->handleCallback();
$response->send();
```

### 2. Nginx конфигурация

```nginx
# /24htv/ — обратная интеграция 24ТВ
location /24htv/ {
    try_files $uri $uri/ /path/to/webhook.php?$query_string;

    # Ограничение доступа по IP (опционально)
    # allow <IP-адреса 24ТВ>;
    # deny all;
}
```

### 3. Сообщите URL в поддержку 24ТВ

Отправьте `api_url` (например `https://noc.beirel.ru/24htv/`) в [поддержку 24ТВ](https://t.me/help24htv).

---

## Резолверы

Резолверы определяют, как SDK взаимодействует с вашим биллингом. Есть три способа их настроить:

### Встроенные UTM5-резолверы

SDK включает готовые резолверы для биллинга UTM5:

```php
use TwentyFourTv\Resolver\UtmAuthResolver;
use TwentyFourTv\Resolver\UtmBalanceResolver;

// UtmAuthResolver — поиск по IP в таблицах ip_groups → service_links → users_accounts → users
$client->callbacks()->setAuthResolver(new UtmAuthResolver($db, $logger));

// UtmBalanceResolver — SELECT balance FROM accounts WHERE id = ?
$client->callbacks()->setBalanceResolver(new UtmBalanceResolver($db, $logger));
```

### Callable для любого биллинга

```php
$client->callbacks()->setAuthResolver(function ($params) use ($billing) {
    $ip = isset($params['ip']) ? $params['ip'] : null;
    $user = $billing->findByIp($ip);
    
    if ($user) {
        return ['result' => 'success', 'provider_uid' => (string) $user['id']];
    }
    return ['result' => 'error', 'errmsg' => 'User not found'];
});
```

### Свой класс-резолвер (рекомендуется)

Реализуйте `AuthResolverInterface` или `BalanceResolverInterface`:

```php
use TwentyFourTv\Contract\AuthResolverInterface;

class MyBillingAuthResolver implements AuthResolverInterface
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function __invoke(array $params)
    {
        $ip = isset($params['ip']) ? $params['ip'] : null;
        $stmt = $this->pdo->prepare('SELECT id FROM subscribers WHERE ip = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();
        
        if ($row) {
            return ['result' => 'success', 'provider_uid' => (string) $row['id']];
        }
        return ['result' => 'error', 'errmsg' => 'Subscriber not found'];
    }
}

$client->callbacks()->setAuthResolver(new MyBillingAuthResolver($pdo));
```

---

## Эндпоинты

### AUTH — Авторизация абонента

**Когда вызывается:** при попытке входа абонента в приложение, если у пользователя не заполнен `provider_uid`.

**Запрос:**
```
POST <API_URL>/auth?ip=<ip>&phone=<phone>&provider_uid=<provider_uid>
```

**Параметры запроса:**

- `ip` — IP-адрес абонента
- `phone` — телефон регистрации
- `provider_uid` — ID в биллинге провайдера

> **Примечание:** платформа также может передавать `mbr_id` (ID пользователя в 24ТВ) и `provider_id` (ID провайдера) в GET-параметрах.

**Успех:**
```json
{ "result": "success", "provider_uid": "<account_id>" }
```

**Ошибка:**
```json
{ "result": "error", "errmsg": "User not found" }
```

> **Важно:** IP-адреса провайдера прописываются на стороне 24ТВ — регистрация с этих IP автоматически привязывает абонента к провайдеру.

---

### PACKET — Подключение пакета из приложения

**Когда вызывается:** когда абонент подключает/меняет пакет через приложение 24ТВ.

> **Схема «48 часов»:** запрос PACKET при **автопродлении** НЕ отправляется!

**Запрос:**
```
POST <API_URL>/packet?user_id=<provider_user_id>&trf_id=<packet_id>
```

**Тело запроса:**
```json
{
  "type": "packet",
  "user": {
    "id": 12345,
    "phone": "+79001234567",
    "email": "user@example.com",
    "provider_uid": "12345",
    "username": "user_12345",
    "first_name": "Иван",
    "last_name": "Петров",
    "timezone": "Europe/Moscow"
  },
  "packet": {
    "id": 456,
    "price": "300.00",
    "is_base": true,
    "name": "Базовый HD"
  }
}
```

**Логика обработки на стороне провайдера:**

1. Определить возможность подключения (достаточно ли средств)
2. **Upgrade** (более дорогой пакет) — подключить сейчас:
   - Отключить текущий: `DELETE /subscriptions/<subId>`
   - Подключить новый: `POST /subscriptions` с текущей датой
3. **Downgrade** (более дешёвый пакет) — запланировать:
   - Отключить автопродление текущего: `PATCH /subscriptions/<subId>` → `renew=false`
   - Подключить новый с `start_at` = дата окончания текущего
4. Вызвать API 24ТВ для фактического подключения

**Дефолтная реализация:** возвращает `{"result": "success"}` без действий. Полная бизнес-логика реализуется через `setPacketHandler()`.

**Кастомизация:**
```php
$client->callbacks()->setPacketHandler(function ($params, $body) use ($client, $db) {
    $providerUid = $params['user_id'];
    $packetId    = $params['trf_id'];
    $packet      = $body['packet'];
    $price       = (float) $packet['price'];
    
    // 1. Проверить баланс
    $balance = $this->getBalance($providerUid);
    if ($balance < $price) {
        return ['status' => -1, 'errmsg' => 'Недостаточно средств'];
    }
    
    // 2. Списать средства в биллинге
    $this->deductBalance($providerUid, $price);
    
    // 3. Подключить пакет через API 24ТВ
    $user24tv = $body['user'];
    $client->subscriptions()->connectSingle($user24tv['id'], $packetId, true);
    
    return ['status' => 1];
});
```

> **Важно:** при нехватке средств **обязательно** `status = -1`. Платформа 24ТВ ожидает ответ в формате `{"status": 1}` (успех) или `{"status": -1, "errmsg": "..."}` (ошибка).

---

### DELETE_SUBSCRIPTION — Отключение из приложения

**Когда вызывается:** когда абонент отключает автопродление пакета через приложение.

**Запрос:**
```
POST <API_URL>/delete_subscription?user_id=<provider_user_id>&sub_id=<subscription_id>
```

**Тело запроса:**
```json
{
  "type": "delete_sub",
  "user": {
    "id": 12345,
    "provider_uid": "12345",
    "username": "user_12345",
    "first_name": "Иван",
    "last_name": "Петров",
    "phone": "+79001234567",
    "email": "user@example.com",
    "timezone": "Europe/Moscow"
  },
  "subscription": {
    "packet": {
      "id": 456,
      "name": "Базовый HD",
      "price": "300.00",
      "is_base": true
    },
    "start_at": "2026-01-01T00:00:00.000Z",
    "end_at": "2026-01-31T23:59:59.000Z",
    "renew": true,
    "is_paused": false
  }
}
```

**Действие:** снять автопродление → вызвать API 24ТВ:

```
PATCH /v2/users/<userId>/subscriptions/<subId>?token=<TOKEN>
{"packet_id": 456, "renew": false}
```

**Дефолтная реализация:** возвращает `{"result": "success"}` без действий. Полная бизнес-логика реализуется через `setDeleteSubscriptionHandler()`.

**Кастомизация:**
```php
$client->callbacks()->setDeleteSubscriptionHandler(function ($params, $body) use ($client) {
    $userId = $body['user']['id'];
    $subId  = $params['sub_id'];
    $packetId = $body['subscription']['packet']['id'];
    
    // Снять автопродление в 24ТВ
    $client->subscriptions()->disableRenew($userId, $subId, $packetId);
    
    // Отключить услугу в биллинге
    $this->disableServiceInBilling($params['user_id'], $packetId);
    
    return ['status' => 1];
});
```

---

### BALANCE — Запрос баланса

**Когда вызывается:** при входе абонента в настройки приложения.

> Включается через поддержку 24ТВ — опция «Запрашивать баланс абонентов из биллинга».

**Запрос:**
```
POST <API_URL>/balance?user_id=<provider_user_id>&provider_uid=<provider_uid>
```

> **Примечание:** `provider_uid` может приходить как в GET-параметрах, так и в теле запроса. `CallbackHandler` проверяет оба источника.

**Тело запроса:**
```json
{
  "type": "balance",
  "user": {
    "id": 12345,
    "provider_uid": "12345",
    "username": "user_12345",
    "first_name": "Иван",
    "last_name": "Петров",
    "phone": "+79001234567",
    "email": "user@example.com",
    "timezone": "Europe/Moscow"
  }
}
```

**Успех:**
```json
{"result": "success", "balance": "500.00"}
```

**Ошибка:**
```json
{"result": "error", "errmsg": "Account not found"}
```

---

## Сводка сеттеров CallbackHandler

- `setAuthResolver()` — `callable|AuthResolverInterface` — резолвер авторизации
- `setBalanceResolver()` — `callable|BalanceResolverInterface` — резолвер баланса
- `setPacketHandler()` — `fn($params, $body): array` — обработчик подключения пакета
- `setDeleteSubscriptionHandler()` — `fn($params, $body): array` — обработчик отключения подписки

## Встроенные резолверы

| Класс | Биллинг | Описание |
|---|---|---|
| `UtmAuthResolver` | UTM5 | Поиск абонента по IP через `ip_groups` → `service_links` → `users_accounts` |
| `UtmBalanceResolver` | UTM5 | Баланс из таблицы `accounts` |

## Интерфейсы для своих резолверов

| Интерфейс | Метод | Возвращает |
|---|---|---|
| `AuthResolverInterface` | `__invoke(array $params)` | `['result' => 'success', 'provider_uid' => '...']` |
| `BalanceResolverInterface` | `__invoke(array $params)` | `['result' => 'success', 'balance' => '...']` |

---

## Логи и отладка

### Логи на стороне 24ТВ

Все запросы обратной интеграции логируются в [админ-панели → Инструменты](https://24htv.platform24.tv/admin/api-requests/).

> **Таймаут:** 5 секунд. При превышении запрос отбрасывается, тело ответа = `"none"`.

### Логи на стороне провайдера

Все callback-запросы логируются через `Logger`:

```
[INFO]  24HTV callback: входящий запрос {uri: "/auth", params: {...}, ip: "..."}
[INFO]  24HTV AUTH: запрос авторизации {ip: "...", phone: "..."}
[INFO]  24HTV AUTH: абонент найден по IP {ip: "...", account_id: "12345"}
[ERROR] 24HTV PACKET: ошибка {error: "..."}
```

---

## Безопасность

1. **Ограничение по IP:** настройте Nginx для приёма callback только с IP-адресов 24ТВ
2. **HTTPS:** обязательно использовать HTTPS для `api_url`
3. **Валидация параметров:** `CallbackHandler` логирует все входящие данные — проверяйте на подозрительную активность
