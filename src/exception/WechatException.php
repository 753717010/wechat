<?php
/**
 * 微信异常基类
 * 处理微信API调用中的各种异常
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\exception;

class WechatException extends \Exception
{
    /**
     * 响应数据
     * @var array|null
     */
    public $response;

    /**
     * 错误码
     * @var int
     */
    public $errorCode;

    /**
     * 构造函数
     * 
     * @param string $message 错误信息
     * @param array|null $response 响应数据
     * @param int $code 错误码
     * @param \Exception|null $previous 前一个异常
     */
    public function __construct(string $message, $response = null, int $code = 0, \Exception $previous = null)
    {
        $this->response = $response;
        $this->errorCode = $code;
        
        // 从响应数据中提取错误码
        if (is_array($response) && isset($response['errcode'])) {
            $this->errorCode = $response['errcode'];
        }
        
        parent::__construct($message, $this->errorCode, $previous);
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->message;
    }

    /**
     * 获取错误码
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 获取响应数据
     * @return array|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 转换为数组
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->errorCode,
            'message' => $this->message,
            'response' => $this->response,
        ];
    }
}
