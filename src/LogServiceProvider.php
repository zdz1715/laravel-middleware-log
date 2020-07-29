<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Foundation\Application;
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
        $config = $this->app['config']->get(self::CONFIG_FILENAME, []);

        $this->app->singleton('LaravelMiddlewareLogHandler', function (Application $app) use ($config){
            return new $config['handler']($app, $config);
        });
    }
}
