# Changelog

Все значительные изменения в проекте документируются в этом файле.

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.0.0/),
проект следует [семантическому версионированию](https://semver.org/lang/ru/).

## [1.1.0] - 2026-03-26

### Добавлено
- Интерфейс `AuthResolverInterface` — контракт резолвера авторизации
- Интерфейс `BalanceResolverInterface` — контракт резолвера баланса
- Класс `UtmAuthResolver` — встроенный резолвер авторизации для UTM5 (поиск по IP)
- Класс `UtmBalanceResolver` — встроенный резолвер баланса для UTM5
- Тесты `UtmAuthResolverTest` (5 тестов), `UtmBalanceResolverTest` (4 теста)
- Конфигурационный файл `cfg/24htv.ini.example`

### Изменено
- `CallbackHandler` больше не принимает `$db` — стал billing-agnostic
- Лицензия изменена с proprietary на MIT
- Конфиг перенесён в `cfg/` (дефолтный путь: `cfg/24htv.ini`)

### Удалено
- `CallbackHandler::defaultAuthResolver()` — SQL-запросы к UTM5 вынесены в `UtmAuthResolver`
- `CallbackHandler::defaultBalanceResolver()` — SQL-запросы к UTM5 вынесены в `UtmBalanceResolver`
- Неиспользуемые секции конфигурации: `[telegram]`, `[logging]`, `[billing]`

### Миграция с 1.0.0

```diff
-$client = ClientFactory::create('24htv.ini', $logger, $db);
+use TwentyFourTv\Resolver\UtmAuthResolver;
+use TwentyFourTv\Resolver\UtmBalanceResolver;
+
+$client = ClientFactory::create('cfg/24htv.ini', $logger);
+$client->callbacks()
+    ->setAuthResolver(new UtmAuthResolver($db, $logger))
+    ->setBalanceResolver(new UtmBalanceResolver($db, $logger));
```

## [1.0.0] - 2026-03-21

### Добавлено
- Архитектура SDK на основе паттерна Facade с lazy-loading сервисов
- Namespace `TwentyFourTv` (PSR-4)
- **11 сервисов**: User, Packet, Subscription, Balance, Channel, Device, Auth, Contract, Tag, Promo, Message
- **DTO / Value Objects** (`src/Model/`): User, Packet, Subscription, Channel, Device, Tag, Message, Balance, Transaction, PaginatedResult
- **Контракты**: `HttpClientInterface`, `ConfigInterface`, `LoggerInterface`, `CallbackHandlerInterface`, 11 интерфейсов сервисов
- Базовый абстрактный класс `AbstractService` (устранение дублирования)
- `ClientFactory` для удобного создания клиента
- `SdkVersion` — единственная точка определения версии SDK
- `Http\ServerRequest` — безопасная обёртка над `$_SERVER`/`$_GET`/`php://input`
- `Http\CircuitBreaker` — паттерн Circuit Breaker для устойчивости к сбоям
- Иерархия исключений: `TwentyFourTvException`, `AuthenticationException`, `ValidationException`, `ConnectionException`, `NotFoundException`, `RateLimitException`, `ForbiddenException`, `ConflictException`, `ConfigException`
- Валидация обязательных полей перед API-вызовами
- Retry-механизм с экспоненциальным backoff и jitter (429, 500, 502, 503, 504)
- Поддержка `Retry-After` заголовка (макс. 30 сек)
- Middleware-система для `HttpClient` (before/after хуки)
- User-Agent заголовок `24htv-sdk/1.0.0 PHP/<version>` во всех запросах
- Маскирование токенов в логах (`Util\TokenMasker`)
- `CallbackHandler` — обработка обратной интеграции (AUTH, PACKET, DELETE_SUBSCRIPTION, BALANCE) с кастомными обработчиками
- `CallbackResponse` DTO — callback-обработчик не вызывает `header()`/`echo` напрямую
- `ApiEndpoints` — центральный реестр всех 60+ API endpoint-ов как констант
- Setter Injection в `Client` — для тестирования и переопределения сервисов
- Все DTO модели помечены как `final` с иммутабельными свойствами
- Типизированные коллекции через `createCollection()`
- `PaginatedResult` implements `Countable`, `IteratorAggregate`
- Полная поддержка ENV-переменных в конфигурации (`${VAR_NAME}`)
- PHPUnit тесты (224 теста, 439 ассертов)
- CI/CD: PHPUnit multi-version matrix (5.6–8.3), PHPStan level 6, PHP CS Fixer
- Полная документация: README, quickstart, api-reference, callbacks, configuration
