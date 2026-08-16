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

Log reporting is enabled by default and captures entries at the configured
minimum level. Configure or disable it in `.env`:

```dotenv
AFTERMATH_LOGGING_ENABLED=true
AFTERMATH_LOGGING_LEVEL=warning
```

When `AFTERMATH_LOGGING_LEVEL` is not set, the package uses `LOG_LEVEL`, or
`debug` if neither value is configured.

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
