# Быстрый старт

## 1. Настройка конфигурации

Создайте файл `24htv.ini`:

```ini
[api]
base_url = "https://provapi.24h.tv/v2"
token = "ВАШ_ТОКЕН_ИЗ_АДМИНПАНЕЛИ"
timeout = 10
connect_timeout = 5
max_retries = 2

[provider]
id = "ВАШ_ID_ПРОВАЙДЕРА"
```

Поддерживаются ENV-переменные:

```ini
[api]
token = "${TWENTYFOURTV_TOKEN}"
```

**Получение TOKEN:**
1. Зайдите в [админ-панель](https://24htv.platform24.tv/admin/login)
2. Раздел → Настройки → [Токены](https://24htv.platform24.tv/admin/settings/tokens/)
3. Скопируйте или создайте новый токен

## 2. Установка

```bash
composer require twentyfourtv/sdk
```

## 3. Инициализация клиента

```php
<?php
require_once 'vendor/autoload.php';

use TwentyFourTv\ClientFactory;

// Создание клиента (рекомендуемый способ)
$client = ClientFactory::create('/path/to/24htv.ini', $logger);

// С подключением к БД (для обратной интеграции)
$client = ClientFactory::create('/path/to/24htv.ini', $logger, $db);
```

## 4. Основные операции

### Регистрация пользователя

```php
// register() возвращает DTO-объект User
$user = $client->users()->register([
    'username'     => 'user_12345',
    'phone'        => '+79001234567',
    'first_name'   => 'Иван',
    'last_name'    => 'Петров',
    'provider_uid' => '12345',  // ID лицевого счёта в UTM
]);

echo "Пользователь создан, ID: " . $user->getId();
echo "Username: " . $user->getUsername();
```

### Подключение базового пакета

```php
// getBase() возвращает Packet[] (массив DTO)
$packets = $client->packets()->getBase();

// Подключить первый базовый пакет с автопродлением
$sub = $client->subscriptions()->connectSingle(
    $user->getId(),        // userId в 24ТВ
    $packets[0]->getId(),  // packetId
    true                   // renew = автопродление
);
```

### Регистрация + подключение за один вызов

```php
$result = $client->registerAndConnect(
    ['username' => 'new_user', 'phone' => '+79009876543', 'provider_uid' => '32240'],
    $packetId,
    true // renew
);

// $result['user'] — DTO-объект User
echo "User ID: " . $result['user']->getId();
```

### Установка баланса

```php
$client->balance()->set($userId, '12345', '500.00');
```

## 5. Обратная интеграция (webhook)

Создайте точку входа для входящих запросов от 24ТВ:

```php
<?php
// webhook.php — обработчик обратной интеграции
require_once 'vendor/autoload.php';

use TwentyFourTv\ClientFactory;

$client = ClientFactory::create('/path/to/24htv.ini', $logger, $db);

// Опциональная настройка кастомных обработчиков
$client->callbacks()->setAuthResolver(function ($params) {
    // Ваша логика поиска абонента
    return ['result' => 'success', 'provider_uid' => $accountId];
});

// Обработка запроса и отправка ответа
$response = $client->handleCallback();
$response->send();
```

Настройте Nginx для маршрутизации:

```nginx
location /24htv/ {
    try_files $uri $uri/ /24htv/webhook.php?$query_string;
}
```

## 6. Проверка работоспособности

```php
<?php
// test.php — быстрый тест подключения к API
require_once 'vendor/autoload.php';

use TwentyFourTv\ClientFactory;

$client = ClientFactory::create('/path/to/24htv.ini', $logger);

try {
    // getBase() возвращает Packet[] (массив DTO)
    $packets = $client->packets()->getBase();
    echo "✅ Подключение работает. Базовых пакетов: " . count($packets) . "\n";
} catch (\Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
```

## Следующие шаги

- Ознакомьтесь с [полным API-справочником](api-reference.md)
- Настройте [обратную интеграцию](callbacks.md)
- Проверьте [все параметры конфигурации](configuration.md)
