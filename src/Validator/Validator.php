<?php

namespace Validator;

use Validator\BaseValidator;
use Validator\LangManager;
use Validator\ValidateException;
use Validator\Utils;

class Validator extends BaseValidator
{

    /**
     * 语言
     */
    protected string $lang = "zh_CN";
    /**
     * 验证规则
     */
    protected array $rules = [];
    /**
     * 是否批量验证
     */
    protected bool $isBatchValidation = false;
    /**
     * 失败是否抛出异常
     */
    protected bool $failThrowException = true;

    /**
     * 验证场景
     */
    protected array $scenes = [];
    /**
     * 当前验证场景
     */
    private string $currentScene = '';
    /**
     * 当前验证规则
     */
    private ?array $currentRules = null;
    /**
     * 验证错误信息
     */
    private string|array $error = [];

    public function __construct()
    {
        $this->langManager = new LangManager($this->lang);
    }

    /**
     * 验证
     * @param $data
     * @return bool
     */
    public function check($data): bool
    {
        $this->error = [];
        $rules = $this->getCurrentRules();

        foreach ($rules as $key => $rule) {
            $value = $data[$key] ?? null;
            $error = $this->validateRule($key, $rule, $value, $data);

            if ($error === null) {
                continue;
            }

            // 批量验证：收集所有错误
            if ($this->isBatchValidation) {
                $this->error[$key] = $error;
                continue;
            }

            // 非批量：立即处理
            if ($this->failThrowException) {
                throw new ValidateException($error);
            }

            $this->error = $error;
            return false;
        }

        // 批量验证结束后统一处理
        if (!empty($this->error)) {
            if ($this->failThrowException) {
                throw new ValidateException($this->error);
            }
            return false;
        }

        return true;
    }

    /**
     * 获取错误信息
     * @return array|string
     */
    public function getError(): array|string
    {
        return $this->error;
    }

    /**
     * 设置语言
     * @param string $lang 语言
     * @return Validator
     */
    public function setLang(string $lang): Validator
    {
        $this->lang = $lang;
        $this->langManager = new LangManager($this->lang);
        return $this;
    }

    /**
     * 设置语言管理器
     * @param LangManager $langManager 语言管理器
     * @return Validator
     */
    public function setLangManager(LangManager $langManager): Validator
    {
        $this->langManager = $langManager;
        return $this;
    }

    /**
     * 设置验证规则
     * @param array $rules 验证规则
     * @return Validator
     */
    public function setRules(array $rules): Validator
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * 设置验证场景
     * @param array $scenes 验证场景
     * @return Validator
     */
    public function setScenes(array $scenes): Validator
    {
        $this->scenes = $scenes;
        return $this;
    }

    /**
     * 选择验证场景
     * @param string $scene 验证场景
     * @return Validator
     */
    public function scene(string $scene): Validator
    {
        $this->currentScene = $scene;
        $this->currentRules = null; // 重置当前规则，让getCurrentRules方法重新处理
        return $this;
    }

    /**
     * 设置批量验证
     * @param bool $batch 是否批量验证
     * @return Validator
     */
    public function batch(bool $batch = true): Validator
    {
        $this->isBatchValidation = $batch;

        return $this;
    }

    /**
     * 设置验证失败后是否抛出异常
     * @param bool $fail 是否抛出异常
     * @return Validator
     */
    public function failException(bool $fail = true): Validator
    {
        $this->failThrowException = $fail;

        return $this;
    }

    /**
     * 选择验证字段
     * @param array $fields
     * @return Validator
     */
    protected function only(array $fields): Validator
    {
        $this->currentRules = array_intersect_key($this->rules, array_flip($fields));
        return $this;
    }

    /**
     * 删除字段验证规则
     * @param string $field 字段名称
     * @param string $ruleName 规则名称
     */
    protected function remove(string $field, string $ruleName): Validator
    {
        // 字段不存在或无规则时直接返回
        if (!isset($this->currentRules[$field]['rules'])) {
            return $this;
        }

        // 使用 array_filter 一次性剔除匹配规则，避免遍历中 unset 与即时 reindex
        $this->currentRules[$field]['rules'] = array_values(
            array_filter(
                $this->currentRules[$field]['rules'],
                fn($rule) => !array_key_exists($ruleName, $rule)
            )
        );
        return $this;
    }

    /**
     * 添加字段验证规则
     * @param string $field 字段名称
     * @param array $rule 验证规则
     */
    protected function append(string $field, array $rule): Validator
    {
        // 确保字段已存在，否则初始化
        if (!isset($this->currentRules[$field])) {
            $this->currentRules[$field] = ['rules' => []];
        }
        // 追加规则
        $this->currentRules[$field]['rules'][] = $rule;
        return $this;
    }

    /**
     * 获取验证规则
     * @return array|null
     */
    public function getCurrentRules(): ?array
    {
        // 无场景时直接返回全部规则
        if (!$this->currentScene) {
            return $this->rules;
        }

        // 若已手动设置过 only / append / remove，则优先使用当前缓存规则
        if ($this->currentRules !== null) {
            return $this->currentRules;
        }

        // 根据场景名动态调用 scene_XXX 方法或读取 scenes 配置
        $scene = $this->currentScene;
        if (method_exists($this, 'scene_' . $scene)) {
            $this->{'scene_' . $scene}();
        } elseif (isset($this->scenes[$scene])) {
            $this->only($this->scenes[$scene]);
        }

        // 最终兜底：若场景处理未产生规则，则返回全部规则
        return $this->currentRules ?? $this->rules;
    }

    /**
     * 对单个字段按规则逐项验证
     * @param string $fieldKey 字段名
     * @param array $fieldRule 字段规则配置
     * @param mixed $value 待验证值
     * @param array $data 全部数据
     * @return string|null 错误消息，无错返回 null
     */
    private function validateRule(string $fieldKey, array $fieldRule, mixed $value, array $data): ?string
    {
        // 无规则直接跳过
        if (empty($fieldRule['rules'])) {
            return null;
        }

        $label = $fieldRule['label'] ?? "[$fieldKey]";

        // 非必填且空值，跳过后续验证
        if (!$this->isRequired($fieldRule['rules'], $value, $data) && Utils::isEmpty($value)) {
            return null;
        }

        foreach ($fieldRule['rules'] as $ruleItem) {
            $validateType = $this->getValidateType($ruleItem);
            if ($validateType === null) {
                continue;
            }

            $fullRule = array_merge($ruleItem, ['label' => $label]);
            if (method_exists($this, $validateType)) {
                $error = $this->$validateType($fullRule, $value, $data);
                if ($error !== null) {
                    return $error;
                }
            }
        }

        return null;
    }

    /**
     * 判断规则中是否存在 required 要求
     * @param array $rules 规则数组
     * @param mixed $value 字段值
     * @param array $data 全部数据
     * @return bool
     */
    private function isRequired(array $rules, mixed $value, array $data): bool
    {
        foreach ($rules as $rule) {
            if (!array_key_exists('required', $rule)) {
                continue;
            }

            $required = $rule['required'];
            if (is_string($required) && method_exists($this, $required)) {
                return (bool)$this->$required($value, $data);
            }

            return (bool)$required;
        }

        return false;
    }

    /**
     * 根据规则数组返回对应的验证方法名
     * @param array $rule
     * @return string|null
     */
    private function getValidateType(array $rule): ?string
    {
        // 保持优先级顺序，先匹配先返回
        return match (true) {
            isset($rule['required']) => 'required',
            isset($rule['type']) => 'type',
            isset($rule['confirm']) => 'confirm',
            isset($rule['enum']) => 'enum',
            isset($rule['regex']) => 'regex',
            isset($rule['validateFunction']) => 'validateFunction',
            (isset($rule['min']) && isset($rule['max'])) => 'between',
            isset($rule['min']) => 'min',
            isset($rule['max']) => 'max',
            default => null,
        };
    }
}