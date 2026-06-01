<?php
/**
 * 测试事件监听器
 * 用于测试事件系统的功能
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\event\listener;

use Cje\Wechat\event\Event;

class TestListener
{
    /**
     * 处理事件
     * 
     * @param Event $event 事件对象
     */
    public function handle(Event $event)
    {
        echo "<pre>" . PHP_EOL;
        echo "[TestListener] 接收到事件: " . $event->getName() . PHP_EOL;
        echo "[TestListener] 事件数据: " . print_r($event->getData(), true) . PHP_EOL;
        echo "</pre>" . PHP_EOL;
        
        // 修改事件数据
        $data = $event->getData();
        $data['processed'] = true;
        $event->setData($data);
    }
}