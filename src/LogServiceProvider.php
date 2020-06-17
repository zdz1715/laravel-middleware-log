<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{

    public const CONFIG_FILENAME = 'log-middleware';

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path(self::CONFIG_FILENAME. '.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', self::CONFIG_FILENAME);
    }
}