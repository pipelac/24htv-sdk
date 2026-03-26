# 24часаТВ PHP SDK

[![PHP](https://img.shields.io/badge/PHP-5.6%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

PHP SDK для интеграции с платформой [24часаТВ](https://24htv.platform24.tv) по провайдерской схеме.

**Версия:** 1.1.0 | **PHP:** ≥ 5.6 | **Лицензия:** MIT

---

## О сервисе 24часаТВ

**[24часаТВ](https://24htv.platform24.tv)** — российская OTT-платформа для операторов связи и интернет-провайдеров, предоставляющая услуги интерактивного телевидения по модели White Label. Платформа позволяет провайдерам предлагать своим абонентам пакеты ТВ-каналов, управлять подписками и балансами через единый API.

**Возможности платформы:**
- 📺 Более 300 ТВ-каналов в HD и SD качестве
- 📱 Мультиэкранный просмотр — ТВ-приставки, Smart TV, мобильные устройства, веб-плеер
- ⏪ Таймшифт и архив передач до 7 дней
- 📦 Гибкая пакетная сетка — базовые и дополнительные пакеты каналов
- 🔗 Провайдерская интеграция — полный API для управления абонентами
- 💰 Биллинговая интеграция — синхронизация балансов, callback-запросы
- 🎁 Промо-система — промо-ключи и бесплатные периоды
- 🔐 Авторизация устройств — контроль одновременных сессий

**Официальные ресурсы:**

- 🌐 [24htv.platform24.tv](https://24htv.platform24.tv) — платформа провайдера
- 📖 [provapi.24h.tv](https://provapi.24h.tv) — Provider API
- 📺 [24h.tv](https://24h.tv) — абонентский портал

---

## Возможности

- 🎯 **11 сервисов** — Users, Packets, Subscriptions, Balance, Channels, Devices, Auth, Contracts, Tags, Promo, Messages
- 📦 **Типизированные DTO** — `User`, `Packet`, `Subscription`, `Channel`, `Balance` и др.
- 🔄 **Retry с Circuit Breaker** — автоповторы при 429/5xx + защита от каскадных отказов
- 🔒 **Безопасность** — маскирование токенов в логах, SSL по умолчанию
- 📋 **Callbacks** — обработка обратной интеграции (AUTH, PACKET, DELETE_SUBSCRIPTION, BALANCE)
- ✅ **224 теста** — PHPUnit покрытие, PHPStan level 6
- 🔧 **INI + ENV конфигурация** — поддержка `${ENV_VAR}` в INI-файлах

## Требования

- PHP ≥ 5.6
- расширения: `curl`, `json`, `mbstring`
- HTTPS-доступ до `provapi.24h.tv`
- TOKEN из панели провайдера 24ТВ

## Тестовая панель (Control Panel)

SDK включает веб-интерфейс для интерактивного тестирования **всех 98 методов API** прямо из браузера.

### Быстрый старт

```bash
cd panel
php -S localhost:8080
```

Откройте http://localhost:8080, введите API-токен и тестируйте любые методы.

### Покрытие

| Сервис | Методов |
|---|---|
| 👤 Пользователи | 14 |
| 📦 Пакеты | 14 |
| 🔗 Подписки | 19 |
| 💰 Баланс | 13 |
| 📺 Каналы | 14 |
| 📱 Устройства | 6 |
| 🏷️ Теги | 7 |
| 🎁 Промо | 5 |
| ✉️ Сообщения | 4 |
| 🔑 Авторизация | 1 |
| 📄 Контракт | 1 |

Подробнее: [panel/README.md](panel/README.md)

## Установка

```bash
composer require twentyfourtv/sdk
```

## Быстрый старт

### 1. Создание конфигурации

Скопируйте `cfg/24htv.ini.example` → `cfg/24htv.ini` и заполните:

```ini
[api]
token = "ваш_токен_из_панели_24тв"
base_url = "https://provapi.24h.tv/v2"
timeout = 10
connect_timeout = 5
max_retries = 2

[provider]
id = "your_provider_id"
```

Поддерживаются ENV-переменные:

```ini
[api]
token = "${TWENTYFOURTV_TOKEN}"
```

### 2. Создание клиента

```php
<?php
require_once 'vendor/autoload.php';

use TwentyFourTv\ClientFactory;

// Из INI-файла (рекомендуемый способ)
$client = ClientFactory::create('/path/to/24htv.ini', $logger);

// С поддержкой callback-запросов (передайте БД-соединение)
$client = ClientFactory::create('/path/to/24htv.ini', $logger, $db);

// С кастомным HTTP-клиентом (Guzzle и др.)
$client = ClientFactory::create('/path/to/24htv.ini', $logger, null, $myHttpClient);
```

### 3. Использование

```php
// Регистрация пользователя (возвращает User DTO)
$user = $client->users()->register([
    'username'     => 'ivan_petrov',
    'phone'        => '+79001234567',
    'provider_uid' => '32240',
]);

echo $user->getId();
echo $user->getUsername();

// Подключение пакета
$client->subscriptions()->connectSingle($user->getId(), $packetId, true);
```

## Кастомный HTTP-клиент

SDK использует `HttpClientInterface` для HTTP-запросов. По умолчанию — встроенный `HttpClient` на базе cURL.
Можно заменить на собственную реализацию (например, адаптер Guzzle):

```php
use TwentyFourTv\Contract\HttpClientInterface;

class GuzzleHttpAdapter implements HttpClientInterface
{
    private $guzzle;

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function apiGet($endpoint, array $query = [])
    {
        $response = $this->guzzle->get($endpoint, ['query' => $query]);
        return json_decode($response->getBody(), true);
    }

    public function apiPost($endpoint, $body = null, array $query = []) { /* ... */ }
    public function apiPatch($endpoint, $body = null, array $query = []) { /* ... */ }
    public function apiPut($endpoint, $body = null, array $query = []) { /* ... */ }
    public function apiDelete($endpoint, array $query = []) { /* ... */ }
}

// Использование с кастомным клиентом
$guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://provapi.24h.tv/v2/']);
$adapter = new GuzzleHttpAdapter($guzzle);

$client = ClientFactory::create('/path/to/24htv.ini', $logger, null, $adapter);
```

Это полезно для:
- 🧪 Модульного тестирования — подставьте mock-клиент
- 🔧 Интеграции с корпоративным HTTP-стеком (прокси, custom middleware)
- 📊 Мониторинга — оборачивайте вызовы в метрики

## Управление пользователями

```php
// Регистрация
$user = $client->users()->register([
    'username'     => 'ivan_petrov',
    'phone'        => '+79001234567',
    'provider_uid' => '32240',
]);

// Получить по ID
$user = $client->users()->getById($userId);
echo $user->getUsername();
echo $user->getPhone();
echo $user->getProviderUid();

// Обновить данные
$client->users()->update($userId, [
    'phone' => '+79009876543',
]);

// Заблокировать пользователя (рекомендуемый способ)
$client->users()->block($userId);

// Быстрая регистрация + подключение пакета
$result = $client->registerAndConnect(
    ['username' => 'test', 'phone' => '+79001234567', 'provider_uid' => '32240'],
    $packetId
);
$user = $result['user'];          // User DTO
$subs = $result['subscriptions']; // данные подписок
```

## Управление пакетами

```php
// Получить все пакеты (возвращает Packet[])
$packets = $client->packets()->getAll();
foreach ($packets as $packet) {
    echo $packet->getId() . ': ' . $packet->getName() . PHP_EOL;
    echo '  Цена: ' . $packet->getPrice() . PHP_EOL;
}

// Получить пакет по ID
$packet = $client->packets()->getById($packetId);

// Получить персональные пакеты пользователя
$packets = $client->packets()->getUserPackets($userId);

// Получить покупки пакета
$purchases = $client->packets()->getPurchases($packetId);

// Получить периоды покупки
$periods = $client->packets()->getPurchasePeriods($packetId);
```

## Управление подписками

```php
// Подключить пакет пользователю
$client->subscriptions()->connectSingle($userId, $packetId, $renew = true);

// Текущие подписки (Subscription[])
$subs = $client->subscriptions()->getCurrent($userId);
foreach ($subs as $sub) {
    echo 'Пакет: ' . $sub->getPacketId() . PHP_EOL;
    echo 'Статус: ' . $sub->getStatus() . PHP_EOL;
    echo 'До: ' . $sub->getEndAt() . PHP_EOL;
}

// Все подписки (включая архивные)
$allSubs = $client->subscriptions()->getAll($userId);

// Отключить подписку
$client->subscriptions()->disconnect($userId, $subscriptionId);

// Приостановить подписку
$client->subscriptions()->pause($userId, $subscriptionId);

// Возобновить подписку
$client->subscriptions()->unpause($userId, $subscriptionId, $pauseId);

// Приостановить все подписки
$client->subscriptions()->pauseAll($userId);

// Снять все паузы
$client->subscriptions()->unpauseAll($userId);
```

## Баланс

```php
// Установить баланс
$client->balance()->set($userId, '12345', '500.00');

// Получить баланс (возвращает массив)
$balance = $client->balance()->get($userId);

// Получить транзакции
$transactions = $client->balance()->getTransactions($userId, $accountId);
```

## Каналы

```php
// Все каналы (Channel[])
$channels = $client->channels()->getAll(['limit' => 20]);
foreach ($channels as $channel) {
    echo $channel->getId() . ': ' . $channel->getName() . PHP_EOL;
}

// Канал по ID
$channel = $client->channels()->getById($channelId);

// Бесплатные каналы
$free = $client->channels()->getFreeList();

// Категории каналов
$categories = $client->channels()->getCategories();

// Программа передач
$schedule = $client->channels()->getSchedule($channelId);

// Получить поток
$stream = $client->channels()->getStream($channelId);
```

## Устройства

```php
// Устройства пользователя (Device[])
$devices = $client->devices()->getUserDevices($userId);
foreach ($devices as $device) {
    echo $device->getId() . ': ' . $device->getType() . PHP_EOL;
}

// Устройство по ID
$device = $client->devices()->getUserDevice($userId, $deviceId);

// Устройство по access_token
$device = $client->devices()->getUserDeviceByToken($userId, $accessToken);
```

## Аутентификация

```php
// Получить access_token для пользователя
$auth = $client->auth()->getProviderToken([
    'provider_uid' => '32240',
    'device_id'    => 'abc123',
]);
echo $auth['access_token'];
```

## Теги, Промо, Сообщения

```php
// Теги
$tags = $client->tags()->getAll();

// Промо-пакеты
$promos = $client->promo()->getPackets();

// Сообщения пользователя
$messages = $client->messages()->getAll($userId);
$client->messages()->create($userId, ['text' => 'Уведомление']);
```

## Обработка Callbacks

24ТВ отправляет callback-запросы на ваш сервер для обратной интеграции.  
Поддерживаемые типы: `AUTH`, `PACKET`, `DELETE_SUBSCRIPTION`, `BALANCE`.

### UTM5 (встроенные резолверы)

```php
<?php
require_once 'vendor/autoload.php';

use TwentyFourTv\ClientFactory;
use TwentyFourTv\Resolver\UtmAuthResolver;
use TwentyFourTv\Resolver\UtmBalanceResolver;

$client = ClientFactory::create(__DIR__ . '/../cfg/24htv.ini', $logger);

$client->callbacks()
    ->setAuthResolver(new UtmAuthResolver($db, $logger))
    ->setBalanceResolver(new UtmBalanceResolver($db, $logger));

$response = $client->handleCallback();
$response->send();
```

### Другой биллинг (callable или свой класс)

```php
// Вариант 1: callable
$client->callbacks()
    ->setAuthResolver(function ($params) use ($billing) {
        $user = $billing->findByIp($params['ip']);
        return $user
            ? ['result' => 'success', 'provider_uid' => $user['account_id']]
            : ['result' => 'error', 'errmsg' => 'User not found'];
    });

// Вариант 2: свой класс (реализует AuthResolverInterface)
$client->callbacks()->setAuthResolver(new MyBillingAuthResolver($pdo));
```

Подробнее: [Callbacks (обратная интеграция)](docs/callbacks.md)

## Обработка ошибок

```php
use TwentyFourTv\Exception\AuthenticationException;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Exception\NotFoundException;
use TwentyFourTv\Exception\RateLimitException;
use TwentyFourTv\Exception\ConnectionException;
use TwentyFourTv\Exception\ConflictException;
use TwentyFourTv\Exception\ForbiddenException;

try {
    $user = $client->users()->getById($userId);
} catch (NotFoundException $e) {
    echo 'Пользователь не найден';
} catch (AuthenticationException $e) {
    echo 'Невалидный токен — проверьте api.token в конфигурации';
} catch (RateLimitException $e) {
    echo 'Превышен лимит запросов, повторите позже';
} catch (ForbiddenException $e) {
    echo 'Нет доступа к ресурсу';
} catch (ConflictException $e) {
    echo 'Конфликт: ' . $e->getMessage();
} catch (ValidationException $e) {
    echo 'Ошибка валидации: ' . $e->getMessage();
} catch (ConnectionException $e) {
    echo 'Ошибка соединения: ' . $e->getMessage();
}
```

### Иерархия исключений

```
TwentyFourTvException (базовый)
├── AuthenticationException    (401)
├── ForbiddenException         (403)
├── NotFoundException          (404)
├── ConflictException          (409)
├── RateLimitException         (429)
├── ValidationException        (422)
├── ConnectionException        (сеть)
└── ConfigException            (конфигурация)
```

## Структура проекта

```
24htv/
├── src/
│   ├── Client.php                 # Главный фасад
│   ├── ClientFactory.php          # Фабрика создания
│   ├── Config.php                 # Конфигурация (INI + ENV)
│   ├── HttpClient.php             # HTTP-клиент с retry + Circuit Breaker
│   ├── ApiEndpoints.php           # Константы API-путей
│   ├── SdkVersion.php             # Версия SDK
│   ├── Contract/                  # Интерфейсы
│   ├── Resolver/                  # Встроенные резолверы (UTM5)
│   ├── Service/                   # 11 сервисов
│   ├── Model/                     # 10 DTO-моделей
│   ├── Callback/                  # Обратная интеграция
│   ├── Http/                      # CircuitBreaker, ServerRequest
│   ├── Util/                      # QueryBuilder, TokenMasker
│   └── Exception/                 # Иерархия исключений (8 классов)
├── cfg/
│   └── 24htv.ini.example           # Шаблон конфигурации
├── panel/                         # Тестовая панель (98 методов API)
│   ├── index.html                 # Веб-интерфейс
│   ├── app.js                     # Логика и реестр методов
│   ├── style.css                  # Дизайн-система (dark/light)
│   └── proxy.php                  # CORS-прокси для API
├── tests/                         # PHPUnit тесты
├── examples/                      # Примеры использования
├── docs/                          # Документация
├── composer.json
├── phpstan.neon
└── phpunit.xml.dist
```

## Тестирование

```bash
# Установка зависимостей
composer install

# Запуск тестов
composer test

# Статический анализ (PHPStan level 6)
composer analyse

# Проверка стиля кода
composer cs-check

# Автоисправление стиля
composer cs-fix
```

## Документация

- [Быстрый старт](docs/quickstart.md)
- [Конфигурация](docs/configuration.md)
- [API Reference](docs/api-reference.md)
- [Расширенные примеры](docs/examples.md)
- [Callbacks (обратная интеграция)](docs/callbacks.md)
- [Примеры (PHP-файлы)](examples/)
- [CHANGELOG](CHANGELOG.md)

## Лицензия

[MIT License](LICENSE)
