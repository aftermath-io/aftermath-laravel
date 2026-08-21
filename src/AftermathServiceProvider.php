<?php

namespace Aftermath;

use Aftermath\Instrumentation\DatabaseInstrumentation;
use Aftermath\Instrumentation\HttpInstrumentation;
use Aftermath\Middleware\AftermathTracingMiddleware;
use Aftermath\Tracing\TracingManager;
use Aftermath\Transport\Transport;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

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

        if (config('aftermath.tracing.enabled', true)) {
            $this->registerInstrumentation();
        }

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
        $this->app->bind(Transport::class, config('aftermath.transport'));
        
        $this->app->singleton('aftermath', function () {
            return new Aftermath(app(Transport::class));
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
        $kernel->prependMiddleware(AftermathTracingMiddleware::class);
    }

    protected function registerInstrumentation(): void
    {
        $instrumentationClasses = config('aftermath.instrumentation', [
            DatabaseInstrumentation::class,
            HttpInstrumentation::class,
        ]);

        foreach ($instrumentationClasses as $instrumentationClass) {
            if (class_exists($instrumentationClass)) {
                $instrumentation = $this->app->make($instrumentationClass);
                if ($instrumentation instanceof \Aftermath\Instrumentation\Instrumentation) {
                    $instrumentation->boot();
                }
            }
        }
    }
}
