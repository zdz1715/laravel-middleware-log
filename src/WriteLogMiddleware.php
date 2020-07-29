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
        if ($this->checkRoute()) {
            $this->handler->record(
                $request,
                $response,
                $this->getException($response->exception),
                $this->getLogFields()
            );
            $this->handler->handle($this->getConfig('log_level'), $this->getConfig('log_message'));
        }
        return $response;
    }



}
