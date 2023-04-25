<?php

namespace Validator\lib;

class Utils
{
    const pattern = [
        'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'email' => '/^\S+?@\S+?\.\S+?$/',
        'mobile' => '/^1[3-9]\d{9}$/',
        'weak_password' => '/^[\w!@#$%^&*?]{6,18}$/',
        'password' => '/^(?!^\d+$)(?!^[a-zA-Z]+$)(?!^[!@#$%^&_.*?]+$).{6,18}$/',
        'strong_password' => '/^.*(?=.{8,})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&_.*?]).*$/',
        'date' => '/^[0-9]{4}(\-|\/)[0-9]{1,2}(\1)[0-9]{1,2}(|\s+[0-9]{1,2}(|:[0-9]{1,2}(|:[0-9]{1,2})))$/',
        'url' => '/^((http|ftp|ws)(s)?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/',
        'id_card' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
    ];

    public static function isEmpty($value): bool
    {
        return (empty($value) && !is_numeric($value) && !is_bool($value));
    }

    public static function checkFormat($type, $value): bool
    {
        return match ($type) {
            'int' => is_int($value),
            'string' => is_string($value),
            'number' => is_int($value) || is_double($value) || is_float($value),
            'bool' => is_bool($value),
            'float' => is_float($value),
            'array' => is_array(json_decode(json_encode($value))),
            'object' => is_object(json_decode(json_encode($value))),
            'date' => (bool)preg_match(self::pattern['date'], $value),
            'timestamp' => is_numeric($value) && (strlen($value) === 10 || strlen($value) === 13),
            'mobile' => (bool)preg_match(self::pattern['mobile'], $value),
            'email' => (bool)preg_match(self::pattern['email'], $value),
            'url' => (bool)preg_match(self::pattern['url'], $value),
            'id_card' => (bool)preg_match(self::pattern['id_card'], $value),
            'password' => (bool)preg_match(self::pattern['password'], $value),
            'weak_password' => (bool)preg_match(self::pattern['weak_password'], $value),
            'strong_password' => (bool)preg_match(self::pattern['strong_password'], $value),
            'chinese' => (bool)preg_match(self::pattern['chinese'], $value),
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