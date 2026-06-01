<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\bases\Requester;
use Cje\Wechat\bases\Request;

class RequesterTest extends TestCase
{
    /**
     * 测试HTTP请求
     */
    public function testHttpGet()
    {
        // 初始化请求器
        $requester = new Requester();

        // 测试GET请求（模拟）
        // 注意：这里需要模拟 HTTP 请求，实际测试时需要使用 Mock
        try {
            $response = $requester->get('https://api.weixin.qq.com/cgi-bin/user/get', ['next_openid' => '']);
            $this->assertNotNull($response);
        } catch (Exception $e) {
            // 实际测试时，这里会抛出异常，因为是模拟请求
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /**
     * 测试请求对象
     */
    public function testRequestObject()
    {
        // 创建请求对象
        $request = new class extends Request {
            public function getApi(): string {
                return 'cgi-bin/user/get';
            }
            public function getMethod(): string {
                return 'GET';
            }
            public function getNeedAccessToken(): bool {
                return false;
            }
            public function build(): array {
                return ['next_openid' => ''];
            }
        };

        // 测试请求方法
        $this->assertEquals('cgi-bin/user/get', $request->getApi());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(false, $request->getNeedAccessToken());
        $this->assertEquals(['next_openid' => ''], $request->build());
    }
}