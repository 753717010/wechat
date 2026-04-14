<?php
/**
 * 基础事件类
 * 实现事件接口的基本方法
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\event;

class BaseEvent implements Event
{
    /**
     * 事件名称
     * @var string
     */
    protected $name;

    /**
     * 事件数据
     * @var array
     */
    protected $data = [];

    /**
     * 是否停止传播
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * 构造函数
     * 
     * @param string $name 事件名称
     * @param array $data 事件数据
     */
    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * 获取事件名称
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取事件数据
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 设置事件数据
     * 
     * @param array $data
     * @return Event
     */
    public function setData(array $data): Event
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 停止事件传播
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * 检查事件是否已停止传播
     * 
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
