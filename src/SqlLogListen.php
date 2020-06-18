<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;

class SqlLogListen extends Middleware
{
    public function handle(QueryExecuted $event) {
        // 注册回调事件
        $handle = $this->getConfig('sql');
        if (is_callable($handle)) {
            call_user_func_array($handle, [ $event ]);
        }
    }

}
