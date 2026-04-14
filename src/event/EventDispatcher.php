<?php
/**
 * 事件调度器
 * 管理事件和监听器
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\event;

use Closure;

class EventDispatcher
{
    /**
     * 事件监听器
     * @var array
     */
    protected $listeners = [];

    /**
     * 注册事件监听器
     * 
     * @param string $event 事件名称
     * @param Closure|string $listener 监听器
     * @param int $priority 优先级
     * @return self
     */
    public function listen(string $event, $listener, int $priority = 0): self
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];

        // 按优先级排序
        usort($this->listeners[$event], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $this;
    }

    /**
     * 触发事件
     * 
     * @param Event|string $event 事件对象或事件名称
     * @param array $data 事件数据
     * @return Event
     */
    public function dispatch($event, array $data = []): Event
    {
        // 如果是字符串，创建事件对象
        if (is_string($event)) {
            $event = new BaseEvent($event, $data);
        }

        $eventName = $event->getName();

        // 触发事件监听器
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $item) {
                $listener = $item['listener'];

                // 如果监听器是闭包，直接调用
                if ($listener instanceof Closure) {
                    $listener($event);
                } elseif (is_string($listener) && class_exists($listener)) {
                    // 如果监听器是类名，实例化后调用handle方法
                    $listenerInstance = new $listener();
                    if (method_exists($listenerInstance, 'handle')) {
                        $listenerInstance->handle($event);
                    }
                }

                // 检查事件是否已停止传播
                if ($event->isPropagationStopped()) {
                    break;
                }
            }
        }

        return $event;
    }

    /**
     * 注册多个事件监听器
     * 
     * @param array $listeners 监听器数组
     * @return self
     */
    public function listenMany(array $listeners): self
    {
        foreach ($listeners as $event => $listener) {
            $this->listen($event, $listener);
        }

        return $this;
    }

    /**
     * 移除事件监听器
     * 
     * @param string $event 事件名称
     * @return self
     */
    public function forget(string $event): self
    {
        unset($this->listeners[$event]);
        return $this;
    }

    /**
     * 移除所有事件监听器
     * 
     * @return self
     */
    public function forgetAll(): self
    {
        $this->listeners = [];
        return $this;
    }

    /**
     * 获取事件监听器
     * 
     * @param string $event 事件名称
     * @return array
     */
    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * 检查事件是否有监听器
     * 
     * @param string $event 事件名称
     * @return bool
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && !empty($this->listeners[$event]);
    }
}
