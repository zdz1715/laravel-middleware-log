<?php
namespace zdz\LaravelMiddlewareLog\handler;


use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use zdz\LaravelMiddlewareLog\tool\FormatLog;

class SingleHandler extends AbstractHandler
{

    /**
     * @param $response
     */
    public function record($response): void
    {
        FormatLog::writeMany($this->parseLogFields($response));
    }






    /**
     * @param QueryExecuted $event
     * @return mixed|void
     */
   public function recordSql(QueryExecuted $event): void
   {
       FormatLog::write('db_sql', FormatLog::LOG_APPEND, [
           'connection_name'  => $event->connectionName,
           'sql' => $event->sql,
           'bindings' => $event->bindings,
           'ms' => $event->time
       ]);
   }


    /**
     * @param string $level
     * @param string $message
     */
   public function handle(string $level, string $message): void {
       \Illuminate\Support\Facades\Log::log($level, $message, FormatLog::flushData());
   }
}
