<?php

namespace Aftermath;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Aftermath\Transport\HttpTransport;

class AftermathServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigs();
        $this->registerFacade();
        $this->registerLoggingChannel();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/aftermath.php' => $this->app->configPath('aftermath.php'),
        ], 'config');
    }

    protected function mergeConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath.php', 'aftermath');
        $this->mergeConfigFrom(__DIR__ . '/../config/aftermath_internal.php', 'aftermath_internal');
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging');
    }

    protected function registerFacade(): void
    {
        $this->app->singleton('aftermath', function () {
            return new Aftermath(new HttpTransport());
        });
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
}