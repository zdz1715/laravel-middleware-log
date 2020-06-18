<?php
namespace zdz\LaravelMiddlewareLog\tool;

class FormatLog {

    public const LOG_WRITE = 'w';
    public const LOG_APPEND = 'a';

    private static $data = [];


    /**
     * @param string $str
     * @return bool
     */
    public static function isJson(string $str): bool {
        return !is_null(json_decode($str));
    }

    /**
     * @param array $array
     */
    public static function writeMany(array $array): void {
        self::$data = array_merge(self::$data, $array);
    }

    /**
     * 临时记录日志到静态变量中
     * @param string $point
     * @param string $op 写入方式 w 覆盖， a 追加
     * @param string | array $context
     * @param boolean $jsonStrToArray
     */
    public static function write(string $point, string $op, $context = '', bool $jsonStrToArray = true): void {
        if (is_string($context) && $jsonStrToArray && self::isJson($context)) {
            $context = json_decode($context, true);
        }

        if ($op === self::LOG_WRITE) {
            self::$data[$point] = $context;
        } else {
            if (!isset(self::$data[$point])) {
                self::$data[$point] = [];
            }

            if (!is_array(self::$data[$point])) {
                self::$data[$point] = [
                    self::$data[$point]
                ];
            }
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
