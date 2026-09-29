# Laravel Observability

[![Latest Version on Packagist](https://img.shields.io/packagist/v/furushka/laravel-observability.svg?style=flat-square)](https://packagist.org/packages/furushka/laravel-observability)
[![Total Downloads](https://img.shields.io/packagist/dt/furushka/laravel-observability.svg?style=flat-square)](https://packagist.org/packages/furushka/laravel-observability)
[![License](https://img.shields.io/packagist/l/furushka/laravel-observability.svg?style=flat-square)](https://packagist.org/packages/furushka/laravel-observability)

A lightweight alternative to Laravel Telescope: records HTTP traces, SQL queries, and exceptions into the database and displays them in a simple Windows 95–styled UI.

[🇷🇺 Русская версия ниже](#русская-версия)

---

## Features

-   **HTTP traces** — method, path, status, duration
-   **SQL spans** — query, bindings, duration, slow-query flag
-   **Exception spans** — class, message, file, line, stack trace
-   **Sampling** — record only a fraction of requests in production
-   **Auto-prune** — `observability:prune` command to delete old records
-   **Log integration** — `trace_id` is added to the log context via `Log::shareContext`

## Requirements

-   PHP `^8.2`
-   Laravel `11`, `12` or `13`

## Installation

Install the package via Composer:

```bash
composer require furushka/laravel-observability
```
Run the migrations:

```bash
php artisan migrate
```

## Configuration

Key options can be set in `.env`:

```env
OBSERVABILITY_ENABLED=true
OBSERVABILITY_SAMPLE_RATE=1.0
OBSERVABILITY_SLOW_QUERY_MS=200
OBSERVABILITY_UI_ENABLED=true
OBSERVABILITY_UI_PATH=observability
OBSERVABILITY_RETENTION_DAYS=7
```

> **Important:** On a public site, always protect the UI with authentication. In `config/observability.php` set `ui.middleware` to `['web', 'auth']`.

### Ignored paths

By default, the package does not trace its own UI or common debug routes:

```php
'ignore_paths' => [
    'observability',
    'observability/*',
    'up',
    'telescope',
    'telescope/*',
    '_debugbar',
    '_debugbar/*',
],
```

## Usage

Open in your browser:

```
http://your-app.test/observability
```

You will see a list of traces. Click a trace to view its spans (SQL queries and exceptions).

## Auto-prune

To automatically delete old traces, add the following to your application's `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('observability:prune')->daily();
```

Or run it manually:

```bash
php artisan observability:prune --days=7
```

## Storage

By default, data is stored in the database. The package is built on top of a `StorageInterface`, so you can implement your own driver (Redis, files, remote APM) and bind it in the service container:

```php
$this->app->singleton(StorageInterface::class, fn () => new RedisStorage());
```

## Security

If you discover any security-related issues, please email the author instead of using the issue tracker.

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.

---

## Русская версия

Легковесная альтернатива Laravel Telescope: записывает HTTP-трейсы, SQL-запросы и исключения в базу данных и отображает их в простом UI, стилизованном под Windows 95.

## Возможности

-   **HTTP-трейсы** — метод, путь, статус, длительность
-   **SQL-спаны** — запрос, bindings, длительность, флаг медленного запроса
-   **Спаны исключений** — класс, сообщение, файл, строка, стек-трейс
-   **Сэмплирование** — записывать только часть запросов на продакшене
-   **Автоочистка** — команда `observability:prune` для удаления старых записей
-   **Интеграция с логами** — `trace_id` добавляется в контекст логов через `Log::shareContext`

## Требования

-   PHP `^8.2`
-   Laravel `11`, `12` или `13`

## Установка

Установите пакет через Composer:

```bash
composer require furushka/laravel-observability
```

Примените миграции:

```bash
php artisan migrate
```

## Настройка

Основные параметры задаются в `.env`:

```env
OBSERVABILITY_ENABLED=true
OBSERVABILITY_SAMPLE_RATE=1.0
OBSERVABILITY_SLOW_QUERY_MS=200
OBSERVABILITY_UI_ENABLED=true
OBSERVABILITY_UI_PATH=observability
OBSERVABILITY_RETENTION_DAYS=7
```

> **Важно:** На публичном сайте обязательно закройте UI авторизацией. В `config/observability.php` для `ui.middleware` укажите `['web', 'auth']`.

### Игнорируемые пути

По умолчанию пакет не трейсит свой собственный UI и типовые отладочные роуты:

```php
'ignore_paths' => [
    'observability',
    'observability/*',
    'up',
    'telescope',
    'telescope/*',
    '_debugbar',
    '_debugbar/*',
],
```

## Использование

Откройте в браузере:

```
http://your-app.test/observability
```

Вы увидите список трейсов. Кликните на трейс, чтобы посмотреть его спаны (SQL-запросы и исключения).

## Автоочистка

Чтобы старые трейсы удалялись автоматически, добавьте в `routes/console.php` вашего приложения:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('observability:prune')->daily();
```

Или запустите вручную:

```bash
php artisan observability:prune --days=7
```

## Хранилище

По умолчанию данные хранятся в базе данных. Пакет построен на `StorageInterface`, поэтому вы можете реализовать собственный драйвер (Redis, файлы, внешний APM) и зарегистрировать его в контейнере:

```php
$this->app->singleton(StorageInterface::class, fn () => new RedisStorage());
```

## Безопасность

Если вы обнаружили проблему безопасности, напишите автору на почту, а не через issue tracker.

## Лицензия

The MIT License (MIT). Подробности в файле [LICENSE](LICENSE).
