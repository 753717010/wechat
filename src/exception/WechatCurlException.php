<?php
/**
 * 微信网络异常类
 * 处理微信API调用中的网络异常
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\exception;

class WechatCurlException extends WechatException
{
    /**
     * 请求上下文
     * @var array
     */
    public $requestContext;

    /**
     * 构造函数
     * 
     * @param string $message 错误信息
     * @param int $code 错误码
     * @param \Exception|null $previous 前一个异常
     * @param array $requestContext 请求上下文
     */
    public function __construct(string $message, int $code = 0, \Exception $previous = null, array $requestContext = [])
    {
        $this->requestContext = $requestContext;
        parent::__construct($message, null, $code, $previous);
    }

    /**
     * 获取请求上下文
     * @return array
     */
    public function getRequestContext(): array
    {
        return $this->requestContext;
    }

    /**
     * 转换为数组
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'request_context' => $this->requestContext,
        ]);
    }
}