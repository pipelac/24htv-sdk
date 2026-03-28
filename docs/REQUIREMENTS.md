# Требования к SDK-проекту

## 1. Архитектура и SOLID

- [x] **SRP** — каждый класс имеет одну ответственность (Client ≠ HttpClient ≠ Service)
  > `Client` — фасад, `HttpClient` — транспорт с retry, `CircuitBreaker` — устойчивость, `Service/*` — API-методы, `Config` — конфигурация
- [x] **OCP** — добавление новых сервисов без модификации существующего кода
  > 16 интерфейсов в `Contract/`: 5 инфраструктурных + 11 сервисных
- [x] **LSP** — все реализации интерфейсов полностью взаимозаменяемы
  > `Client` использует интерфейсы для всех свойств и return-типов. `resolveService()` создаёт сервисы, `registerService()` позволяет подменить реализацию.
- [x] **ISP** — интерфейсы узкие
  > `HttpClientInterface` (5 методов), `LoggerInterface` (4 метода), `ConfigInterface` (getters)
- [x] **DIP** — зависимость от абстракций
  > `Client(HttpClientInterface, ConfigInterface, LoggerInterface = null)`
- [x] Отсутствие God-классов (>500 строк — проверить обоснованность)
  > Самый большой — `HttpClient.php` (~460 строк, обосновано: retry + CircuitBreaker + middleware). `Client.php` (~385 строк, обосновано: 11 lazy-сервисов через `resolveService()` + Service Registry).
- [x] Composition over Inheritance (наследование только для исключений и DTO)
  > Наследование: `TwentyFourTvException → leaf`, `AbstractModel → DTO`, `AbstractService → Service`. Всё остальное — композиция.
- [x] Иммутабельность конфигурации после инициализации
  > `Config` — нет setters, все значения через конструктор + INI-файл.
- [x] Ленивая инициализация сервисов (lazy loading)
  > `Client::users()`, `Client::packets()` и т.д. — создание при первом обращении через `resolveService()`.
- [x] Фабрики для создания сложных объектов (Factory pattern)
  > `ClientFactory::create()` — фабричный метод. `Client::registerService()` — подмена реализаций через кастомные фабрики.

## 2. Контракты и интерфейсы

- [x] Интерфейсы для всех ключевых зависимостей
  > 16 интерфейсов: `HttpClientInterface`, `ConfigInterface`, `LoggerInterface`, `DatabaseInterface`, `CallbackHandlerInterface`, 11 сервисных интерфейсов.
- [x] Отсутствие пустых/мёртвых интерфейсов
  > 16 интерфейсов, все используются в конструкторах, type-hints и PHPDoc.
- [x] PHPDoc на каждом методе интерфейса с `@param`, `@return`, `@throws`
  > Все методы во всех интерфейсах задокументированы.
- [x] `@since` аннотации при изменении контрактов
  > `@since 1.0.0` на всех классах и интерфейсах.
- [x] Интерфейсы сгруппированы в отдельной директории (`Contract/`)
  > `src/Contract/` — 5 файлов + `src/Contract/Service/` — 11 файлов.

## 3. Типизация и PHPDoc

- [x] Type hints на всех параметрах (для PHP 5.6 — через PHPDoc)
  > Все классы используют type-hints для интерфейсов + PHPDoc `@param` для scalar.
- [x] `@return` типы на всех методах
  > Проверено: `Client`, `Config`, `HttpClient`, все DTO, все Services, все Contracts.
- [x] `@throws` с FQCN
  > `@throws TwentyFourTvException`, `AuthenticationException`, `ConnectionException` и т.д.
- [x] `@param` описания на всех параметрах
  > Все публичные и приватные методы документированы.
- [x] `@var` на всех свойствах класса
  > Проверено на всех 60 src-файлах.
- [x] Единый язык PHPDoc (только русский или только английский)
  > Русский — единый язык PHPDoc, комментариев, исключений.

## 4. Обработка ошибок

- [x] Иерархия исключений с единым базовым классом SDK
  > `TwentyFourTvException → Authentication, Forbidden, NotFound, Conflict, RateLimit, Validation, Connection, Config`.
- [x] Базовое исключение extends `\Exception`
  > `TwentyFourTvException extends Exception`.
- [x] Специализированные исключения: Authentication, Forbidden, NotFound, Conflict, RateLimit, Validation, Connection, Config
  > 8 leaf-исключений в `src/Exception/`.
- [x] Цепочка `$previous` при re-throw
  > `TwentyFourTvException::__construct(..., Exception $previous = null)` → `parent::__construct($message, $code, $previous)`.
- [x] HTTP контекст в исключениях запросов (httpCode, responseBody, method, endpoint)
  > `TwentyFourTvException::getHttpCode()`, `getResponseBody()`, `getMethod()`, `getEndpoint()`.
- [x] Конфигурационные ошибки через отдельный `ConfigException`
  > `ConfigException` — отсутствующий файл, пустой токен, невалидные параметры.
- [x] Валидационные ошибки через отдельный `ValidationException`
  > `ValidationException` — невалидные входные параметры.
- [x] Все сообщения исключений на едином языке
  > Русский.
- [x] Leaf-исключения помечены `final`
  > Все 8: `final class AuthenticationException`, `final class ConfigException`, `final class ConflictException`, `final class ConnectionException`, `final class ForbiddenException`, `final class NotFoundException`, `final class RateLimitException`, `final class ValidationException`.

## 5. DTO / Value Objects

- [x] Иммутабельность (нет setters после создания)
  > `protected function __construct()` (через `AbstractModel`) + нет setters.
- [x] `fromArray()` factory — статический метод создания из массива API
  > Все DTO: `User::fromArray()`, `Packet::fromArray()`, и т.д.
- [x] `toArray()` serialize — обратная сериализация в массив
  > Все DTO реализуют `toArray()`.
- [x] Типизированные getters
  > `AbstractModel`: `getString`, `getInt`, `getFloat`, `getBool`, `getArray` + `*OrNull` варианты.
- [x] `final` классы — все DTO помечены `final`
  > Все 10: User, Packet, Subscription, Channel, Device, Tag, Message, Balance, Transaction, PaginatedResult.
- [x] Абстрактный базовый класс (`AbstractModel`) с общими хелперами
  > `abstract class AbstractModel` с protected helper-методами + `fromArray()`, `toArray()`, `collection()`.
- [x] Коллекции — типизированные массивы DTO
  > `PaginatedResult` реализует `Countable` + `IteratorAggregate`.

## 6. Безопасность

- [x] SSL валидация по умолчанию
  > `curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true)` в `HttpClient`.
- [x] Маскирование чувствительных данных в логах
  > `TokenMasker::mask()` и `TokenMasker::maskInUrl()` — используются в `HttpClient` и `Config`.
- [x] Отсутствие секретов в логах
  > Логирование через `HttpClient` использует `TokenMasker`.
- [x] Timing-safe сравнение (`hash_equals()`) для callback secret
  > `CallbackHandler::verifySignature()` — `hash_hmac('sha256', $rawBody, $secret)` + `hash_equals($expected, $signature)`.
- [x] Чувствительные файлы (config) в `.gitignore`
  > `*.ini`, `.env`, `*.local` — в `.gitignore`.
- [x] Валидация входных данных перед API-вызовами
  > `AbstractService::validateRequired()` — проверяет обязательные поля перед запросами.
- [x] No redirect (`CURLOPT_FOLLOWLOCATION = false`)
  > `curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false)` в `HttpClient::executeRequest()`.

## 7. Производительность

- [x] Ленивая инициализация (lazy loading) для сервисов
  > `Client::users()`, `Client::packets()` и т.д. создают сервисы при первом обращении.
- [x] Кэширование вычислений
  > Сервисы кэшируются в `$services[]` массиве через `Client::resolveService()`.
- [x] Exponential backoff + jitter для retry-механизма
  > `HttpClient::calculateRetryDelay()` — `pow(2, $attempt) * baseDelay` + `mt_rand() jitter`.
- [x] Ограничение максимального количества retry
  > `Config::max_retries` с валидацией границ.
- [x] Cleanup ресурсов (curl handles) через `finally`
  > `finally { curl_close($ch); }` в `HttpClient::executeRequest()`.
- [x] Circuit Breaker для устойчивости
  > `Http\CircuitBreaker` — паттерн Circuit Breaker с состояниями CLOSED/OPEN/HALF-OPEN.

## 8. Тестирование

- [x] Unit-тесты на все классы
  > 31 тестовых файлов. 224 теста, 439 assertions.
- [x] Тесты DTO (fromArray, toArray, getters)
  > 10 тестов DTO: `UserTest`, `PacketTest`, `SubscriptionTest`, `ChannelTest`, `DeviceTest`, `TagTest`, `MessageTest`, `BalanceTest`, `TransactionTest`, `PaginatedResultTest`.
- [x] Тесты сервисов (все 11 сервисов)
  > 11 тестов сервисов + `AbstractServiceTest` через них.
- [x] Тесты конфигурации
  > `ConfigTest` — defaults, boundaries, INI, ENV.
- [x] Тесты исключений (все типы, коды, сообщения)
  > `ExceptionHierarchyTest` — все 9 типов исключений с `@dataProvider`.
- [x] Code coverage конфигурация в `phpunit.xml.dist`
  > `<source><include><directory>src/</directory></include></source>`.
- [x] Современная схема `phpunit.xml.dist`
  > PHPUnit 11 XML schema, `bootstrap`, `beStrictAboutTestsThatDoNotTestAnything`.
- [x] Параметризованные тесты (`@dataProvider`) где уместно
  > `@dataProvider` в `ExceptionHierarchyTest`.
- [x] Тесты работают без внешних API (моки / тестовые дублёры)
  > Все тесты offline — mock HttpClient.
- [x] Strict mode
  > `beStrictAboutTestsThatDoNotTestAnything="true"` в `phpunit.xml.dist`.

## 9. Инфраструктура и DX (Developer Experience)

- [x] `composer.json` с PSR-4 autoload и scripts
  > `"TwentyFourTv\\": "src/"`, scripts: `test`, `test-coverage`, `analyse`, `cs-check`, `cs-fix`, `security`.
- [x] Composer scripts: `test`, `analyse`, `cs-check`, `cs-fix`
  > 6 scripts в `composer.json`.
- [x] PHPStan — уровень ≥5
  > `phpstan.neon`: `level: 6`, `phpVersion: 50600`.
- [x] PHP-CS-Fixer — актуальная версия (v3+) с `.php-cs-fixer.dist.php`
  > `.php-cs-fixer.dist.php` с PSR-12 правилами.
- [x] PHPUnit — актуальная версия с современной XML-схемой
  > PHPUnit 11, `phpunit.xml.dist` с XML schema.
- [x] CI/CD — GitHub Actions с матрицей PHP версий
  > `.github/workflows/ci.yml` — 8 PHP версий (5.6–8.3), `fail-fast: false`.
- [x] CI — отдельные jobs для тестов, статического анализа, code style
  > 3 jobs: `phpunit`, `phpstan`, `code-style`.
- [x] `.gitignore` — vendor, coverage, cache, IDE, OS
  > vendor, build, .phpunit.result.cache, *.ini, .env, .idea, .vscode.
- [x] CHANGELOG — формат Keep a Changelog
  > `CHANGELOG.md` в корне проекта.
- [x] README — описание, установка, примеры, бейджи
  > `README.md` с бейджами, установкой, примерами, ссылками на документацию.

## 10. Документация

- [x] Полные гайды в `docs/` (quickstart, конфигурация, API reference)
  > 5 гайдов: `quickstart.md`, `configuration.md`, `api-reference.md`, `callbacks.md`, `examples.md`.
- [x] Примеры в `docs/examples.md` + `examples/*.php`
  > `docs/examples.md` — 8 секций с полными сценариями. 3 PHP-файла в `examples/`.
- [x] PHPDoc class-level с `<code>` примерами
  > `Client`, `ClientFactory`, `CallbackHandler`, `TokenMasker` — все с `<code>` примерами.
- [x] `@since` аннотации на класс-уровне
  > Все 60 src-файлов имеют `@since 1.0.0`.
- [x] `@method` аннотации для magic-методов
  > N/A — проект не использует magic-методы (все методы явные).
- [x] Отсутствие устаревших/удалённых ссылок в документации
  > Проверено.
- [x] Навигационный `docs/README.md`
  > Создан — ссылки на все doc-файлы + внешние ресурсы.

## 11. Единый язык

- [x] PHPDoc описания на едином языке
  > Русский — во всех PHPDoc блоках.
- [x] Сообщения исключений на едином языке
  > Русский — все throw messages.
- [x] Сообщения логирования на едином языке
  > Русский — `'24HTV callback: входящий запрос'`, `'24HTV AUTH: абонент найден по IP'`.
- [x] Inline-комментарии на едином языке
  > Русский — все inline-комментарии.
- [x] `@since` аннотации
  > Формат `@since X.Y.Z` — язык-нейтральный.
- [x] Технические термины допускаются на английском
  > Используются: token, HTTP, JSON, API, cURL, callback, SSL, PSR, DTO — все общепринятые.

## 12. Code Style

- [x] Единый стиль кода (PSR-12)
  > PSR-12 с PHP-CS-Fixer. Проверяется в CI.
- [x] Short array syntax `[]` вместо `array()`
  > `[]` — во всём проекте: src, docs, examples, README.
- [x] Упорядоченные `use`-импорты
  > Алфавитный порядок во всех файлах.
- [x] Отсутствие неиспользуемых импортов
  > Проверено.
- [x] Trailing comma в multiline arrays
  > Присутствует во всех multiline arrays.
- [x] Именованные константы вместо magic numbers
  > `Config` — именованные defaults, `ApiEndpoints` — все 60+ endpoint-ов как константы.
- [x] Единый стиль конкатенации строк (пробелы вокруг `.`)
  > Единообразно с пробелами.

## 13. Версионирование

- [x] Semantic Versioning (MAJOR.MINOR.PATCH)
  > `SdkVersion::VERSION = '1.0.0'`, `@since 1.0.0`.
- [x] CHANGELOG с документированными изменениями
  > `CHANGELOG.md` с описанием всех фич 1.0.0.
- [x] `@since` аннотации при изменении API
  > На всех 60 src-файлах.
- [x] `@deprecated` для устаревших методов
  > `CallbackResponse::send()` помечен `@deprecated` в пользу `ResponseEmitter::emit()`.

## 14. Robustness / Edge Cases

- [x] Guard clauses — early return / throw для невалидных входов
  > `Config::__construct()` — throw для пустого токена. `AbstractService::validateRequired()` — early throw.
- [x] `finally`-блоки — cleanup ресурсов при любом исходе
  > `HttpClient::executeRequest()`: `finally { curl_close($ch); }`.
- [x] Defensive coding — `isset()` проверки для необязательных полей API
  > Все DTO используют `isset()` + `is_array()` в `hydrate()`. `CallbackHandler` — проверки `$params`.
- [x] Unicode в JSON — `JSON_UNESCAPED_UNICODE` для корректной кириллицы
  > `HttpClient`, `CallbackResponse` — `JSON_UNESCAPED_UNICODE`.
- [x] Unused code cleanup
  > `CallbackHandler::$config` — используется в `verifySignature()` для чтения `callback.secret`.
