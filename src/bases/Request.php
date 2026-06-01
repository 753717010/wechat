<?php
/**
 * 请求基类
 * 定义API请求的基本结构和方法
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\bases;

abstract class Request extends BaseClass
{
    /**
     * 是否需要访问令牌
     * @var bool
     */
    protected $needAccessToken = true;
    
    /**
     * API路径
     * @var string
     */
    protected $api;

    /**
     * 获取是否需要访问令牌
     * @return bool
     */
    public function getNeedAccessToken(): bool
    {
        return $this->needAccessToken;
    }

    /**
     * 获取API路径
     * @return string
     */
    public function getApi(): string
    {
        return $this->api;
    }

    /**
     * 构建请求参数
     * @return array
     */
    abstract public function build(): array;

    abstract public function getMethod(): string;
}