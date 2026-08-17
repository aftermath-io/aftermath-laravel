# Aftermath Laravel Package Instructions

## Commands

This is a Composer library; it has no frontend build, test suite, lint configuration, or Composer scripts.

```bash
composer install
composer validate --strict
find src config -name '*.php' -print0 | xargs -0 -n1 php -l
```

No single-test command exists because the repository does not currently contain a test runner or tests.

## Architecture

- This package ships a Laravel service provider through Composer package discovery (`Aftermath\AftermathServiceProvider`). The provider merges package configuration, binds the `aftermath` singleton, publishes only `config/aftermath.php`, and adds the `aftermath` Monolog channel only when an application has not already defined one.
- Host applications opt into exception capture by calling `Aftermath::handles($exceptions)` from Laravel's `withExceptions` configuration. That reportable callback resolves the singleton and sends an `ExceptionEvent`.
- `AftermathLoggingHandler` is the logging path: the `aftermath` channel delivers Monolog 3 `LogRecord` instances to `LogEvent`, then resolves the same singleton to send them.
- `ExceptionEvent` and `LogEvent` own the ingest payload contract. `HttpTransport` posts their arrays to the production ingest URL, or to `http://localhost:8081` when `aftermath_internal.debug` is enabled.

## Package Conventions

- Keep the external ingest payload stable: timestamps are UTC ISO 8601 strings; exceptions use `payload.level` plus `payload.exception.values`; logs use `payload.level` plus `payload.log`; PHP and additional context belong in `payload.metadata`.
- Reporting is intentionally best-effort: `Aftermath` suppresses transport and payload failures unless `aftermath_internal.debug` is true, in which case it rethrows to make local integration failures visible. Preserve this behavior for both exception and log paths.
- Use the `aftermath` config namespace for user-facing settings and `aftermath_internal` only for package debugging. Public settings are environment-backed by `AFTERMATH_DSN`, `AFTERMATH_ENVIRONMENT`, `AFTERMATH_ENABLED`, `AFTERMATH_LOGGING_ENABLED`, and `AFTERMATH_LOGGING_LEVEL`.
- Respect application logging customization: do not overwrite an existing `logging.channels.aftermath` definition. The default handler receives all records at Monolog `Debug`, then applies `aftermath.logging.enabled` and `aftermath.logging.level` itself.
- The facade accessor and container binding are the string `aftermath`; distinguish `Aftermath\Facade\Aftermath` from the `Aftermath\Aftermath` service class when importing classes.
- The package targets PHP 8.2+ and Laravel 11–13 support components. Logging code uses Monolog 3 APIs (`LogRecord` and `Level`).
