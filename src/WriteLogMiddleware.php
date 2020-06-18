<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Closure;

class WriteLogMiddleware extends Middleware
{
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
            call_user_func_array($this->getConfig($handleKey), [ $request, $response]);
        }
        return $response;
    }
}
