# PHP 表单验证器测试指南

## 测试目标

本测试指南旨在全面验证 PHP 表单验证器的功能正确性、稳定性和性能，确保验证器能够在各种场景下可靠工作。

## 测试环境要求

### 1. 系统环境

- **操作系统**：Windows/macOS/Linux
- **PHP 版本**：8.0+（推荐 8.1+）
- **内存**：至少 512MB RAM
- **磁盘空间**：至少 100MB 可用空间

### 2. 依赖扩展

- `ext-json`：处理 JSON 数据
- `ext-intl`：国际化支持

### 3. 开发工具（可选）

- **Composer**：依赖管理
- **PHPUnit**：单元测试框架
- **IDE**：PhpStorm / VS Code / Sublime Text 等

## 测试用例结构

测试用例分为 7 个主要测试场景，覆盖验证器的核心功能：

| 测试用例 | 测试目标 | 覆盖范围 |
|---------|---------|----------|
| 1. 基本验证功能 | 验证各种基本验证规则的正确性 | 必填项、类型验证、正则表达式、密码强度等 |
| 2. 批量验证功能 | 验证批量验证模式下的错误收集 | 多个字段同时验证、错误信息聚合 |
| 3. 场景验证功能 | 验证不同场景下的字段验证 | 登录场景、注册场景等 |
| 4. 多语言支持 | 验证国际化消息功能 | 中英文错误消息切换 |
| 5. 边界值测试 | 验证边界条件下的验证行为 | 最小/最大值、长度限制等 |
| 6. 自定义验证函数 | 验证自定义验证逻辑 | 内置扩展验证、闭包验证 |
| 7. 空值处理 | 验证非必填字段的空值处理 | 空值跳过验证、空字符串处理 |

## 测试数据设计

### 1. 有效数据示例

```php
$validData = [
    'username' => 'test_user123',
    'email' => 'test@example.com',
    'password' => 'Test@123456',
    'age' => 25,
    'phone' => '13800138000'
];
```

### 2. 无效数据示例

```php
$invalidData = [
    'username' => '123invalid', // 以数字开头，不符合正则
    'email' => 'invalid-email', // 邮箱格式错误
    'password' => 'weak',       // 密码强度不足
    'age' => 17,                // 年龄太小
    'phone' => '123456'          // 手机号格式错误
];
```

### 3. 边界值数据

```php
$boundaryData = [
    'age_min' => 18,                // 最小年龄边界
    'age_max' => 100,               // 最大年龄边界
    'username_min' => 'test123',    // 用户名最小长度
    'username_max' => 'testuser1234567890' // 用户名最大长度
];
```

## 运行测试

### 方法 1：直接运行测试文件

```bash
# 进入项目根目录
cd /path/to/php-form-validator

# 运行测试脚本
php tests/ValidatorTest.php
```

### 方法 2：使用 PHPUnit（推荐）

1. 安装 PHPUnit：

```bash
composer require --dev phpunit/phpunit
```

2. 创建 PHPUnit 配置文件（phpunit.xml）：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         cacheDirectory=".phpunit.cache">

    <testsuites>
        <testsuite name="Validator Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

3. 运行 PHPUnit 测试：

```bash
vendor/bin/phpunit
```

## 测试结果验证

### 1. 预期输出格式

```
=== 测试用例1：基本验证功能测试 ===

测试场景1：有效输入
✅ 有效输入验证通过

测试场景2：无效输入（用户名格式错误）
✅ 无效用户名验证失败（符合预期）

测试场景3：无效输入（邮箱格式错误）
✅ 无效邮箱验证失败（符合预期）

测试场景4：无效输入（密码强度不足）
✅ 弱密码验证失败（符合预期）

=== 测试用例2：批量验证功能测试 ===
✅ 批量验证通过，收集到 2 个错误
- username: 用户名格式不正确
- email: 邮箱类型错误
```

### 2. 错误情况处理

如果测试失败，会显示具体的失败信息：

```
❌ 断言失败：无效输入（用户名）验证通过
```

### 3. 批量验证结果

批量验证模式下会显示所有错误信息：

```
=== 测试用例2：批量验证功能测试 ===
✅ 批量验证通过，收集到 2 个错误
- username: 用户名格式不正确
- email: 邮箱类型错误
```

## 验证规则测试覆盖

### 1. 必填验证 (required)

```php
'username' => [
    'label' => '用户名',
    'rules' => [
        'required' => true
    ]
]
```

测试场景：

- 有值时应通过验证
- 值为空时应返回错误

### 2. 类型验证 (type)

支持的类型：string、int、number、float、bool、array、object、email、mobile、url、date、timestamp、id_card、chinese、pwd、weak_pwd、strong_pwd

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

### 3. 正则验证 (regex)

```php
'username' => [
    'label' => '用户名',
    'rules' => [
        'regex' => '/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/'
    ]
]
```

测试场景：

- 有效用户名（如 `test_user123`）应通过
- 无效用户名（如 `123invalid`）应返回错误

### 4. 枚举验证 (enum)

```php
'status' => [
    'label' => '状态',
    'rules' => [
        'enum' => ['pending', 'approved', 'rejected']
    ]
]
```

### 5. 范围验证 (min/max/between)

```php
'age' => [
    'label' => '年龄',
    'rules' => [
        'min' => 18,
        'max' => 100
    ]
]
```

### 6. 确认验证 (confirm)

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
        'confirm' => 'password'
    ]
]
```

### 7. 自定义验证函数 (validateFunction)

```php
'age' => [
    'label' => '年龄',
    'rules' => [
        'required' => true,
        'validateFunction' => 'validateAge'
    ]
]
```

或使用闭包：

```php
'age' => [
    'label' => '年龄',
    'rules' => [
        'validateFunction' => function($rule, $value, $data) {
            if ($value < 18 || $value > 100) {
                return '年龄必须在18-100岁之间';
            }
            return true;
        }
    ]
]
```

## 功能特性测试

### 1. 批量验证模式

```php
$validator->batch(true)->failException(false);
$result = $validator->check($data);
$errors = $validator->getError();
```

验证要点：

- 多个字段错误时收集所有错误
- 错误信息按字段分组

### 2. 场景验证

```php
$validator->setScenes([
    'login' => ['username', 'password'],
    'register' => ['username', 'password', 'email']
]);

$validator->scene('login');
$validator->check($loginData);
```

### 3. 多语言支持

```php
$validator->setLang('en_US');
$validator->setLang('zh_CN');
```

### 4. 异常处理

```php
try {
    $validator->check($data);
} catch (ValidateException $e) {
    echo $e->getMessage();
}
```

### 5. 空值处理

非必填字段值为空时跳过验证：

```php
'phone' => [
    'label' => '电话',
    'rules' => [
        'required' => false,
        'type' => 'mobile'
    ]
]
```

## 故障排除

### 1. 常见错误及解决方案

| 错误信息 | 可能原因 | 解决方案 |
|---------|---------|----------|
| `Class 'Validator\Validator' not found` | 自动加载失败 | 检查 Composer 安装或手动引入所有类文件 |
| `Function 'intl_is_failure' not found` | 缺少 intl 扩展 | 安装或启用 intl 扩展 |
| `Invalid JSON syntax` | JSON 格式错误 | 检查输入数据的 JSON 格式 |
| `Cannot declare class Validator, because the name is already in use` | 命名冲突 | 检查命名空间或类名是否重复 |

### 2. 调试技巧

1. **启用错误显示**：

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

2. **检查验证规则**：

```php
var_dump($this->validator->getCurrentRules());
```

3. **获取错误信息**：

```php
$validator->failException(false);
if (!$validator->check($data)) {
    print_r($validator->getError());
}
```

## 性能测试

### 1. 响应时间测试

单条验证请求应在 1ms 内完成，批量验证（10 个字段）应在 5ms 内完成。

### 2. 内存占用测试

验证器初始化和单次验证的内存占用应小于 1MB。

### 3. 并发测试（可选）

使用 Apache Bench 或其他工具测试并发请求：

```bash
ab -n 1000 -c 10 http://localhost/test-validator.php
```

## 测试结论

通过本测试指南的全面验证，确保 PHP 表单验证器能够：

1. **功能正确**：所有验证规则按预期工作
2. **性能稳定**：响应时间在可接受范围内
3. **易于扩展**：支持自定义验证规则
4. **用户友好**：提供清晰的错误信息
5. **国际化**：支持多语言环境

---

**测试完成标准**：所有 7 个测试用例全部通过，无任何错误或警告。

**更新日期**：2026-03-30
