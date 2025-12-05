<?php

namespace Validator;

use Validator\Utils;

class BaseValidator
{
    /**
     * 语言管理器
     */
    protected LangManager $langManager;

    /**
     * 验证字段值是否允许为空
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function required(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        if (is_string($rule['required']) && method_exists($this, $rule['required'])) {
            // 当required为自定义函数名时，调用该函数，若返回true且值为空，则判定为错误
            $res = $this->{$rule['required']}($value, $data);
            $error = $res && Utils::isEmpty($value);
        } else {
            // 当required为布尔值时，若值为空则判定为错误
            $error = $rule['required'] && Utils::isEmpty($value);
        }
        if ($error === true) {
            return $message->getMessage('required');
        }
        return null;
    }

    /**
     * 字段格式是否正确
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function type(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        $format = $rule['type'];
        if (str_contains($format, 'pwd')) {
            $msgType = $format;
        }else{
            $msgType = 'type';
        }
        if (!Utils::checkType($format, $value)) {
            return $message->getMessage($msgType);
        }
        return null;
    }

    /**
     * 正则表达式验证字段值是否匹配指定模式
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function regex(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        try {
            if (preg_match('/^\/.+\/[igmsxaeuADXUS]*$/', $rule['regex'])) {
                $regex = $rule['regex'];
            } else {
                $regex = '/' . $rule['regex'] . '/';
            }
            $result = preg_match($regex, $value);
        } catch (\Exception $e) {
            $result = false;
        }
        if (!$result) {
            return $message->getMessage('regex');
        }
        return null;
    }

    /**
     * 验证字段值是否与在指定范围内
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function enum(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        $range = $rule['enum'] ?? [];
        $list = [];
        foreach ($range as $item) {
            if (Utils::checkType('object', $item) && isset($item['value'])) {
                $list[] = $item['value'];
            } else {
                $list[] = $item;
            }
        }
        if (Utils::checkType('array', $value)) {
            $result = count(array_diff($value, $list)) === 0 || count($value) <= count($list);
        } else {
            $result = in_array($value, $list, true);
        }
        if (!$result) {
            return $message->getMessage('enum');
        }
        return null;
    }

    /**
     * 验证字段值是否在指定值之间
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function between(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        $min = $rule['min'] ?? null;
        $max = $rule['max'] ?? null;

        // 只要有一个边界条件不满足即判定失败
        if (
            ($min !== null && $value < $min) ||
            ($max !== null && $value > $max)
        ) {
            return $message->getMessage('between');
        }

        return null;
    }

    /**
     * 验证字段值是否大于等于指定值
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function min(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        $min = $rule['min'] ?? null;
        if ($min !== null && $value < $min) {
            return $message->getMessage('min');
        }
        return null;
    }

    /**
     * 验证字段值是否小于等于指定值
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function max(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        $max = $rule['max'] ?? null;
        if ($max !== null && $value > $max) {
            return $message->getMessage('max');
        }
        return null;
    }

    /**
     * 验证字段的值是否与指定字段的值相同
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function confirm(array $rule, mixed $value, mixed $data): ?string
    {
        $message = new Message($rule, $this->langManager);
        if (!Utils::checkType('string', $value)) {
            return $message->getMessage('type_error');
        }
        $confirm_field = $rule['confirm_field'];
        $rule['confirm_label'] ??= "[$confirm_field]";
        $message->setRule($rule);
        if (!isset($data[$confirm_field])) return $message->getMessage('confirm_not_found');
        if ($data[$confirm_field] !== $value) return $message->getMessage('confirm');
        return null;
    }

    /**
     * 使用自定义函数验证字段值是否正确
     * @param array $rule 验证规则
     * @param mixed $value 字段值
     * @param mixed $data 表单数据
     * @return string|null 错误信息
     */
    protected function validateFunction(array $rule, mixed $value, mixed $data): ?string
    {
        $fn = $rule['validateFunction'] ?? null;
        if (!$fn) {
            return null;
        }

        // 统一获取验证结果
        if (is_callable($fn)) {
            $result = $fn($rule, $value, $data);
        } elseif (is_string($fn) && method_exists($this, $fn)) {
            $result = $this->{$fn}($rule, $value, $data);
        } else {
            // 既不是可调用也不是有效方法名，直接返回 null
            return null;
        }

        // 统一处理结果
        if (is_string($result)) {
            return $result;
        }
        if ($result === false) {
            $message = new Message($rule, $this->langManager);
            return $message->getMessage('default');
        }

        return null;
    }
}