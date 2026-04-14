<?php
/**
 * 事件接口
 * 定义事件的基本方法
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\event;

interface Event
{
    /**
     * 获取事件名称
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * 获取事件数据
     * 
     * @return array
     */
    public function getData(): array;

    /**
     * 设置事件数据
     * 
     * @param array $data
     * @return Event
     */
    public function setData(array $data): Event;

    /**
     * 停止事件传播
     */
    public function stopPropagation();

    /**
     * 检查事件是否已停止传播
     * 
     * @return bool
     */
    public function isPropagationStopped(): bool;
}
