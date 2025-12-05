# PHP 表单验证器

一个功能强大、灵活易用的PHP表单验证库，支持多种验证规则、多语言错误消息和场景验证。

## 项目概述

PHP表单验证器是一个轻量级但功能全面的验证库，专为简化Web表单和API请求的数据验证而设计。它提供了丰富的内置验证规则，同时支持自定义验证函数和多语言错误消息，使开发人员能够快速实现复杂的数据验证需求。

### 核心特性

- ✅ 支持多种内置验证规则（必填、格式检查、正则表达式、范围检查等）
- ✅ 支持多语言错误消息（中文、英文及自定义语言）
- ✅ 支持场景验证（不同场景使用不同的验证规则）
- ✅ 支持批量验证（收集所有错误信息）
- ✅ 支持自定义验证函数
- ✅ 支持验证失败时抛出异常或返回错误信息
- ✅ 简单易用的API接口
- ✅ 轻量级，无外部依赖

## 安装说明

### 使用 Composer 安装（推荐）

在您的项目根目录下运行以下命令：

```bash
composer require aqqwiwng/php-form-validator
```

### 手动安装

1. 从GitHub下载最新版本的源代码
2. 将`src`目录复制到您的项目中
3. 在需要使用的文件中引入自动加载器或直接包含所需的类文件

## 配置指南

### 基本配置

验证器支持以下配置选项：

| 配置项 | 类型 | 默认值 | 说明 |
|-------|------|--------|------|
| lang | string | "zh-CN" | 设置默认语言 |
| isBatchValidation | bool | false | 是否启用批量验证 |
| failThrowException | bool | true | 验证失败时是否抛出异常 |

### 语言配置

验证器默认支持中文（zh_CN）和英文（en_US）。您可以通过以下方式添加自定义语言：

1. 在`src/Validator/lang/`目录下创建新的语言文件（如`ja_JP.php`）
2. 按照现有语言文件的格式定义错误消息
3. 使用`setLang()`方法切换到自定义语言

## 使用示例

### 基本使用

```php
<?php

require_once 'vendor/autoload.php';

// 创建验证器实例
$validator = new Validator();

// 设置验证规则
$validator->setRules([
    'username' => [
        'label' => '用户名',
        'rules'=>[
            ['required'=>true],
            ['regex'=>'/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/'],
        ]
    ],
    'email' => [
        'label' => '邮箱',
        'rules'=>[
            ['required'=>true],
            ['type'=>'email'],
        ]
    ],
    'password' => [
        'label' => '密码',
        'rules'=>[
            ['required'=>true,'message'=>'请输入密码'],
            ['type'=>'strong_pwd'],
        ]
    ]
]);

// 待验证的数据
$data = [
    'username' => 'user123',
    'email' => 'invalid-email',
    'password' => 'weak'
];

try {
    // 执行验证
    if ($validator->check($data)) {
        echo '验证通过！';
    }
} catch (Validator\ValidateException $e) {
    // 处理验证失败
    echo '验证失败：' . $e->getMessage();
}
```

### 批量验证

```php
// 启用批量验证
$validator->batch(true);

// 禁用异常抛出，改为返回错误信息
$validator->failException(false);

// 执行验证
$isValid = $validator->check($data);

if (!$isValid) {
    // 获取所有错误信息
    $errors = $validator->getError();
    foreach ($errors as $field => $error) {
        echo $field . ': ' . $error . '<br>';
    }
}
```

### 场景验证

```php
// 设置验证规则
$validator->setRules([
    'username' => [
        'label' => '用户名',
        'rules'=>[
            ['required'=>true],
            ['regex'=>'/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/'],
        ]
    ],
    'email' => [
        'label' => '邮箱',
        'rules'=>[
            ['required'=>true],
            ['type'=>'email'],
        ]
    ],
    'password' => [
        'label' => '密码',
        'rules'=>[
            ['required'=>true],
            ['type'=>'strong_pwd'],
        ]
    ],
    'confirm_password' => [
        'label' => '确认密码',
        'rules'=>[
            ['required'=>true],
            ['confirm_field'=>'password'],
        ]
    ]
]);

// 设置场景
$validator->setScenes([
    'register' => ['username', 'email', 'password', 'confirm_password'],
    'login' => ['username', 'password']
]);

// 使用登录场景进行验证
$validator->scene('login');
$isValid = $validator->check($loginData);
```

### 自定义验证函数

```php
$validator->setRules([
    'age' => [
        'label' => '年龄',
        'rules'=>[
            ['required'=>true],
            ['validateFunction'=>function($value, $data){
                if ($value < 18 || $value > 100) {
                    return '年龄必须在18-100岁之间';
                }
                return true;
            }],
        ]
    ]
]);
```

### 多语言支持

```php
// 设置语言为英文
$validator->setLang('en_US');

// 或使用语言管理器直接设置
$langManager = new Validator\LangManager('en_US');
$validator->setLangManager($langManager);
```
### 继承 Validator 类

您可以创建一个自定义验证器类，继承 `Validator` 类，以实现自定义的验证逻辑。

```php
class MyValidator extends Validator
{
    // 自定义验证规则
    protected $rules = [
        'username' => [
            'label' => '用户名',
            'rules'=>[
                ['required'=>true],
                ['regex'=>'/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/'],
            ]
        ],
        'email' => [
            'label' => '邮箱',
            'rules'=>[
                ['required'=>true],
                ['type'=>'email'],
            ]
        ],
        'password' => [
            'label' => '密码',
            'rules'=>[
                ['required'=>true],
                ['type'=>'strong_pwd'],
            ]
        ],
        'confirm_password' => [
            'label' => '确认密码',
            'rules'=>[
                ['required'=>true],
                ['confirm'=>'password'],
            ]
        ],
        'age' => [
            'label' => '年龄',
            'rules'=>[
                ['required'=>true],
                // 自定义验证函数,可以是一个闭包
                ['validateFunction'=>function($value, $data){
                    if ($value < 18 || $value > 100) {
                        return '年龄必须在18-100岁之间';
                    }
                    return true;
                }],
                // 也可以使用自定义验证函数
                //['validateFunction'=>'validateAge'],
            ]
        ]
    ];
    // 自定义验证场景
    protected $scenes = [
        'register' => ['username', 'email', 'password', 'confirm_password'],
        'login' => ['username', 'password']
    ];

    // 一些特殊验证场景可以直接在类中定义方法修剪验证规则,方法格式为 scene_场景名()
    // 例如：login 场景下只需要验证 username、password 这两个字段
    // 登录场景下，username 字段不需要正则验证，password 字段不需要类型验证
    protected function scene_login()
    {
        $this->only(['username', 'password'])
             ->remove('username','regex')
             ->remove('password','type');
             // 也可以用append方法添加验证规则
             //->append('age', ['validateFunction'=>'validateAge']);
    }

    // 自定义验证函数
    protected function validateAge(array $rule, mixed $value, mixed $data): ?string
    {
        if ($value < 18 || $value > 100) {
            return '年龄必须在18-100岁之间';
        }
        return true;
    }
}
```

## API 文档

### Validator 类

#### 构造函数

```php
public function __construct()
```

创建一个新的验证器实例。

#### setRules()

```php
public function setRules(array $rules): Validator
```

设置验证规则。

参数：
- `$rules`：验证规则数组

返回值：
- 当前验证器实例（用于链式调用）

#### setScenes()

```php
public function setScenes(array $scenes): Validator
```

设置验证场景。

参数：
- `$scenes`：场景数组，键为场景名称，值为该场景下需要验证的字段列表

返回值：
- 当前验证器实例（用于链式调用）

#### scene()

```php
public function scene(string $scene): Validator
```

选择验证场景。

参数：
- `$scene`：场景名称

返回值：
- 当前验证器实例（用于链式调用）

#### batch()

```php
public function batch(bool $batch = true): Validator
```

设置是否启用批量验证。

参数：
- `$batch`：是否启用批量验证

返回值：
- 当前验证器实例（用于链式调用）

#### setFailThrowException()

```php
public function failException(bool $failThrowException = true): Validator
```

设置验证失败时是否抛出异常。

参数：
- `$failThrowException`：验证失败时是否抛出异常

返回值：
- 当前验证器实例（用于链式调用）

#### setLang()

```php
public function setLang(string $lang): Validator
```

设置验证器语言。

参数：
- `$lang`：语言代码（如 'zh_CN'、'en_US'）

返回值：
- 当前验证器实例（用于链式调用）

#### check()

```php
public function check($data): bool
```

执行验证。

参数：
- `$data`：待验证的数据数组

返回值：
- 验证通过返回 true，失败返回 false

抛出：
- `Validator\ValidateException`：当验证失败且 `failThrowException` 为 true 时

#### getError()

```php
public function getError(): array|string
```

获取验证错误信息。

返回值：
- 错误信息（字符串或数组，取决于是否启用批量验证）

### 验证规则

#### 内置验证规则

| 规则名称 | 类型 | 说明 |
|---------|------|------|
| required | bool/string | 是否必填，也可以是自定义验证函数名 |
| type | string | 内置格式验证（如 email、url、mobile 等） |
| regex | string | 正则表达式验证 |
| enum | array | 枚举值验证 |
| min | int | 最小值验证 |
| max | int | 最大值验证 |
| confirm | string | 验证字段值是否与指定字段值相同 |
| validateFunction | callable | 自定义验证函数 |

#### type 规则可选值

| 格式名称 | 说明 |
|---------|------|
| int | 整数 |
| bool | 布尔值 |
| float | 浮点数 |
| number | 数字 |
| string | 字符串 |
| email | 邮箱 |
| url | 网址 |
| mobile | 手机号 |
| id_card | 身份证号 |
| array | 数组 |
| object | 对象 |
| date | 日期 |
| timestamp | 时间戳 |
| pwd | 密码（至少包含字母和数字） |
| weak_pwd | 弱密码（6-18位字符） |
| strong_pwd | 强密码（至少8位，包含大小写字母、数字和特殊字符） |
| chinese | 中文 |

## 故障排除技巧

### 常见问题

1. **验证规则不生效**
   - 检查规则数组的格式是否正确
   - 确保字段名称与待验证数据的键名一致
   - 检查是否正确设置了验证场景

2. **多语言错误消息不显示**
   - 确保语言文件存在于 `src/Validator/lang/` 目录下
   - 检查语言代码格式是否正确（如 `zh_CN` 而不是 `zh-CN`）
   - 确保语言文件中的键名与验证规则一致

3. **自定义验证函数不执行**
   - 确保函数名或匿名函数正确设置
   - 检查函数是否返回了正确的结果（true 表示验证通过，字符串表示错误消息）

4. **验证异常未抛出**
   - 检查 `failThrowException` 配置是否为 true
   - 确保在 try/catch 块中捕获了 `Validator\ValidateException` 异常

### 调试技巧

1. **启用批量验证和禁用异常抛出**：
   ```php
   $validator->batch(true)->failException(false);
   $isValid = $validator->check($data);
   if (!$isValid) {
       var_dump($validator->getError());
   }
   ```

2. **检查当前验证规则**：
   ```php
   var_dump($validator->getCurrentRules());
   ```

3. **检查语言管理器加载的消息**：
   ```php
   $langManager = new Validator\LangManager();
   var_dump($langManager->getMessages());
   ```

## 贡献指南

我们欢迎社区成员为该项目做出贡献！如果您想参与开发，请遵循以下指南：

### 提交 Issue

1. 在提交 Issue 前，请先搜索现有 Issue，确保您的问题或建议尚未被提出
2. 清晰描述问题或建议，包括重现步骤、预期行为和实际行为
3. 如果可能，提供代码示例或截图

### 提交 Pull Request

1. Fork 本仓库
2. 创建一个新的分支（如 `feature/new-validation-rule` 或 `bugfix/email-validation`）
3. 在分支上进行修改
4. 确保所有修改符合项目的代码风格
5. 编写测试用例（如果适用）
6. 提交 Pull Request，描述您的修改内容和原因

### 代码风格

- 遵循 PSR-12 代码风格指南
- 类名使用 PascalCase，方法名和变量名使用 camelCase
- 函数和方法的参数要有类型提示
- 添加适当的注释，特别是复杂的逻辑

### 开发环境

- PHP 8.0 或更高版本
- Composer
- Git

## 许可证

本项目采用 Apache-2.0 许可证。详情请查看 [LICENSE](LICENSE) 文件。

## 联系方式

如果您有任何问题或建议，请通过以下方式联系我们：

- GitHub Issues：https://github.com/aqqwiwng/php-form-validator/issues
- Email：aqqwiwng@qq.com

---

感谢您使用 PHP 表单验证器！如果您觉得这个项目对您有帮助，请给我们一个 Star 支持一下！