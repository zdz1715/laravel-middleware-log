<?php
namespace zdz\LaravelMiddlewareLog;

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
     */
    public function handle($request, Closure $next)
    {
        $response =  $next($request);
        // 注册回调事件
        $handle = $this->getConfig('handle');
        if (is_callable($handle)) {
            call_user_func_array($handle, [ $request, $response]);
        }
        return $response;
    }
}