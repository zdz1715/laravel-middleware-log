<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Closure;

class WriteLogMiddleware extends Middleware
{

    public static $levels = [
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    public function handle($request, Closure $next)
    {
        $response =  $next($request);
        // 注册回调事件
        $handleKey = $this->getConfig('handle');
        if ($this->checkRoute() && is_callable($this->getConfig($handleKey))) {
            call_user_func_array($this->getConfig($handleKey), [
                $request,
                $response,
                $this->getLogLevel(),
                $this->getException($response->exception ?? '', $this->getExcludeException())
            ]);
        }
        return $response;
    }

    /**
     * @param $exception
     * @param array $excludeException
     * @return string
     */
    public function getException($exception, array $excludeException) {
        if (!$exception) {
            return '';
        }

        foreach ($excludeException as $key => $value) {
            if ($exception instanceof $value) {
                return '';
            }
        }
        return $exception;
    }


    /**
     * @return array
     */
    public function getExcludeException(): array {
        return $this->getConfig('exclude_exception', []);
    }


    /**
     * @param string $default
     * @return string
     */
    public function getLogLevel($default = 'debug'): string {
        $level = $this->getConfig('log_level', $default);
        return in_array($level, self::$levels) ? $level : $default;
    }
}
