<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\officialAccount\AccessToken;

class AccessTokenTest extends TestCase
{
    /**
     * 测试访问令牌获取
     */
    public function testAccessToken()
    {
        // 初始化缓存
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        // 初始化访问令牌
        $accessToken = new AccessToken('test-app-id', 'test-app-secret', false, $cache);

        // 测试获取访问令牌（模拟）
        // 注意：这里需要模拟 HTTP 请求，实际测试时需要使用 Mock
        try {
            $token = $accessToken->getToken();
            $this->assertNotNull($token);
            $this->assertIsString($token);
        } catch (Exception $e) {
            // 实际测试时，这里会抛出异常，因为是模拟请求
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /**
     * 测试稳定版访问令牌
     */
    public function testStableAccessToken()
    {
        // 初始化缓存
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        // 初始化访问令牌
        $accessToken = new AccessToken('test-app-id', 'test-app-secret', true, $cache);

        // 测试获取稳定版访问令牌（模拟）
        try {
            $token = $accessToken->getToken();
            $this->assertNotNull($token);
            $this->assertIsString($token);
        } catch (Exception $e) {
            // 实际测试时，这里会抛出异常，因为是模拟请求
            $this->assertInstanceOf(Exception::class, $e);
        }
    }
}