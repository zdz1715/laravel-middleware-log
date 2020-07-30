<?php
namespace zdz\LaravelMiddlewareLog;

use Illuminate\Database\Events\QueryExecuted;

class SqlLogListen extends Middleware
{
    /**
     * @param QueryExecuted $event
     */
    public function handle(QueryExecuted $event) {
        $this->checkRoute() && $this->handler->recordSql($event);
    }

}
