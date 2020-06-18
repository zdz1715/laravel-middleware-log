<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use zdz\LaravelMiddlewareLog\LogServiceProvider;

abstract class Middleware
{

    /**
     * @var Application
     */
    protected $application;
    /**
     * @var Repository
     */
    protected $config;

    /**
     * Middleware constructor.
     *
     * @param Application $application
     * @param Repository $config
     */
    public function __construct(Application $application, Repository $config)
    {
        $this->application = $application;
        $this->config = $config;
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function getConfig($key, $default = null) {
        return $this->config->get(LogServiceProvider::CONFIG_FILENAME. '.' .$key, $default);
    }

    public function checkRoute(string $uri): bool {
        return !in_array($uri, $this->getConfig('exclude_route'));
    }
}
