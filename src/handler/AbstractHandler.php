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




    /**
     * @param $response
     */
    abstract public function record($response): void ;



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


    /**
     * @param $response
     * @param $exception
     * @return array
     */
    protected function parseLogFields($response, $exception): array
    {
        $data = [];
        foreach ($this->getLogFields() as $key => $val) {
            if ($exception && $this->isExcludeExceptionField($key)) {
                $data[$key] = '';
                continue;
            }
            $className = array_shift($val);
            $method = array_shift($val);
            $attr = array_shift($val);

            $class = $className == 'response' ? $response : $this->request;

            if (!is_null($attr) && property_exists($class, $attr)) {
                $logValue = $class->$attr;
                if (!is_null($method) && method_exists($logValue, $method)) {
                    $logValue = $logValue->$method();
                }
            } else if (!is_null($method) && method_exists($class, $method)) {
                $logValue = $class->$method();
            }

            $data[$key] = $logValue ?? null;
        }

        return $data;
    }

    /**
     * @param ?Exception $exception
     * @return mixed
     */
    protected function getException(?Exception $exception): ?Exception
    {
        if (!$exception) {
            return null;
        }
        foreach ($this->getConfig('exclude_exception', []) as $key => $value) {
            if ($exception instanceof $value) {
                return null;
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
