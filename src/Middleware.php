<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use zdz\LaravelMiddlewareLog\handle\AbstractHandler;
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
     * @throws BindingResolutionException
     */
    public function checkRoute(): bool {
        return !in_array($this->getPathInfo(), $this->getConfig('exclude_route', []));
    }

    /**
     * @return mixed
     * @throws BindingResolutionException
     */
    public function getPathInfo(): string {
        return $this->application->make('request')->path();
    }

    /**
     * @param $exception
     * @return string
     */
    public function getException($exception) {
        if (!$exception) {
            return '';
        }
        foreach ($this->getConfig('exclude_exception', []) as $key => $value) {
            if ($exception instanceof $value) {
                return '';
            }
        }
        return $exception;
    }

    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function getExcludeRouteFields(): array {
        return $this->getConfig('exclude_route_fields')[$this->getPathInfo()] ?? [];
    }

    /**
     * @return mixed
     * @throws BindingResolutionException
     */
    public function getLogFields() {
        $excludeRouteFields = $this->getExcludeRouteFields();
        $logFields = $this->getConfig('log_fields');
        if ($excludeRouteFields) {
            return array_diff_key($logFields, array_flip($excludeRouteFields));
        }
        return $logFields;
    }
}
