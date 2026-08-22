<?php

namespace Aftermath;

use Aftermath\Instrumentation\DatabaseInstrumentation;
use Aftermath\Instrumentation\HttpInstrumentation;
use Aftermath\EventBuffer;
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
        $this->mergeConfigs()
            ->registerServices()
            ->registerLoggingChannel();
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
            if (config('aftermath.enabled', true)) {
                app(Aftermath::class)->flushEventBuffer();
            }

            if (config('aftermath.tracing.enabled', true) || app(Aftermath::class)->exceptionWasReported()) {
                app(TracingManager::class)->flush();
                app(Aftermath::class)->resetExceptionReported();
            }
        });
    }

    protected function mergeConfigs(): self
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath.php', 'aftermath');
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath_internal.php', 'aftermath_internal');
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging');

        return $this;
    }

    protected function registerServices(): self
    {
        $this->app->bind(Transport::class, config('aftermath.transport'));
        $this->app->singleton(EventBuffer::class, config('aftermath.event_buffer'));

        $this->app->singleton('aftermath', function () {
            return new Aftermath(app(Transport::class), app(EventBuffer::class));
        });

        if (config('aftermath.tracing.enabled', true)) {
            $this->app->singleton(TracingManager::class, function () {
                return new (config('aftermath.tracing.manager_class'))();
            });
        }

        return $this;
    }

    protected function registerLoggingChannel(): self
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

        return $this;
    }

    protected function registerMiddleware(Kernel $kernel): self
    {
        if (config('aftermath.tracing.enabled', true)) {
            $this->registerTracingMiddleware($kernel);
        }

        return $this;
    }

    protected function registerTracingMiddleware(Kernel $kernel): self
    {
        $kernel->prependMiddleware(AftermathTracingMiddleware::class);

        return $this;
    }

    protected function registerInstrumentation(): self
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

        return $this;
    }
}
