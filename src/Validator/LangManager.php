<?php

namespace Validator;

use InvalidArgumentException;

class LangManager
{
    private string $lang;
    private array $messages;
    private const LANG_DIR = __DIR__ . '/lang/';

    public function __construct(string $lang = 'zh_CN')
    {
        $this->lang = $lang;
        $this->messages = $this->loadLanguageFile($this->lang);
    }

    private function loadLanguageFile(string $langCode): array
    {
        $filePath = self::LANG_DIR . $langCode . '.php';
        if (file_exists($filePath)) {
            return require $filePath;
        }
        // 如果指定语言文件不存在，返回默认语言（zh_CN）
        $defaultLangPath = self::LANG_DIR . 'zh_CN.php';
        if (file_exists($defaultLangPath)) {
            return require $defaultLangPath;
        }
        // 如果默认语言文件也不存在，返回空数组
        return [];
    }

    /**
     * 设置语言
     *
     * @param string $langCode 语言代码，格式为 en_US
     * @throws InvalidArgumentException 如果语言代码格式无效
     */
    public function setLanguage(string $langCode): void
    {
        if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $langCode)) {
            throw new InvalidArgumentException("Invalid language code format: $langCode. Expected format: en_US");
        }

        $this->lang = $langCode;
        $this->messages = $this->loadLanguageFile($langCode);
    }

    /**
     * 获取当前语言
     *
     * @return string 当前语言
     */
    public function getLanguage(): string
    {
        return $this->lang;
    }

    /**
     * 获取所有消息
     *
     * @return array 所有消息键值对
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * 获取指定键的消息
     *
     * @param string $key 消息键
     * @param array $params 替换参数
     * @return string 格式化后的消息
     */
    public function getMessage(string $key, array $params = []): string
    {
        $message = $this->messages[$key] ?? $key;
        foreach ($params as $placeholder => $value) {
            $message = str_replace('{' . $placeholder . '}', $value, $message);
        }
        return $message;
    }

}