<?php

namespace Aftermath;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Kernel;
use Aftermath\Middleware\AftermathTracingMiddleware;
use Aftermath\Transport\HttpTransport;
use Aftermath\Tracing\TracingManager;
use Aftermath\Tracing\AftermathTracingManager;

class AftermathServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigs();
        $this->registerServices();
        $this->registerLoggingChannel();
    }

    public function boot(Kernel $kernel): void
    {
        $this->publishes([
            __DIR__ . '/../config/aftermath.php' => $this->app->configPath('aftermath.php'),
        ], 'config');

        $this->registerMiddleware($kernel);

        $this->app->terminating(function () {
            if (config('aftermath.tracing.enabled', true) || app(Aftermath::class)->exceptionWasReported()) {
                app(TracingManager::class)->flush();
                app(Aftermath::class)->resetExceptionReported();
            }
        });
    }

    protected function mergeConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath.php', 'aftermath');
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath_internal.php', 'aftermath_internal');
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging');
    }

    protected function registerServices(): void
    {
        $this->app->singleton('aftermath', function () {
            return new Aftermath(new HttpTransport());
        });

        if (config('aftermath.tracing.enabled', true)) {
            $this->app->singleton(TracingManager::class, function () {
                return new (config('aftermath.tracing.manager_class'))();
            });
        }
    }

    protected function registerLoggingChannel(): void
    {
        $config = $this->app->get(Repository::class);

        if (!array_key_exists('aftermath', $config->get('logging.channels', []))) {
            $channels = $config->get('logging.channels', []);
            $channels['aftermath'] = [
                'driver' => 'monolog',
                'handler' => \Aftermath\Logging\AftermathLoggingHandler::class,
                'level' => 'debug',
            ];

            $config->set('logging.channels', $channels);
        }
    }

    protected function registerMiddleware(Kernel $kernel): void
    {
        if (config('aftermath.tracing.enabled', true)) {
            $this->registerTracingMiddleware($kernel);
        }
    }

    protected function registerTracingMiddleware(Kernel $kernel): void
    {
        $kernel->pushMiddleware(AftermathTracingMiddleware::class);
    }
}