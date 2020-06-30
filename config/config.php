<?php
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use zdz\LaravelMiddlewareLog\tool\FormatLog;

return [
    // 忽略的路由,在此数组中则不会记录日志，示例：['api/test/log']
    'exclude_route' => [],
    // 忽略的异常
    'exclude_exception' => [
        Illuminate\Validation\ValidationException::class,
    ],
    'log_message' => 'auto-log',
    // 日志级别: debug, info, notice, warning, error, critical, alert, emergency
    'log_level' => 'debug',
    // 自定义使用方法
    'handle' => 'api',
    // api日志常规记录方法
    'api' => function(Request $request, $response, $levelRecord, $exception, $message) {
        /**
         * @var Response $response
         */
        // 有异常时就不记录返回的错误值了
        FormatLog::writeMany([
            'exec_exception' => $exception,
            'full_url' => $request->fullUrl(),
            'path_info' => $request->path(),
            'client_ip' => $request->ip(),
            'request_method' => $request->getMethod(),
            'request_header' => $request->header(),
            'request_params' => $request->all(),
            'response_header' => $response->headers->all(),
            'response_body' => $exception ? '' : $response->getContent()
        ]);

        // 执行时间
        if (defined('LARAVEL_START')) {
            $execTime = round((microtime(true) - LARAVEL_START) * 1000, 2);
            FormatLog::write('exec_ms', FormatLog::LOG_WRITE, $execTime);
        }
        \Illuminate\Support\Facades\Log::{$levelRecord}($message, FormatLog::flushData());
    },
    // sql日志记录方法
    'sql' => function($event) {
        /**
         * @var Illuminate\Database\Events\QueryExecuted $event
         */
        FormatLog::write('db_sql', FormatLog::LOG_APPEND, [
            'connection_name'  => $event->connectionName,
            'sql' => $event->sql,
            'bindings' => $event->bindings,
            'ms' => $event->time
        ]);
    }
];

