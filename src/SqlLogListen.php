<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;

class SqlLogListen extends Middleware
{
    /**
     * @param QueryExecuted $event
     * @throws BindingResolutionException
     */
    public function handle(QueryExecuted $event) {
        // 注册回调事件
        $handle = $this->getConfig('sql');
        if ($this->checkRoute() && is_callable($handle)) {
            call_user_func_array($handle, [ $event ]);
        }
    }

}
