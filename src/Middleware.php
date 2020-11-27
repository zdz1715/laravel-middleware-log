<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use zdz\LaravelMiddlewareLog\handler\AbstractHandler;
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
     * @var AbstractHandler
     */
    protected $handler;

    /**
     * @var Request
     */
    protected $request;


    /**
     * Middleware constructor.
     * @param Application $application
     * @param Repository $config
     * @throws BindingResolutionException
     */
    public function __construct(Application $application, Repository $config)
    {
        $this->application = $application;
        $this->config = $config;
        $this->handler = $this->application->make('LaravelMiddlewareLogHandler');
        $this->request = $this->application->make('request');
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null) {
        return $this->config->get(LogServiceProvider::CONFIG_FILENAME. '.' .$key, $default);
    }


    /**
     * @return bool
     */
    public function checkRoute(): bool {
        return !$this->request->is(...$this->getConfig('exclude_route', []));
    }

}
