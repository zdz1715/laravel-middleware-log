<?php
namespace zdz\LaravelMiddlewareLog\tool;

class FormatLog {

    public const LOG_WRITE = 'w';
    public const LOG_APPEND = 'a';

    private static $data = [];

    /**
     * 临时记录日志到静态变量中
     * @param string $point
     * @param string $op 写入方式 w 覆盖， a 追加
     * @param array $context
     */
    public static function write(string $point, string $op, array $context = []): void {
        if ($op === self::LOG_WRITE) {
            self::$data[$point] = $context;
        } else {
            self::$data[$point][] = $context;
        }
    }

    /**
     * 清空日志 并且返回日志数组
     * @return array
     */
    public static function flushData(): array {
        $tmp = self::$data;
        self::$data = [];
        return $tmp;
    }
}