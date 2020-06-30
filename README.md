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
> 全局注册

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

> 中间件组 (推荐,只记录api日志)
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

> 单独路由
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

> 开启sql记录
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
## Configuration

使用以下命令发布配置，发布之后会生成`config/log-middleware.php`，在此文件里修改配置
```shell script
php artisan vendor:publish --provider="zdz\LaravelMiddlewareLog\LogServiceProvider"
```
`config/log-middleware.php`

|      字段     |  类型  |     描述      | 示例 |
| :----------- | :---- | :---------- | :----------  |
| exclude_route | array |  忽略的路由,在此数组中则不会记录日志 | ['api/test/log'] |
| exclude_exception | array | 异常忽略数组 |  默认忽略 [Illuminate\Validation\ValidationException::class,] |
| log_message | string | 日志消息 | auto-log |
| log_level | string | 日志级别: debug, info, notice, warning, error, critical, alert, emergency | debug |
| handle | string | 日志记录的方法名称，可以自定义别的名称，然后自定义自己的记录方法 | api |
| api | callable | 日志记录方法主体 | |
| sql | callable | sql记录方法主体| |

## Log
默认记录的日志结构

|      字段     |     描述      |
| :----------- | :---------- |
| exec_exception | 程序运行抛出的异常 |
| full_url | 完整路由 |
| path_info | 请求路由 |
| client_ip | 客户端ip |
| request_method | 请求方法 |
| request_header | 请求header |
| request_params | 请求参数 |
| response_header | 响应header |
| response_body | 响应结果 |
| db_sql | sql语句数组 |
|  &#124;- connection_name | 连接名称 |
|  &#124;- sql | sql语句 |
|  &#124;- bindings | 绑定参数 |
|  &#124;- ms | sql执行时间 |
| exec_ms | 执行时间，依赖于常量`LARAVEL_START`, 没有则可以在`public/index.php`添加 `define('LARAVEL_START', microtime(true));`|

## 其他操作

可以使用`zdz\LaravelMiddlewareLog\tool\FormatLog`的方法，在代码任何地方增加日志数据里的内容

##### write(string $point, string $op, $context = '', bool $jsonStrToArray = true): void

- $point：字段
- $op： 写入方式 FormatLog::LOG_WRITE 覆盖写入， FormatLog::LOG_APPEND 追加写入
- $context： 内容，字符串或者数组
- $jsonStrToArray：是否将json转换成数组，默认true，避免json嵌套的问题

##### writeMany(array $array): void

- $array 数组，会和日志数据做`array_merge`操作
 









