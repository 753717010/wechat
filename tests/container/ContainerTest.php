<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\container\Container;

class ContainerTest extends TestCase
{
    /**
     * 测试依赖注入容器
     */
    public function testContainer()
    {
        // 初始化容器
        $container = Container::create();

        // 测试服务注册和解析
        $container->bind('testService', function () {
            return 'test_value';
        });

        $this->assertEquals('test_value', $container->make('testService'));
    }

    /**
     * 测试单例服务
     */
    public function testSingleton()
    {
        // 初始化容器
        $container = Container::create();

        // 测试单例服务
        $container->singleton('testSingleton', function () {
            return new class {
                public $value = 0;
                public function increment() {
                    $this->value++;
                }
            };
        });

        // 获取单例实例
        $instance1 = $container->make('testSingleton');
        $instance1->increment();

        // 获取另一个实例
        $instance2 = $container->make('testSingleton');

        // 验证是同一个实例
        $this->assertEquals($instance1->value, $instance2->value);
        $this->assertEquals(1, $instance2->value);
    }

    /**
     * 测试服务存在检查
     */
    public function testHas()
    {
        // 初始化容器
        $container = Container::create();

        // 测试服务存在检查
        $container->bind('testService', function () {
            return 'test_value';
        });

        $this->assertTrue($container->has('testService'));
        $this->assertFalse($container->has('nonExistentService'));
    }

    /**
     * 测试依赖解析
     */
    public function testDependencyResolution()
    {
        // 初始化容器
        $container = Container::create();

        // 注册依赖服务
        $container->bind('dependency', function () {
            return 'dependency_value';
        });

        // 注册依赖于其他服务的服务
        $container->bind('service', function (Container $container) {
            return 'service_' . $container->make('dependency');
        });

        // 解析服务
        $this->assertEquals('service_dependency_value', $container->make('service'));
    }
}