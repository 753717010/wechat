<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\event\EventDispatcher;
use Cje\Wechat\event\BaseEvent;

class EventTest extends TestCase
{
    /**
     * 测试事件系统
     */
    public function testEventDispatcher()
    {
        // 初始化事件调度器
        $dispatcher = new EventDispatcher();

        // 测试事件监听器
        $called = false;
        $eventData = null;

        // 注册事件监听器
        $dispatcher->listen('test.event', function ($event) use (&$called, &$eventData) {
            $called = true;
            $eventData = $event->getData();
        });

        // 创建事件
        $event = new BaseEvent('test.event');
        $event->setData(['key' => 'value']);

        // 触发事件
        $dispatcher->dispatch($event);

        // 验证事件监听器是否被调用
        $this->assertTrue($called);
        $this->assertEquals(['key' => 'value'], $eventData);
    }

    /**
     * 测试事件传播控制
     */
    public function testEventPropagation()
    {
        // 初始化事件调度器
        $dispatcher = new EventDispatcher();

        // 注册多个事件监听器
        $callCount = 0;

        $dispatcher->listen('test.event', function ($event) use (&$callCount) {
            $callCount++;
            // 停止事件传播
            $event->stopPropagation();
        });

        $dispatcher->listen('test.event', function ($event) use (&$callCount) {
            $callCount++;
        });

        // 创建事件
        $event = new BaseEvent('test.event');

        // 触发事件
        $dispatcher->dispatch($event);

        // 验证只有第一个监听器被调用
        $this->assertEquals(1, $callCount);
    }
}