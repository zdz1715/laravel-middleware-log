<?php

return [
    // 如：不记录api/log/test路由的日志
    'exclude_route' => [
        // 'api/log/test'
    ],
    // 如：记录api/log/test路由的日志，但不记录log_fields里的response_body数据
    'exclude_route_fields' => [
        // 'api/log/test' => [ 'response_body' ]
    ],
    // 忽略异常，比如：字段验证，想要如期返回响应内容
    'exclude_exception' => [
        Illuminate\Validation\ValidationException::class,
    ],
    // 用于异常情况下，记录响应内容为空
    'response_body_key' => 'response_body',
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
    ],
    'log_message' => 'auto-log',
    'log_level' => 'debug',
    // 修改此handler自定义自己的方法
    'handler' => zdz\LaravelMiddlewareLog\handle\SingleHandler::class,
];

