# laravel-middleware-log

laravel日志中间件

## Requirements
- php: ^7.2
- laravel: ^6.0

## Installation
```shell script
composer require zdz/laravel-middleware-log
```

## Usage
### 注册中间件
####  1. 全局注册

在 `app/Http/Kernel.php` 中的 `$middleware` 属性中列出这个中间件

```php
// 在 App\Http\Kernel 类中...

protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \App\Http\Middleware\CheckForMaintenanceMode::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    // 日志中间件
    \zdz\LaravelMiddlewareLog\WriteLogMiddleware::class,
];
```

#### 2. 中间件组 (推荐,只记录api日志)
```php
// 在 App\Http\Kernel 类中...

protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // 日志中间件
            \zdz\LaravelMiddlewareLog\WriteLogMiddleware::class,
        ],
    ];
```

#### 3. 单独路由
```php
// 在 App\Http\Kernel 类中...
protected $routeMiddleware = [
    'auto_log' => \zdz\LaravelMiddlewareLog\WriteLogMiddleware::class,
];
```

```php
Route::get('admin/profile', function () {
    //
})->middleware('auto_log');
```

### 监听sql语句
```php
// 在 App\Providers\EventServiceProvider 类中...
protected $listen = [
    Registered::class => [
        SendEmailVerificationNotification::class,
    ],
   // 添加sql监听 
    \Illuminate\Database\Events\QueryExecuted::class => [
        \zdz\LaravelMiddlewareLog\SqlLogListen::class
    ]
];
```
<style>
table th:first-of-type {
    width: 4cm;
}
table th:nth-of-type(2) {
    width: 150pt;
}
table th:nth-of-type(3) {
    width: 8em;
}
</style>

## Log结构
> 默认请求一次，记录一条日志，日志为json字符串，结构如下：

|      字段     |     描述      |
| :---------- | :---------- |
| message | 默认为auto-log，修改config里的log_message自定义 |
| level | laravel日志级别 |
| level_name | laravel日志级别名称 |
| channel | |
| datetime | 记录时间 |
| extra | |
| context | 日志内容，修改config里的log_fields自定义默认记录字段 |
| &#124;- exec_exception | 程序运行抛出的异常（内置，不可修改） |
| &#124;- exec_ms | 执行时间，依赖于常量`LARAVEL_START`, 没有则可以在`public/index.php`添加 `define('LARAVEL_START', microtime(true));`（内置，不可修改）|
| &#124;- full_url | 完整路由 |
| &#124;- path_info | 请求路由 |
| &#124;- client_ip | 客户端ip |
| &#124;- request_method | 请求方法 |
| &#124;- request_header | 请求header |
| &#124;- request_params | 请求参数 |
| &#124;- response_header | 响应header |
| &#124;- response_body | 响应结果 |
| &#124;- db_sql | sql语句数组 |
| &#124;-- connection_name  | 连接名称 |
| &#124;-- sql  | sql语句 |
| &#124;-- bindings | 绑定参数 |
| &#124;-- ms | sql执行时间 |

## Configuration（可选，自定义配置）

使用以下命令发布配置，发布之后会生成`config/log-middleware.php`，在此文件里修改配置
```shell script
php artisan vendor:publish --provider="zdz\LaravelMiddlewareLog\LogServiceProvider"
```
`config/log-middleware.php`

|      字段     |  类型  |     描述      | 示例 |
| :----------- | :---- | :---------- | :----------  |
| exclude_route | array |  忽略的路由,在此数组中则不会记录日志 | ['api/test/log'] |
| exclude_route_fields | array | 忽略的路由字段，记录路由日志，但不记录字段里的值 | ['api/test/log' => [ 'response_body' ] ] |
| exclude_exception | array | 忽略的异常数组 |  默认忽略 [Illuminate\Validation\ValidationException::class,] |
| response_body_key | string | 用于异常情况下，记录响应内容为空 | 如：response_body，当有异常，则不记录response_body的数据 |
| log_fields | array | 记录的数据字段 |  key => [ 类（request、response）, 方法， 属性 ] <br>有以下三种情况：<br> 1. 'full_url'  => [ 'request', 'fullUrl' ] = $request->fullUrl() <br>2. 'response_header' => [ 'response', 'all', 'headers' ] = $response->headers->all() <br>3. 'response_header' => [ 'response', '', 'headers' ] = $response->headers | 
| log_message | string | 日志消息 | auto-log |
| log_level | string | 日志级别: debug, info, notice, warning, error, critical, alert, emergency | debug |
| handler | string | 修改此handler自定义自己的方法 | zdz\LaravelMiddlewareLog\handle\SingleHandler::class |

## 其他操作

使用`zdz\LaravelMiddlewareLog\tool\FormatLog`增加日志的内容
### 提供的方法
##### write(string $point, string $op, $context = '', bool $jsonStrToArray = true): void

- $point：字段
- $op： 写入方式 FormatLog::LOG_WRITE 覆盖写入， FormatLog::LOG_APPEND 追加写入
- $context： 内容，字符串或者数组
- $jsonStrToArray：是否将json转换成数组，默认true，避免json嵌套的问题

##### writeMany(array $array): void

- $array 数组，会和日志数据做`array_merge`操作
 









