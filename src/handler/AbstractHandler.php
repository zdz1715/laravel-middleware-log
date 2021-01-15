<?php
namespace zdz\LaravelMiddlewareLog\handler;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Exception;

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
     * @throws BindingResolutionException
     */
    public function __construct(Application $app, $config = [])
    {
        $this->config = $config;
        $this->app = $app;
        $this->request = $this->app->make('request');
    }



    /*
     * @param QueryExecuted $event
     */
    abstract public function recordSql(QueryExecuted $event): void ;


    /**
     * @param string $level
     * @param string $message
     * @param $response
     */
    abstract public function handle(string $level, string $message, $response): void ;

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



    /**
     * @param $response
     * @param string $default
     * @return array
     */
    protected function parseLogFields($response, $default = ""): array
    {
        $data = [];
        /**
         * @var Response $response
         * @var Exception $exception
         */
        $exception = $this->getException($response->exception ?? null);
        foreach ($this->getLogFields() as $key => $val) {
            if ($exception && $this->isExcludeExceptionField($key)) {
                $data[$key] = $default;
                continue;
            }
            $className = array_shift($val);
            $method = array_shift($val);
            $attr = array_shift($val);

            $class = $className == 'response' ? $response : $this->request;

            if (!is_null($attr) && property_exists($class, $attr)) {
                $class = $class->$attr;
            }

            $data[$key] = $this->execMethod($class, $method) ?? $default;
        }

        return $this->addFields($data, $response, $exception);
    }

    private function addFields($array, $response, $exception): array
    {
        /**
         * @var Response $response
         * @var Exception $exception
         */
        return array_merge($array, [
            '_time_' => date('Y-m-d H:i:s'),
            'exec_ms' => defined('LARAVEL_START') ?
                round((microtime(true) - LARAVEL_START) * 1000, 2) :  0,
            'exec_exception' => $exception,
        ]);
    }

    /**
     * @param $class
     * @param $method
     * @return mixed
     */
    private function execMethod($class, $method) {
        if (!is_null($method) && method_exists($class, $method)) {
            return $class->$method();
        }
        return $class;
    }

    /**
     * @param Exception|null|string $exception
     * @param string $default
     * @return Exception|string
     */
    protected function getException($exception, $default = "")
    {
        if (!$exception) {
            return $default;
        }
        foreach ($this->getConfig('exclude_exception', []) as $key => $value) {
            if ($exception instanceof $value) {
                return $default;
            }
        }
        return $exception;
    }


    /**
     * @param $field
     * @return bool
     */
    public function isExcludeExceptionField($field): bool
    {
        return in_array($field, $this->getConfig('exclude_exception_fields', []));
    }

    /**
     * @param mixed ...$patterns
     * @return string 返回匹配的路由名称
     */
    public function isRoute(...$patterns): string {
        $path = $this->request->decodedPath();

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $path)) {
                return $pattern;
            }
        }

        return '';
    }

    /**
     * @return array
     */
    public function getExcludeRouteFields(): array {
        $excludeRouteFields = $this->getConfig('exclude_route_fields', []);
        $pathKey = $this->isRoute(...array_keys($excludeRouteFields));
        return $excludeRouteFields[$pathKey] ?? [];
    }

    /**
     * @return mixed
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
