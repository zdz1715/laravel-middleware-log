<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
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

    /**
     * @param string $uri
     * @return bool
     * @throws BindingResolutionException
     */
    public function checkRoute(): bool {
        return !in_array($this->getPathInfo(), $this->getConfig('exclude_route'));
    }

    /**
     * @return mixed
     * @throws BindingResolutionException
     */
    public function getPathInfo() {
        return $this->application->make('request')->getPathInfo();
    }
}
