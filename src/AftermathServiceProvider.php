<?php

namespace Aftermath;

use Illuminate\Support\ServiceProvider;
use Aftermath\Transport\HttpTransport;

class AftermathServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath.php', 'aftermath');
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath_internal.php', 'aftermath_internal');
        
        $this->registerLoggingChannel();

        $this->app->singleton('aftermath', function () {
            return new Aftermath(new HttpTransport());
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/aftermath.php' => $this->app->configPath('aftermath.php'),
        ], 'config');
    }

    protected function registerLoggingChannel(): void
    {
        $this->app->make('log')->extend('aftermath', function ($app, $config) {
            return new \Monolog\Logger('aftermath', [
                new \Aftermath\Logging\AftermathLoggingHandler(),
            ]);
        });
    }
}