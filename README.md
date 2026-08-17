# Aftermath for Laravel

Capture unhandled Laravel exceptions in [Aftermath](https://aftermath.dev).

## Requirements

- PHP 8.2 or later
- Laravel 11, 12, or 13

## Installation

Install the package with Composer:

```bash
composer require aftermath/aftermath-laravel
```

Laravel discovers the package service provider automatically.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Aftermath\AftermathServiceProvider" --tag=config
```

Set the DSN from your Aftermath project in `.env`:

```dotenv
AFTERMATH_DSN=your-project-dsn
```

The package uses your Laravel application environment by default. You can override
it or disable event reporting when needed:

```dotenv
AFTERMATH_ENVIRONMENT=production
AFTERMATH_ENABLED=true
```

### Logging

The package automatically registers an `aftermath` logging channel. Send log
entries to it to capture them in Aftermath:

```php
use Illuminate\Support\Facades\Log;

Log::channel('aftermath')->error('Payment processing failed', [
    'order_id' => $order->id,
]);
```

Enable logging by adding it to the log stack.

```dotenv
LOG_STACK=single,aftermath
```

You can disable it or change the level by setting the .env settings:

```dotenv
AFTERMATH_LOGGING_ENABLED=true
AFTERMATH_LOGGING_LEVEL=warning
```

When `AFTERMATH_LOGGING_LEVEL` is not set, the package uses `LOG_LEVEL`, or
`debug` if neither value is configured.

## Event format

Events are sent to `POST /api/ingest/{dsn}` with a UUID event ID, UTC ISO 8601
timestamp, environment, type, and payload. Exception events use
`payload.exception.values` with Sentry-compatible stack frames; chained PHP
exceptions are sent as separate values. Log events use `payload.log` with the
Monolog severity, message, formatted message when available, and channel.

Laravel log context and Monolog extra data are retained under
`payload.metadata`; both event types include PHP runtime metadata.

## Integration

Register Aftermath in your application's exception configuration. In
`bootstrap/app.php`, import the exception configurator and the package class:

```php
use Aftermath\Aftermath;
use Illuminate\Foundation\Configuration\Exceptions;
```

Then register the handler in `withExceptions`:

```php
->withExceptions(function (Exceptions $exceptions): void {
    Aftermath::handles($exceptions);
})
```

Aftermath reports exceptions handled by Laravel's exception reporting pipeline.

## Local development

Set `aftermath_internal.debug` to `true` in your application configuration to send
events to `http://localhost:8081` and rethrow errors encountered while reporting.

## License

This package is licensed under the MIT License.
