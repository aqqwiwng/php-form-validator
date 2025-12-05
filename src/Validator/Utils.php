<?php

namespace Validator;

class Utils
{
    const pattern = [
        'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'mobile' => '/^1[3-9]\d{9}$/',
        'weak_pwd' => '/^[\w!@#$%^&*?]{6,18}$/',
        'pwd' => '/^(?!^\d+$)(?!^[a-zA-Z]+$)(?!^[!@#$%^&_.*?]+$).{6,18}$/',
        'strong_pwd' => '/^.*(?=.{8,})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&_.*?]).*$/',
        'date' => '/^[0-9]{4}(\-|\/)[0-9]{1,2}(\1)[0-9]{1,2}(|\s+[0-9]{1,2}(|:[0-9]{1,2}(|:[0-9]{1,2})))$/',
        'url' => '/^((http|ftp|ws)(s)?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/',
        'id_card' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
    ];

    /**
     * 判断值是否为空
     * 允许 0、0.0、'0'、false 等合法非空值
     * @param mixed $value 值
     * @return bool
     */
    public static function isEmpty(mixed $value): bool
    {
        // 数值与布尔值永远不算空
        if (is_numeric($value) || is_bool($value)) {
            return false;
        }
        // 其余情况交给 empty 判断
        return empty($value);
    }

    /**
     * 检查值是否符合指定类型
     * @param string $type 类型
     * @param mixed $value 值
     * @return bool
     */
    public static function checkType(string $type, mixed $value): bool
    {
        return match ($type) {
            'int' => is_int($value),
            'string' => is_string($value),
            'number' => is_int($value) || is_float($value),
            'bool' => is_bool($value),
            'float' => is_float($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'date' => is_string($value) && preg_match(self::pattern['date'], $value),
            'timestamp' => is_numeric($value) && (strlen((string)$value) === 10 || strlen((string)$value) === 13),
            'mobile' => is_string($value) && preg_match(self::pattern['mobile'], $value),
            'email' => is_string($value) && preg_match(self::pattern['email'], $value),
            'url' => is_string($value) && preg_match(self::pattern['url'], $value),
            'id_card' => is_string($value) && preg_match(self::pattern['id_card'], $value),
            'pwd' => is_string($value) && preg_match(self::pattern['pwd'], $value),
            'weak_pwd' => is_string($value) && preg_match(self::pattern['weak_pwd'], $value),
            'strong_pwd' => is_string($value) && preg_match(self::pattern['strong_pwd'], $value),
            'chinese' => is_string($value) && preg_match(self::pattern['chinese'], $value),
            default => false,
        };
    }

    /**
     * 蛇形命名转换为驼峰命名
     * @param string $value
     * @param bool $firstUpper
     * @return string
     */
    public static function snakeToCamel(string $value, bool $firstUpper = false): string
    {
        $value = ucwords(str_replace(['_', '-'], ' ', $value));
        $value = str_replace(' ', '', $value);
        return $firstUpper ? $value : lcfirst($value);
    }

    /**
     * 驼峰命名转换为蛇形命名
     * @param string $value
     * @param string $connector
     * @return string
     */

    public static function camelToSnake(string $value,string $connector='_'): string
    {
        $value = preg_replace('/\s+/u', '', $value);
        return strtolower(preg_replace('/(.)(?=[A-Z])/u', "$1".$connector, $value));
    }

}