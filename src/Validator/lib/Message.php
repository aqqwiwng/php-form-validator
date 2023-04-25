<?php

namespace Validator\lib;


class Message
{
    private array $message = [
        'default' => '验证错误',
        'required' => '{label}必填',
        'enum' => '{label}超出范围',
        'regex' => '{label}格式不匹配',
        'whitespace' => '{label}不能为空',
        'type_error' => '{label}类型无效',
        'confirm' => '{label}与{confirm_label}不一致！请重新输入',
        'password' => '{label}不能为纯字母、纯数字或纯特殊字符(!@#$%^&_.*?),长度6至18位',
        'weak_password' => '{label}只能包含字母、数字或特殊字符(!@#$%^&_.*?),长度6至18位',
        'strong_password' => '{label}最少8位，包括至少1个大写字母，1个小写字母，1个数字，1个特殊字符(!@#$%^&_.*?)',
        'minimum' => '{label}不能小于{minimum}',
        'maximum' => '{label}不能大于{maximum}',
        'range_number' => '{label}必须介于{minimum}and{maximum}之间',
        'min_length' => '{label}长度不能少于{min_length}',
        'max_length' => '{label}长度不能超过{max_length}',
        'range_length' => '{label}必须介于{min_length}和{max_length}之间'
    ];
    protected array $rule = [];

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    /**
     * 获取消息
     * @param $type
     * @return string
     */
    public function getMessage($type): string
    {
        $message = $this->rule['message'] ?? $this->message[$type] ?? $this->message['default'];
        return $this->formatMessage($message);
    }

    /**
     * 设置规则
     * @param array $rule
     * @return Message
     */
    public function setRule(array $rule): Message
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * 格式化消息
     * @param $message
     * @return array|mixed|string|string[]
     */
    public function formatMessage($message): mixed
    {
        $str = $message;
        foreach ($this->rule as $k => $v) {
            $str = str_replace('{' . $k . '}', $v, $str);
        }
        return $str;
    }

}