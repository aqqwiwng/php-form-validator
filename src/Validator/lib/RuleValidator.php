<?php

namespace Validator\lib;

class RuleValidator
{
    /**
     * 验证字段值是否允许为空
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function required($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        if (is_string($rule['required']) && method_exists($this, $rule['required'])) {
            $res = $this->{$rule['required']}($value, $data);
            $error = $res && Utils::isEmpty($value);
        } else {
            $error = Utils::isEmpty($value);
        }
        if ($error === true) {
            return $message->getMessage('required');
        }
        return null;
    }

    /**
     * 字段格式是否正确
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function format($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        $format = $rule['format'];
        if (str_contains($format, 'password')) {
            $msgType = $format;
        }else{
            $msgType = 'type_error';
        }
        if (!Utils::checkFormat($format, $value)) {
            return $message->getMessage($msgType);
        }
        return null;
    }

    /**
     * 正则表达式验证字段值
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function regex($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        if (preg_match('/^\/.+\/[igmsxaeuADXUS]*$/', $rule['regex'])) {
            $regex = $rule['regex'];
        } else {
            $regex = '/' . $rule['regex'] . '/';
        }

        try {
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
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function range($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        $range = $rule['range'] ?? [];
        $list = [];
        foreach ($range as $item) {
            if (Utils::checkFormat('object', $item) && isset($item['value'])) {
                $list[] = $item['value'];
            } else {
                $list[] = $item;
            }
        }
        if (Utils::checkFormat('array', $value)) {
            $result = empty(array_diff($value, $list));
        } else {
            $result = in_array($value, $list);
        }
        if (!$result) {
            return $message->getMessage('enum');
        }
        return null;
    }

    /**
     * 验证字段值是否在指定长度范围内
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function range_length($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        if (!Utils::checkFormat('string', $value)) {
            return $message->getMessage('regex');
        }
        $min = isset($rule['min_length']) && strlen($value) <= $rule['min_length'];
        $max = isset($rule['max_length']) && strlen($value) >= $rule['max_length'];
        if (isset($rule['min_length']) && isset($rule['max_length']) && ($min || $max)) {
            return $message->getMessage('range_length');
        } elseif (isset($rule['min_length']) && $min) {
            return $message->getMessage('min_length');
        } elseif (isset($rule['max_length']) && $max) {
            return $message->getMessage('max_length');
        }
        return null;
    }

    /**
     * 验证字段值是否在指定数值范围内
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function range_number($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        if (!Utils::checkFormat('number', $value)) {
            return $message->getMessage('regex');
        }
        $min = isset($rule['minimum']) && $value <= $rule['minimum'];
        $max = isset($rule['maximum']) && $value >= $rule['maximum'];
        if (isset($rule['minimum']) && isset($rule['maximum']) && ($min || $max)) {
            return $message->getMessage('range_number');
        } elseif (isset($rule['minimum']) && $min) {
            return $message->getMessage('minimum');
        } elseif (isset($rule['maximum']) && $max) {
            return $message->getMessage('maximum');
        }
        return null;
    }

    /**
     * 验证字段值是否与指定字段值相同
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function confirm($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        if (!Utils::checkFormat('string', $value)) {
            return $message->getMessage('regex');
        }
        $confirm = $rule ['confirm'];
        $rule['confirm_label'] = $rule['confirm_label'] ?? "[$confirm]";
        if (!isset($data[$confirm])) return "[$confirm]字段不存在！";
        if ($data[$confirm] !== $value) return $message->getMessage('confirm');
        return null;
    }

    /**
     * 使用自定义函数验证字段值是否正确
     * @param $rule
     * @param $value
     * @param $data
     * @return string|null
     */
    public function validateFunction($rule, $value, $data): ?string
    {
        $message = new Message($rule);
        if (is_string($rule['validateFunction']) && method_exists($this, $rule['validateFunction'])) {
            $result = $this->{$rule['validateFunction']}($rule, $value, $data);
            if (is_string($result)) return $result;
            if ($result === false) return $message->getMessage('default');
        }
        return null;
    }
}