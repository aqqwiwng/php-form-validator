<?php

/**
 * PHP表单验证器测试示例
 * 
 * 测试目标：全面验证Validator类的功能，包括各种验证规则、场景验证、批量验证和多语言支持
 * 测试环境：PHP 8.0+，ext-json，ext-intl
 * 测试工具：PHPUnit（可选）
 */

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

// 如果没有使用Composer，直接引入类文件
// require_once __DIR__ . '/../src/Validator.php';
// require_once __DIR__ . '/../src/Validator/BaseValidator.php';
// require_once __DIR__ . '/../src/Validator/LangManager.php';
// require_once __DIR__ . '/../src/Validator/Message.php';
// require_once __DIR__ . '/../src/Validator/Utils.php';
// require_once __DIR__ . '/../src/Validator/ValidateException.php';

use Validator\ValidateException;
use Validator\Validator;

// 自定义验证器类，用于测试继承扩展
class CustomValidator extends Validator {
    protected function validateAge(array $rule, mixed $value, mixed $data): ?string {
        if ($value < 18 || $value > 100) {
            return $rule['label'] . '必须在18-100岁之间';
        }
        return null;
    }
}

class ValidatorTest {
    
    private $validator;
    
    public function __construct() {
        $this->validator = new Validator();
    }
    
    /**
     * 测试准备：初始化验证器
     */
    public function setUp() {
        $this->validator = new Validator();
    }
    
    /**
     * 测试用例1：基本验证功能测试
     * 测试目标：验证required、type、regex等基本验证规则
     */
    public function testBasicValidation() {
        echo "\n=== 测试用例1：基本验证功能测试 ===\n";
        
        // 设置验证规则
        $this->validator->setRules([
            'username' => [
                'label' => '用户名',
                'rules' => [
                    ['required' => true],
                    ['type' => 'string'],
                    ['regex' => '/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/']
                ]
            ],
            'email' => [
                'label' => '邮箱',
                'rules' => [
                    ['required' => true],
                    ['type' => 'email']
                ]
            ],
            'password' => [
                'label' => '密码',
                'rules' => [
                    ['required' => true],
                    ['type' => 'strong_pwd']
                ]
            ]
        ]);
        
        // 测试场景1：有效输入
        echo "\n测试场景1：有效输入\n";
        $validData = [
            'username' => 'test_user123',
            'email' => 'test@example.com',
            'password' => 'Test@123456'
        ];
        
        try {
            $result = $this->validator->check($validData);
            $this->assertTrue($result, "有效输入验证失败");
            echo "✅ 有效输入验证通过\n";
        } catch (ValidateException $e) {
            $this->assertTrue(false, "有效输入验证抛出异常: " . $e->getMessage());
        }
        
        // 测试场景2：无效输入（用户名格式错误）
        echo "\n测试场景2：无效输入（用户名格式错误）\n";
        $invalidData1 = [
            'username' => '123invalid',
            'email' => 'test@example.com',
            'password' => 'Test@123456'
        ];
        
        $this->validator->failException(false);
        $result = $this->validator->check($invalidData1);
        $this->assertFalse($result, "无效输入（用户名）验证通过");
        echo "✅ 无效用户名验证失败（符合预期）\n";
        
        // 测试场景3：无效输入（邮箱格式错误）
        echo "\n测试场景3：无效输入（邮箱格式错误）\n";
        $invalidData2 = [
            'username' => 'test_user123',
            'email' => 'invalid-email',
            'password' => 'Test@123456'
        ];
        
        $result = $this->validator->check($invalidData2);
        $this->assertFalse($result, "无效输入（邮箱）验证通过");
        echo "✅ 无效邮箱验证失败（符合预期）\n";
        
        // 测试场景4：无效输入（密码强度不足）
        echo "\n测试场景4：无效输入（密码强度不足）\n";
        $invalidData3 = [
            'username' => 'test_user123',
            'email' => 'test@example.com',
            'password' => 'weak'
        ];
        
        $result = $this->validator->check($invalidData3);
        $this->assertFalse($result, "无效输入（密码）验证通过");
        echo "✅ 弱密码验证失败（符合预期）\n";
    }
    
    /**
     * 测试用例2：批量验证功能测试
     * 测试目标：验证批量验证模式下的错误信息收集
     */
    public function testBatchValidation() {
        echo "\n=== 测试用例2：批量验证功能测试 ===\n";
        
        // 设置验证规则
        $this->validator->setRules([
            'username' => [
                'label' => '用户名',
                'rules' => [
                    ['required' => true],
                    ['type' => 'string'],
                    ['regex' => '/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/']
                ]
            ],
            'email' => [
                'label' => '邮箱',
                'rules' => [
                    ['required' => true],
                    ['type' => 'email']
                ]
            ]
        ]);
        
        // 启用批量验证
        $this->validator->batch(true)->failException(false);
        
        // 测试数据：多个字段同时出错
        $invalidData = [
            'username' => '123', // 用户名太短
            'email' => 'invalid-email' // 邮箱格式错误
        ];
        
        $result = $this->validator->check($invalidData);
        $errors = $this->validator->getError();
        
        // 验证结果
        $this->assertFalse($result, "批量验证：无效输入验证通过");
        $this->assertIsArray($errors, "批量验证：错误信息不是数组");
        $this->assertCount(2, $errors, "批量验证：错误信息数量不符合预期");
        
        echo "✅ 批量验证通过，收集到 " . count($errors) . " 个错误\n";
        foreach ($errors as $field => $error) {
            echo "- $field: $error\n";
        }
    }
    
    /**
     * 测试用例3：场景验证功能测试
     * 测试目标：验证不同场景下的字段验证
     */
    public function testSceneValidation() {
        echo "\n=== 测试用例3：场景验证功能测试 ===\n";
        
        // 设置验证规则
        $this->validator->setRules([
            'username' => [
                'label' => '用户名',
                'rules' => [
                    ['required' => true],
                    ['type' => 'string']
                ]
            ],
            'email' => [
                'label' => '邮箱',
                'rules' => [
                    ['required' => true],
                    ['type' => 'email']
                ]
            ],
            'password' => [
                'label' => '密码',
                'rules' => [
                    ['required' => true],
                    ['type' => 'strong_pwd']
                ]
            ],
            'confirm_password' => [
                'label' => '确认密码',
                'rules' => [
                    ['required' => true],
                    ['confirm_field' => 'password']
                ]
            ]
        ]);
        
        // 设置场景
        $this->validator->setScenes([
            'register' => ['username', 'email', 'password', 'confirm_password'],
            'login' => ['username', 'password']
        ]);
        
        // 测试登录场景
        echo "\n测试场景：登录场景\n";
        $this->validator->scene('login')->failException(false);
        
        $loginData = [
            'username' => 'test_user',
            'password' => 'Test@123456'
        ];
        
        $result = $this->validator->check($loginData);
        $this->assertTrue($result, "登录场景验证失败");
        echo "✅ 登录场景验证通过\n";
        
        // 测试注册场景
        echo "\n测试场景：注册场景\n";
        $this->validator->scene('register');
        
        $registerData = [
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => 'Test@123456',
            'confirm_password' => 'Test@123456'
        ];
        
        $result = $this->validator->check($registerData);
        $this->assertTrue($result, "注册场景验证失败");
        echo "✅ 注册场景验证通过\n";
    }
    
    /**
     * 测试用例4：多语言支持测试
     * 测试目标：验证不同语言下的错误消息
     */
    public function testMultilingualSupport() {
        echo "\n=== 测试用例4：多语言支持测试 ===\n";
        
        // 设置验证规则
        $this->validator->setRules([
            'email' => [
                'label' => '邮箱',
                'rules' => [
                    ['required' => true],
                    ['type' => 'email']
                ]
            ]
        ]);
        
        // 测试数据：无效邮箱
        $invalidData = ['email' => 'invalid-email'];
        
        // 测试中文错误消息
        echo "\n测试：中文错误消息\n";
        $this->validator->setLang('zh_CN')->failException(false);
        $this->validator->check($invalidData);
        $errorZh = $this->validator->getError();
        echo "中文错误：$errorZh\n";
        
        // 测试英文错误消息
        echo "\n测试：英文错误消息\n";
        $this->validator->setLang('en_US')->failException(false);
        $this->validator->check($invalidData);
        $errorEn = $this->validator->getError();
        echo "英文错误：$errorEn\n";
        
        $this->assertNotEquals($errorZh, $errorEn, "多语言：错误消息相同");
        echo "✅ 多语言支持测试通过\n";
    }
    
    /**
     * 测试用例5：边界值测试
     * 测试目标：验证边界条件下的验证行为
     */
    public function testBoundaryValues() {
        echo "\n=== 测试用例5：边界值测试 ===\n";
        
        // 设置验证规则
        $this->validator->setRules([
            'age' => [
                'label' => '年龄',
                'rules' => [
                    ['required' => true],
                    ['type' => 'int'],
                    ['min' => 18,'max' => 100]
                ]
            ],
            'username' => [
                'label' => '用户名',
                'rules' => [
                    ['required' => true],
                    ['regex' => '/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/']
                ]
            ]
        ]);
        
        $this->validator->failException(false);
        
        // 测试年龄边界
        echo "\n测试年龄边界：\n";
        
        // 最小边界值
        $dataMin = ['age' => 18, 'username' => 'testuser123'];
        $resultMin = $this->validator->check($dataMin);
        echo "- 年龄18岁：" . ($resultMin ? "✅ 通过" : "❌ 失败") . "\n";
        $this->assertTrue($resultMin, "边界值：年龄最小值验证失败");
        
        // 小于最小边界值
        $dataBelowMin = ['age' => 17, 'username' => 'testuser123'];
        $resultBelowMin = $this->validator->check($dataBelowMin);
        echo "- 年龄17岁：" . ($resultBelowMin ? "❌ 通过" : "✅ 失败") . "\n";
        $this->assertFalse($resultBelowMin, "边界值：年龄小于最小值验证通过");
        
        // 最大边界值
        $dataMax = ['age' => 100, 'username' => 'testuser123'];
        $resultMax = $this->validator->check($dataMax);
        echo "- 年龄100岁：" . ($resultMax ? "✅ 通过" : "❌ 失败") . "\n";
        $this->assertTrue($resultMax, "边界值：年龄最大值验证失败");
        
        // 大于最大边界值
        $dataAboveMax = ['age' => 101, 'username' => 'testuser123'];
        $resultAboveMax = $this->validator->check($dataAboveMax);
        echo "- 年龄101岁：" . ($resultAboveMax ? "❌ 通过" : "✅ 失败") . "\n";
        $this->assertFalse($resultAboveMax, "边界值：年龄大于最大值验证通过");
        
        // 测试用户名长度边界
        echo "\n测试用户名长度边界：\n";
        
        $shortUser = ['age' => 25, 'username' => 'test1'];
        $resultShort = $this->validator->check($shortUser);
        echo "- 用户名5个字符：" . ($resultShort ? "❌ 通过" : "✅ 失败") . "\n";
        $this->assertFalse($resultShort, "边界值：用户名太短验证通过");
        
        $longUser = ['age' => 25, 'username' => 'testuser1234567890']; // 20个字符
        $resultLong = $this->validator->check($longUser);
        echo "- 用户名20个字符：" . ($resultLong ? "✅ 通过" : "❌ 失败") . "\n";
        $this->assertTrue($resultLong, "边界值：用户名最大长度验证失败");
        
        $tooLongUser = ['age' => 25, 'username' => 'testuser12345678901234567890']; // 21个字符
        $resultTooLong = $this->validator->check($tooLongUser);
        echo "- 用户名21个字符：" . ($resultTooLong ? "❌ 通过" : "✅ 失败") . "\n";
        $this->assertFalse($resultTooLong, "边界值：用户名超过最大长度验证通过");
    }
    
    /**
     * 测试用例6：自定义验证函数测试
     * 测试目标：验证自定义验证函数的使用
     */
    public function testCustomValidation() {
        echo "\n=== 测试用例6：自定义验证函数测试 ===\n";
        
        $customValidator = new CustomValidator();
        
        // 设置验证规则
        $customValidator->setRules([
            'age' => [
                'label' => '年龄',
                'rules' => [
                    ['required' => true],
                    ['validateFunction' => 'validateAge']
                ]
            ]
        ]);
        
        $customValidator->failException(false);
        
        // 测试有效年龄
        $validAgeData = ['age' => 25];
        $resultValid = $customValidator->check($validAgeData);
        echo "测试有效年龄：" . ($resultValid ? "✅ 通过" : "❌ 失败") . "\n";
        $this->assertTrue($resultValid, "自定义验证：有效年龄验证失败");
        
        // 测试无效年龄
        $invalidAgeData = ['age' => 17];
        $resultInvalid = $customValidator->check($invalidAgeData);
        $error = $customValidator->getError();
        echo "测试无效年龄：" . ($resultInvalid ? "❌ 通过" : "✅ 失败") . "\n";
        echo "错误信息：$error\n";
        $this->assertFalse($resultInvalid, "自定义验证：无效年龄验证通过");
    }
    
    /**
     * 测试用例7：空值处理测试
     * 测试目标：验证非必填字段的空值处理
     */
    public function testEmptyValueHandling() {
        echo "\n=== 测试用例7：空值处理测试 ===\n";
        
        // 设置验证规则：phone字段非必填
        $this->validator->setRules([
            'name' => [
                'label' => '姓名',
                'rules' => [
                    ['required' => true],
                    ['type' => 'string']
                ]
            ],
            'phone' => [
                'label' => '电话',
                'rules' => [
                    ['required' => false],
                    ['type' => 'mobile']
                ]
            ]
        ]);
        
        $this->validator->failException(false);
        
        // 测试：提供姓名，不提供电话
        $data1 = ['name' => '张三'];
        $result1 = $this->validator->check($data1);
        echo "测试：提供姓名，不提供电话：" . ($result1 ? "✅ 通过" : "❌ 失败") . "\n";
        $this->assertTrue($result1, "空值处理：非必填字段不提供值验证失败");
        
        // 测试：提供姓名，提供空电话
        $data2 = ['name' => '张三', 'phone' => ''];
        $result2 = $this->validator->check($data2);
        echo "测试：提供姓名，提供空电话：" . ($result2 ? "✅ 通过" : "❌ 失败") . "\n";
        $this->assertTrue($result2, "空值处理：非必填字段提供空值验证失败");
        
        // 测试：提供姓名，提供无效电话
        $data3 = ['name' => '张三', 'phone' => '123456789'];
        $result3 = $this->validator->check($data3);
        echo "测试：提供姓名，提供无效电话：" . ($result3 ? "❌ 通过" : "✅ 失败") . "\n";
        $this->assertFalse($result3, "空值处理：非必填字段提供无效值验证通过");
    }
    
    /**
     * 断言函数：检查条件是否为真
     */
    private function assertTrue($condition, $message = '') {
        if (!$condition) {
            echo "❌ 断言失败：$message\n";
            exit(1);
        }
    }
    
    /**
     * 断言函数：检查条件是否为假
     */
    private function assertFalse($condition, $message = '') {
        if ($condition) {
            echo "❌ 断言失败：$message\n";
            exit(1);
        }
    }
    
    /**
     * 断言函数：检查值是否为数组
     */
    private function assertIsArray($value, $message = '') {
        if (!is_array($value)) {
            echo "❌ 断言失败：$message\n";
            exit(1);
        }
    }
    
    /**
     * 断言函数：检查数组元素数量
     */
    private function assertCount($expected, $array, $message = '') {
        if (count($array) !== $expected) {
            echo "❌ 断言失败：$message ，实际数量：" . count($array) . "，预期数量：$expected\n";
            exit(1);
        }
    }
    
    /**
     * 断言函数：检查两个值是否不相等
     */
    private function assertNotEquals($value1, $value2, $message = '') {
        if ($value1 === $value2) {
            echo "❌ 断言失败：$message\n";
            exit(1);
        }
    }
    
    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "\n====================================\n";
        echo "PHP 表单验证器测试套件\n";
        echo "====================================\n";
        
        // 记录测试开始时间
        $startTime = microtime(true);
        
        // 运行所有测试用例
        $this->setUp();
        $this->testBasicValidation();
        
        $this->setUp();
        $this->testBatchValidation();
        
        $this->setUp();
        $this->testSceneValidation();
        
        $this->setUp();
        $this->testMultilingualSupport();
        
        $this->setUp();
        $this->testBoundaryValues();
        
        $this->setUp();
        $this->testCustomValidation();
        
        $this->setUp();
        $this->testEmptyValueHandling();
        
        // 计算测试时间
        $endTime = microtime(true);
        $executionTime = number_format($endTime - $startTime, 4);
        
        echo "\n====================================\n";
        echo "✅ 所有测试通过！\n";
        echo "执行时间：{$executionTime} 秒\n";
        echo "====================================\n";
    }
}

// 运行测试
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $test = new ValidatorTest();
    $test->runAllTests();
}
