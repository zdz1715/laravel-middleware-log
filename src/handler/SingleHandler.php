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
     * @param $exception
     * @param array $fields
     */
    public function record($response, $exception, array $fields): void
    {
        $logData = array_merge([
            'exec_exception' => $exception,
        ], $this->parseLogFields($fields, $response));

        if ($exception) {
            foreach ($this->getConfig('exclude_exception_fields', []) as $field) {
                $logData[$field] = '';
            }
        }

        FormatLog::writeMany($logData);
    }




    /**
     * @param $fields
     * @param $response
     * @return array
     */
    private function parseLogFields($fields, $response) {
        $data = [];
        foreach ($fields as $key => $val) {
            $className = array_shift($val);
            $method = array_shift($val);
            $attr = array_shift($val);

            $class = $className == 'response' ? $response : $this->request;

            if (!is_null($attr) && property_exists($class, $attr)) {
                $logValue = $class->$attr;
                if (!is_null($method) && method_exists($logValue, $method)) {
                    $logValue = $logValue->$method();
                }
            } else if (!is_null($method) && method_exists($class, $method)) {
                $logValue = $class->$method();
            }

            $data[$key] = $logValue ?? null;
        }

        return $data;
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
