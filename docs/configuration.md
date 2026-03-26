# Конфигурация (`24htv.ini`)

Файл конфигурации расположен в `cfg/24htv.ini`.

## Структура секций

### `[api]` — Подключение к платформе 24ТВ

- `base_url` (string, ✅) — Базовый URL API. По умолчанию: `https://provapi.24h.tv/v2`
- `token` (string, ✅) — TOKEN из [админ-панели](https://24htv.platform24.tv/admin/settings/tokens/)
- `timeout` (int) — Общий таймаут запроса в секундах. По умолчанию: `10`
- `connect_timeout` (int) — Таймаут подключения в секундах. По умолчанию: `5`
- `max_retries` (int) — Макс. количество повторов при ошибках 429/5xx. По умолчанию: `2`

> **Важно:** Логи API-запросов хранят платформа — таймаут 5 секунд. При превышении запрос отбрасывается и тело ответа = `"none"`.

---

### `[provider]` — Настройки провайдера

- `id` (string, ✅) — ID провайдера на платформе 24ТВ
- `api_url` (string, ✅) — URL обратной интеграции (должен заканчиваться на `/`)

`api_url` — адрес, на который 24ТВ отправляет callback-запросы (AUTH, PACKET, DELETE_SUBSCRIPTION, BALANCE). Этот URL необходимо сообщить в поддержку 24ТВ.

---

### `[billing]` — Интеграция с UTM-биллингом

- `lookup_method` (string) — Метод поиска абонента: `db` (SQL) или `urfa`. По умолчанию: `db`

Используется в `CallbackHandler` для дефолтной реализации обработчиков AUTH и BALANCE.

---

### `[telegram]` — Уведомления

- `chat_id` (string) — ID Telegram-чата для уведомлений об ошибках

---

### `[logging]` — Логирование

- `level` (string) — Уровень: `debug`, `info`, `warning`, `error`. По умолчанию: `info`
- `log_success` (bool) — Логировать успешные API-ответы. По умолчанию: `true`

---

## Пример конфигурации

```ini
[api]
base_url = "https://provapi.24h.tv/v2"
token = "edb180650f50c525fb33c187f327b16a9cc60d51"
timeout = 10
connect_timeout = 5
max_retries = 2

[provider]
id = "42"
api_url = "https://noc.beirel.ru/24htv/"

[billing]
lookup_method = "db"

[telegram]
chat_id = "-1001234567890"

[logging]
level = "info"
log_success = true
```

## Доступ к конфигурации из кода

```php
$config = $client->getConfig();

// Получить значение (бросает исключение если нет)
$token = $config->get('api.token');

// Получить с дефолтом
$timeout = $config->getOrDefault('api.timeout', 10);

// Получить целую секцию
$apiSection = $config->getSection('api');

// Удобные геттеры
$config->getToken();          // api.token
$config->getBaseUrl();        // api.base_url (без trailing slash)
$config->getTimeout();        // api.timeout → int
$config->getConnectTimeout(); // api.connect_timeout → int
$config->getProviderId();     // provider.id
```
