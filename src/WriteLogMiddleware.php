<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Closure;

class WriteLogMiddleware extends Middleware
{



    /**
     *  Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): Response
    {
        /**
         * @var Response $response
         */
        $response =  $next($request);
        if ($this->checkRoute()) {
            $this->handler->handle($this->getConfig('log_level'), $this->getConfig('log_message'), $response);
        }
        return $response;
    }



}
