<?php
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use zdz\LaravelMiddlewareLog\tool\FormatLog;
use Exception;

return [
    // 忽略的路由数组 示例： /api/test/testLog
    'exclude_route' => [],
    // 自定义使用方法
    'handle' => 'api',
    // api日志常规记录方法
    'api' => function(Request $request, $response) {
        /**
         * @var Response $response
         */
        $responseBody = '';
        $errMsg = '';
        // 有异常时就不记录返回的错误值了
        if ($response->exception instanceof Exception) {
            $errMsg = $response->exception->getMessage();
        } else {
            $responseBody = $response->getContent();
        }

        FormatLog::write('host', FormatLog::LOG_WRITE, $request->getHost());
        FormatLog::write('full_path', FormatLog::LOG_WRITE, $request->getUri());
        FormatLog::write('client_ip', FormatLog::LOG_WRITE, $request->ip());
        FormatLog::write('request_uri', FormatLog::LOG_WRITE, $request->getPathInfo());
        FormatLog::write('request_method', FormatLog::LOG_WRITE, $request->getRealMethod());
        FormatLog::write('request_header', FormatLog::LOG_WRITE, $request->header());
        FormatLog::write('request_params', FormatLog::LOG_WRITE, $request->all());
        FormatLog::write('response_header', FormatLog::LOG_WRITE, $response->headers->all());
        FormatLog::write('response_body', FormatLog::LOG_WRITE, $responseBody);

        // 执行时间
        FormatLog::write('exec_exception', FormatLog::LOG_WRITE, $errMsg);
        if (defined('LARAVEL_START')) {
            $execTime = round((microtime(true) - LARAVEL_START) * 1000, 2);
            FormatLog::write('exec_ms', FormatLog::LOG_WRITE, $execTime);
        }
        \Illuminate\Support\Facades\Log::debug('auto-log', FormatLog::flushData());
    },
    // sql日志记录方法
    'sql' => function($event) {
        /**
         * @var \Illuminate\Database\Events\QueryExecuted $event
         */
        FormatLog::write('db_sql', FormatLog::LOG_APPEND, [
            'connection_name'  => $event->connectionName,
            'sql' => $event->sql,
            'bindings' => $event->bindings,
            'ms' => $event->time
        ]);
    }
];

