<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Events\QueryExecuted;

class SqlLogListen extends Middleware
{
    /**
     * @param QueryExecuted $event
     * @throws BindingResolutionException
     */
    public function handle(QueryExecuted $event) {
        $this->checkRoute() && $this->handler->recordSql($event);
    }

}
