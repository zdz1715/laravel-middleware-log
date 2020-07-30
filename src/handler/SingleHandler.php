<?php
namespace zdz\LaravelMiddlewareLog\handle;


use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use zdz\LaravelMiddlewareLog\tool\FormatLog;

class SingleHandler extends AbstractHandler
{

    /**
     * @param $response
     */
    public function record($response): void
    {
        $exception = $this->getException($response->exception);
        $logData = array_merge([
            'exec_exception' => $exception,
        ], $this->parseLogFields($response, $exception));


        FormatLog::writeMany($logData);
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
       // 执行时间
       if (defined('LARAVEL_START')) {
           $execTime = round((microtime(true) - LARAVEL_START) * 1000, 2);
           FormatLog::write('exec_ms', FormatLog::LOG_WRITE, $execTime);
       }
       \Illuminate\Support\Facades\Log::log($level, $message, FormatLog::flushData());
   }
}