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
                $this->getLogLevel()
            ]);
        }
        return $response;
    }

    /**
     * @param string $default
     * @return mixed|string
     */
    public function getLogLevel($default = 'debug') {
        $level = $this->getConfig('log_level', $default);
        return in_array($level, self::$levels) ? $level : $default;
    }
}
