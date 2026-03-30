# PHP 表单验证器

一个功能强大、灵活易用的 PHP 表单验证库，支持多种验证规则、多语言错误消息和场景验证。

## 功能特性

- ✅ **多种验证规则**：必填、类型检查、正则表达式、范围验证、枚举、确认验证等
- ✅ **多语言支持**：内置中文（zh_CN）和英文（en_US）错误消息
- ✅ **场景验证**：不同场景使用不同的验证规则
- ✅ **批量验证**：收集所有字段的错误信息
- ✅ **自定义验证**：支持自定义验证函数和闭包
- ✅ **链式调用**：流畅的 API 设计
- ✅ **轻量无依赖**：仅依赖 PHP 内置扩展

## 环境要求

- PHP 8.0+
- ext-json
- ext-intl

## 安装

### Composer 安装

```bash
composer require aqqwiwng/php-form-validator
```

### 手动安装

1. 下载源码并复制 `src` 目录到项目
2. 引入 `vendor/autoload.php` 或直接加载类文件

## 快速开始

```php
require_once 'vendor/autoload.php';

use Validator\Validator;

$validator = new Validator();

$validator->setRules([
    'username' => [
        'label' => '用户名',
        'rules' => [
            'required' => true,
            'type' => 'string',
            'regex' => '/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/'
        ]
    ],
    'email' => [
        'label' => '邮箱',
        'rules' => [
            'required' => true,
            'type' => 'email'
        ]
    ],
    'password' => [
        'label' => '密码',
        'rules' => [
            'required' => true,
            'type' => 'strong_pwd'
        ]
    ]
]);

$data = [
    'username' => 'test_user123',
    'email' => 'test@example.com',
    'password' => 'Test@123456'
];

$validator->failException(false);
if (!$validator->check($data)) {
    echo $validator->getError();
} else {
    echo '验证通过';
}
```

## 验证规则

### 规则配置结构

```php
$validator->setRules([
    '字段名' => [
        'label' => '显示名称',
        'rules' => [
            '规则名称' => 规则值,
            // ... 更多规则
        ]
    ]
]);
```

### 必填验证 (required)

```php
'username' => [
    'label' => '用户名',
    'rules' => [
        'required' => true
    ]
]
```

### 类型验证 (type)

支持以下类型：

| 类型 | 说明 | 示例 |
|-----|------|------|
| `string` | 字符串 | `'test'` |
| `int` | 整数 | `123` |
| `number` | 数字 | `123`, `1.5` |
| `float` | 浮点数 | `1.5` |
| `bool` | 布尔值 | `true`, `false` |
| `array` | 数组 | `['a', 'b']` |
| `object` | 对象 | `(object)['a'=>1]` |
| `email` | 电子邮箱 | `test@example.com` |
| `mobile` | 手机号 | `13800138000` |
| `url` | 网址 | `https://example.com` |
| `date` | 日期 | `2024-01-01` |
| `timestamp` | 时间戳 | `1704067200` |
| `id_card` | 身份证号 | `110101199001011234` |
| `chinese` | 中文 | `中文内容` |
| `pwd` | 密码（6-18位，不能全为字母、数字或特殊字符） | `Test@123` |
| `weak_pwd` | 弱密码（6-18位字母、数字或特殊字符） | `weak123` |
| `strong_pwd` | 强密码（至少8位，含大小写字母、数字和特殊字符） | `Test@123456` |

```php
'email' => [
    'label' => '邮箱',
    'rules' => [
        'type' => 'email'
    ]
],
'phone' => [
    'label' => '手机号',
    'rules' => [
        'type' => 'mobile'
    ]
]
```

### 正则验证 (regex)

```php
'username' => [
    'label' => '用户名',
    'rules' => [
        'regex' => '/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/'
    ]
]
```

### 枚举验证 (enum)

```php
'status' => [
    'label' => '状态',
    'rules' => [
        'enum' => ['pending', 'approved', 'rejected']
    ]
]
```

### 范围验证 (min/max/between)

```php
'age' => [
    'label' => '年龄',
    'rules' => [
        'min' => 18,
        'max' => 100
    ]
]

'quantity' => [
    'label' => '数量',
    'rules' => [
        'between' => ['min' => 1, 'max' => 100]  // 或分开写 min 和 max
    ]
]
```

### 确认验证 (confirm)

用于确认密码等场景：

```php
'password' => [
    'label' => '密码',
    'rules' => [
        'required' => true,
        'type' => 'strong_pwd'
    ]
],
'confirm_password' => [
    'label' => '确认密码',
    'rules' => [
        'required' => true,
        'confirm' => 'password'  // 指定要确认的字段名
    ]
]
```

### 自定义验证函数 (validateFunction)

```php
$validator->setRules([
    'age' => [
        'label' => '年龄',
        'rules' => [
            'required' => true,
            'validateFunction' => function($rule, $value, $data) {
                if ($value < 18 || $value > 100) {
                    return '年龄必须在18-100岁之间';
                }
                return true;
            }
        ]
    ]
]);

// 或使用方法名
class CustomValidator extends Validator {
    protected function validateAge(array $rule, mixed $value, mixed $data): ?string {
        if ($value < 18 || $value > 100) {
            return $rule['label'] . '必须在18-100岁之间';
        }
        return null;
    }
}
```

## API 参考

### 链式调用方法

| 方法 | 说明 | 示例 |
|-----|------|------|
| `setRules($rules)` | 设置验证规则 | `->setRules([...])` |
| `setScenes($scenes)` | 设置场景配置 | `->setScenes([...])` |
| `scene($name)` | 选择验证场景 | `->scene('login')` |
| `batch($enable)` | 启用/禁用批量验证 | `->batch(true)` |
| `failException($enable)` | 设置是否抛出异常 | `->failException(false)` |
| `setLang($lang)` | 设置语言 | `->setLang('en_US')` |

### 验证方法

| 方法 | 说明 |
|-----|------|
| `check($data)` | 执行验证，返回 bool |
| `getError()` | 获取错误信息 |

### 高级方法

| 方法 | 说明 |
|-----|------|
| `only($fields)` | 仅验证指定字段 |
| `append($field, $rule)` | 添加字段规则 |
| `remove($field, $ruleName)` | 删除字段规则 |

## 使用示例

### 基本验证

```php
$validator = new Validator();
$validator->setRules([
    'username' => [
        'label' => '用户名',
        'rules' => [
            'required' => true,
            'type' => 'string',
            'regex' => '/^[a-zA-Z]\w{5,19}$/'
        ]
    ],
    'email' => [
        'label' => '邮箱',
        'rules' => [
            'required' => true,
            'type' => 'email'
        ]
    ]
]);

try {
    $validator->check($data);
    echo '验证通过';
} catch (ValidateException $e) {
    echo '验证失败：' . $e->getMessage();
}
```

### 批量验证

```php
$validator->batch(true)->failException(false);
$result = $validator->check($data);

if (!$result) {
    $errors = $validator->getError();
    foreach ($errors as $field => $error) {
        echo "$field: $error\n";
    }
}
```

### 场景验证

```php
$validator->setRules([
    'username' => ['label' => '用户名', 'rules' => ['required' => true]],
    'password' => ['label' => '密码', 'rules' => ['required' => true, 'type' => 'pwd']],
    'email' => ['label' => '邮箱', 'rules' => ['required' => true, 'type' => 'email']]
]);

$validator->setScenes([
    'login' => ['username', 'password'],
    'register' => ['username', 'password', 'email']
]);

// 登录场景
$validator->scene('login');
$validator->check($loginData);

// 注册场景
$validator->scene('register');
$validator->check($registerData);
```

### 多语言支持

```php
// 切换到英文
$validator->setLang('en_US');

// 切换到中文
$validator->setLang('zh_CN');
```

添加自定义语言：在 `src/Validator/lang/` 目录下创建语言文件。

## 错误消息

默认错误消息模板：

| 规则 | 默认消息 |
|-----|---------|
| `required` | 请填写{label} |
| `type` | {label}类型错误 |
| `regex` | {label}格式不正确 |
| `enum` | {label}不在允许范围内 |
| `confirm` | {label}与{confirm_label}不一致 |
| `min` | {label}不得小于{min} |
| `max` | {label}不得大于{max} |
| `between` | {label}必须在{min}至{max}之间 |
| `pwd` | {label}须为6-18位 |
| `weak_pwd` | {label}仅限6-18位字母、数字或特殊字符 |
| `strong_pwd` | {label}至少8位，需含大小写字母、数字及特殊字符 |

## 项目结构

```
src/Validator/
├── Validator.php          # 主验证器类
├── BaseValidator.php      # 基础验证器（验证方法实现）
├── Utils.php              # 工具类
├── Message.php            # 错误消息类
├── LangManager.php        # 语言管理类
├── ValidateException.php  # 验证异常类
└── lang/
    ├── zh_CN.php          # 中文语言包
    └── en_US.php         # 英文语言包
```

## 许可证

Apache-2.0 License
