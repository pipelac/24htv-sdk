# Участие в разработке

Спасибо за интерес к участию в проекте **24часаТВ PHP SDK**!

## Требования к окружению

- **PHP** 5.6+ (runtime код SDK)
- **PHP** 8.2+ (dev-инструменты: PHPUnit 11, PHPStan 2, PHP-CS-Fixer 3)
- **Extensions:** `curl`, `json`, `mbstring`
- **Composer** для управления зависимостями

> **Важно:** Код SDK **не должен** использовать синтаксис PHP 7.0+ (scalar type hints, return types, null coalescing `??`, spaceship `<=>`, anonymous classes и т.д.)

## Установка

```bash
git clone https://github.com/twentyfourtv/sdk.git
cd sdk
composer install
composer test
```

## Код-стайл

Проект следует **PSR-12** с short array syntax `[]`.

```bash
# Проверка стиля
composer cs-check

# Автоматическое исправление
composer cs-fix
```

## Тестирование

```bash
# Запуск всех тестов
composer test

# С покрытием (требуется Xdebug)
composer test-coverage
```

## Статический анализ

PHPStan level 6 с `phpVersion: 50600`.

```bash
composer analyse
```

## PHPDoc

- Русский язык для описаний и комментариев
- `@param`, `@return`, `@throws` на всех публичных методах
- `@var` на всех свойствах
- `@since` на уровне классов

## Pull Request Checklist

- [ ] Все тесты проходят (`composer test`)
- [ ] PHPStan чистый (`composer analyse`)
- [ ] Code style OK (`composer cs-check`)
- [ ] PHPDoc обновлён для новых/изменённых публичных методов
- [ ] Новые классы помечены `final` (если не предназначены для наследования)
- [ ] Новые leaf-exceptions наследуют `TwentyFourTvException`
- [ ] DTO следуют паттерну: `private __construct` + `fromArray()` + `toArray()`
- [ ] Обновлена документация (если нужно)
- [ ] Обновлён CHANGELOG.md

## Архитектурные принципы

Подробный список требований: [docs/REQUIREMENTS_SENIOR_PROD.md](docs/REQUIREMENTS_SENIOR_PROD.md)

- **Immutability** — Config и DTO без setters
- **DI** — зависимости через конструктор
- **Final by default** — все leaf-классы `final`
- **PSR-12** — единый стиль кода, `[]` вместо `array()`
- **PHP 5.6 compat** — без return type declarations, scalar type hints
