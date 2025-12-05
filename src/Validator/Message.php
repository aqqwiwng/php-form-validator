<?php

namespace Validator;

class Message
{
    protected array $rule = [];
    private langManager $langManager;

    public function __construct($rule, langManager $langManager)
    {
        $this->rule = $rule;
        $this->langManager = $langManager;
    }

    /**
     * 获取消息
     * @param string $type 消息类型
     * @return string
     */
    public function getMessage(string $type): string
    {
        // 优先使用规则中定义的自定义消息
        if (isset($this->rule['message'])) {
            return $this->formatMessage($this->rule['message']);
        }

        // 从语言管理器获取翻译消息
        $message = $this->langManager->getMessage($type) ?? $this->langManager->getMessage('default');
        return $this->formatMessage($message);
    }

    /**
     * 设置规则
     * @param array $rule 验证规则
     * @return Message
     */
    public function setRule(array $rule): Message
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * 格式化消息
     * @param string $message 消息模板
     * @return string|array|string[]
     */
    public function formatMessage(string $message): string|array
    {
        $str = $message;
        foreach ($this->rule as $k => $v) {
            $str = str_replace('{' . $k . '}', $v, $str);
        }
        return $str;
    }
}