<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\middleware\MiddlewareManager;

class MiddlewareTest extends TestCase
{
    /**
     * 测试中间件系统
     */
    public function testMiddleware()
    {
        // 初始化中间件管理器
        $middlewareManager = new MiddlewareManager();

        // 测试中间件执行
        $executionOrder = [];

        // 注册中间件
        $middlewareManager->add(function ($request, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware1_start';
            $response = $next($request);
            $executionOrder[] = 'middleware1_end';
            return $response;
        });

        $middlewareManager->add(function ($request, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware2_start';
            $response = $next($request);
            $executionOrder[] = 'middleware2_end';
            return $response;
        });

        // 执行中间件
        $request = 'test_request';
        $response = $middlewareManager->handle($request, function ($request) use (&$executionOrder) {
            $executionOrder[] = 'handler';
            return 'test_response';
        });

        // 验证中间件执行顺序
        $expectedOrder = [
            'middleware1_start',
            'middleware2_start',
            'handler',
            'middleware2_end',
            'middleware1_end'
        ];
        $this->assertEquals($expectedOrder, $executionOrder);
        $this->assertEquals('test_response', $response);
    }

    /**
     * 测试中间件批量注册
     */
    public function testMiddlewareBatchAdd()
    {
        // 初始化中间件管理器
        $middlewareManager = new MiddlewareManager();

        // 测试中间件批量注册
        $executionOrder = [];

        // 批量注册中间件
        $middlewares = [
            function ($request, $next) use (&$executionOrder) {
                $executionOrder[] = 'middleware1';
                return $next($request);
            },
            function ($request, $next) use (&$executionOrder) {
                $executionOrder[] = 'middleware2';
                return $next($request);
            }
        ];

        $middlewareManager->addMany($middlewares);

        // 执行中间件
        $response = $middlewareManager->handle('test_request', function ($request) use (&$executionOrder) {
            $executionOrder[] = 'handler';
            return 'test_response';
        });

        // 验证中间件执行顺序
        $expectedOrder = ['middleware1', 'middleware2', 'handler'];
        $this->assertEquals($expectedOrder, $executionOrder);
        $this->assertEquals('test_response', $response);
    }
}