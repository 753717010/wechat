<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\bases\Config;

class ConfigTest extends TestCase
{
    /**
     * 测试配置管理
     */
    public function testConfig()
    {
        // 初始化配置
        $config = new Config([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret',
            'token' => 'test-token',
            'encodingAESKey' => 'test-encoding-aes-key',
            'stable' => true
        ]);

        // 测试获取配置
        $this->assertEquals('test-app-id', $config->get('appId'));
        $this->assertEquals('test-app-secret', $config->get('appSecret'));
        $this->assertEquals('test-token', $config->get('token'));
        $this->assertEquals('test-encoding-aes-key', $config->get('encodingAESKey'));
        $this->assertEquals(true, $config->get('stable'));

        // 测试获取不存在的配置
        $this->assertNull($config->get('nonExistent'));
        $this->assertEquals('default', $config->get('nonExistent', 'default'));

        // 测试检查配置是否存在
        $this->assertTrue($config->has('appId'));
        $this->assertFalse($config->has('nonExistent'));

        // 测试设置配置
        $config->set('newKey', 'newValue');
        $this->assertEquals('newValue', $config->get('newKey'));
    }

    /**
     * 测试配置验证
     */
    public function testConfigValidation()
    {
        // 初始化配置
        $config = new Config([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret'
        ]);

        // 测试必要配置项
        $this->assertTrue($config->has('appId'));
        $this->assertTrue($config->has('appSecret'));
    }
}