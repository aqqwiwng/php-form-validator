<?php

namespace Validator;

use Validator\lib\ValidateException;
use Validator\lib\RuleValidator;
use Validator\lib\Utils;

class Validator extends RuleValidator
{
    protected array $rules = [];
    protected bool $batch = false;
    protected bool $failException = true;
    protected array $scene = [];
    protected string $currentScene = '';
    protected ?array $currentRules = null;
    protected string|array $error = [];


    /**
     * 设置验证规则
     * @param array $rules
     * @return Validator
     */
    public function setRules(array $rules): Validator
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * 选择验证场景
     * @param string $name
     * @return Validator
     */
    public function setScene(string $name): Validator
    {
        $this->currentScene = $name;
        return $this;
    }

    /**
     * 选择验证字段
     * @param array $fields
     * @return Validator
     */
    protected function only(array $fields): Validator
    {
        $this->currentRules = [];
        foreach ($fields as $field) {
            if (isset($this->rules[$field])) {
                $this->currentRules[$field] = $this->rules[$field];
            }
        }
        return $this;
    }

    /**
     * 删除字段验证规则
     * @param string $field 字段名称
     * @param string $ruleName 规则名称
     */
    protected function remove(string $field, string $ruleName): Validator
    {
        if (isset($this->currentRules[$field])) {
            $rules = $this->currentRules[$field]['rules'] ?? [];
        }
        if (!empty($rules)) {
            foreach ($rules as $key => $item) {
                if (key_exists($ruleName, $item)) {
                    unset($this->currentRules[$field]['rules'][$key]);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * 添加字段验证规则
     * @param string $field 字段名称
     * @param array $rule 验证规则
     */
    protected function append(string $field, array $rule): Validator
    {
        if (isset($this->currentRules[$field])) {
            $rules = $this->currentRules[$field]['rules'] ?? [];
        }
        if (!empty($rules)) {
            $this->currentRules[$field]['rules'][] = $rule;
        } else {
            $this->currentRules[$field]['rules'] = [$rule];
        }
        return $this;
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
            $result = $this->validateRule($key, $rule, $value, $data);
            if ($result !== null) {
                if ($this->batch) {
                    // 批量验证
                    $this->error[$key] = $result;
                } elseif ($this->failException) {
                    throw new ValidateException($result);
                } else {
                    $this->error = $result;
                    return false;
                }
            }
        }
        if (!empty($this->error)) {
            if ($this->failException) {
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
     * 设置批量验证
     * @access public
     * @param bool $batch 是否批量验证
     * @return $this
     */
    public function batch(bool $batch = true): Validator
    {
        $this->batch = $batch;

        return $this;
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    public function failException(bool $fail = true): Validator
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 判断是否存在某个验证场景
     * @access public
     * @param string $name 场景名
     * @return bool
     */
    private function hasScene(string $name): bool
    {
        return isset($this->scene[$name]) || method_exists($this, 'scene' . $name);
    }

    /**
     * 获取验证规则
     * @return array|null
     */
    private function getCurrentRules(): ?array
    {
        if ($this->hasScene($this->currentScene)) {
            $scene = $this->currentScene;
            if (method_exists($this, 'scene' . $scene)) {
                call_user_func([$this, 'scene' . $scene]);
            } elseif (isset($this->scene[$scene])) {
                $this->only($this->scene[$scene]);
            }
            return $this->currentRules;
        }
        return $this->rules;
    }

    private function validateRule($fieldKey, $fieldRule, $value, $data): ?string
    {
        $result = null;
        if (!isset($fieldRule['rules'])) return null;
        $rules = $fieldRule['rules'];
        $label = $fieldRule['label'] ?? "[$fieldKey]";
        if (!$this->hasRequired($rules,$value, $data) && Utils::isEmpty($value)) {
            return null;
        }
        foreach ($rules as $item) {
            $vt = $this->getValidateType($item);
            $rule = array_merge($item, ['label' => $label]);
            if (method_exists($this, $vt)) {
                $result = $this->$vt($rule, $value, $data);
                if ($result !== null) break;
            }
        }
        return $result;
    }

    private function hasRequired($rules, $value, $data): bool
    {
        $rule = [];
        foreach ($rules as $item) {
            if (isset($item['required'])) {
                $rule = $item;
                break;
            }
        }
        if (empty($rule)) {
            return false;
        }
        if (is_string($rule['required']) && method_exists($this, $rule['required'])) {
            return $this->{$rule['required']}($value, $data);
        }
        return $rule['required'];
    }

    private function getValidateType($rule): ?string
    {
        if (isset($rule['required'])) {
            return 'required';
        } elseif (isset($rule['format'])) {
            return 'format';
        } elseif (isset($rule['confirm'])) {
            return 'confirm';
        } elseif (isset($rule['range'])) {
            return 'range';
        } elseif (isset($rule['regex'])) {
            return 'regex';
        } elseif (isset($rule['validateFunction'])) {
            return 'validateFunction';
        } elseif (isset($rule['minimum']) || isset($rule['maximum'])) {
            return 'range_number';
        } elseif (isset($rule['min_length']) || isset($rule['max_length'])) {
            return 'range_length';
        }
        return null;
    }
}