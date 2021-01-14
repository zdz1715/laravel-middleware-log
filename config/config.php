<?php

return [
    'exclude_route' => [
        // 'api/log/test',
        // 'web/*'
    ],
    'exclude_route_fields' => [
        // 'api/log/test' => [ 'response_body' ],
        // 'web/*' => [ 'response_body' ]
    ],
    // 忽略异常，比如：字段验证，想要如期返回响应内容
    'exclude_exception' => [
        Illuminate\Validation\ValidationException::class,
    ],
    // 用于抛出异常时，字段内容置为空
    'exclude_exception_fields' => [
        'response_body'
    ],
    /**
     * 记录的字段 key => [ 类（request|response）, 方法， 属性 ]
     * 有以下三种情况：
     * 1. 'full_url'  => [ 'request', 'fullUrl' ] = $request->fullUrl()
     * 2. 'response_header' => [ 'response', 'all', 'headers' ] = $response->headers->all()
     * 3. 'response_header' => [ 'response', '', 'headers' ] = $response->headers
     */
    'log_fields' => [
        'full_url'  => [ 'request', 'fullUrl' ],
        'path_info' => [ 'request', 'path' ],
        'client_ip' => [ 'request', 'ip' ],
        'request_method' => [ 'request', 'getMethod' ],
        'request_header' => [ 'request', 'header' ],
        'request_params' => [ 'request', 'all' ],
        'response_header' => [ 'response', 'all', 'headers' ],
        'response_body' => [ 'response', 'getContent'],
        'response_status_code' => [ 'response', 'getStatusCode']
    ],
    'log_message' => 'auto-log',
    'log_level' => 'debug',
    // 修改此handler自定义自己的方法
    'handler' => zdz\LaravelMiddlewareLog\handler\SingleHandler::class,
];

