<?php
namespace zdz\LaravelMiddlewareLog\handle;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class AbstractHandler
{

    /**
     * @var
     */
    protected $config;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * AbstractHandler constructor.
     * @param Application $app
     * @param array $config
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Application $app, $config = [])
    {
        $this->config = $config;
        $this->app = $app;
        $this->request = $this->app->make('request');
    }




    /**
     * @param $response
     * @param $exception
     * @param array $fields
     */
    abstract public function record($response, $exception, array $fields): void ;



    /**
     * @param QueryExecuted $event
     */
    abstract public function recordSql(QueryExecuted $event): void ;

    /**
     * @param string $level
     * @param string $message
     */
    abstract public function handle(string $level, string $message): void ;

    /**
     * @return array
     */
    public function getAllConfig(): array {
        return $this->config;
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }

}
