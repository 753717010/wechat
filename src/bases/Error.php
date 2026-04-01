<?php
/**
 * Author: 风哀伤
 */

namespace Cje\Wechat\bases;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;

class Error
{
    private $errors = [];
    private $requestContext = [];

    // 错误等级常量
    public const ERROR_LEVEL_NONE = 0;
    public const ERROR_LEVEL_CLIENT = 1;     // 4xx 错误
    public const ERROR_LEVEL_SERVER = 2;     // 5xx 错误
    public const ERROR_LEVEL_NETWORK = 3;    // 网络连接错误
    public const ERROR_LEVEL_TIMEOUT = 4;    // 超时错误
    public const ERROR_LEVEL_VALIDATION = 5; // 数据验证错误
    public const ERROR_LEVEL_UNKNOWN = 99;   // 未知错误

    // 错误类型常量
    public const ERROR_TYPE_HTTP = 'http';
    public const ERROR_TYPE_NETWORK = 'network';
    public const ERROR_TYPE_VALIDATION = 'validation';
    public const ERROR_TYPE_BUSINESS = 'business';
    public const ERROR_TYPE_UNKNOWN = 'unknown';

    public function __construct($requestContext = [])
    {
        $this->requestContext = $requestContext;
    }

    /**
     * 添加HTTP错误
     * @param string $message
     * @param int $statusCode
     * @param Exception|null $exception
     */
    public function addHttpError($message, $statusCode, $exception = null)
    {
        $errorLevel = $statusCode >= 500 ? self::ERROR_LEVEL_SERVER : self::ERROR_LEVEL_CLIENT;
        
        $error = [
            'type' => self::ERROR_TYPE_HTTP,
            'level' => $errorLevel,
            'request_context' => $this->requestContext,
            'message' => $message,
            'timestamp' => time(),
        ];
        
        if ($exception) {
            $error['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            
            // 尝试从异常中获取响应内容
            if ($exception instanceof RequestException && $exception->hasResponse()) {
                $response = $exception->getResponse();
                try {
                    $error['response_preview'] = substr($response->getBody()->getContents(), 0, 500);
                    // 重置流指针
                    $response->getBody()->rewind();
                } catch (\Exception $e) {
                    // 忽略获取响应内容的异常
                }
            }
        }
        
        $this->errors[] = $error;
    }

    /**
     * 添加网络错误
     * @param string $message
     * @param Exception $exception
     */
    public function addNetworkError($message, $exception)
    {
        $errorLevel = self::ERROR_LEVEL_NETWORK;
        
        // 检查是否超时
        if (strpos($exception->getMessage(), 'timeout') !== false || 
            strpos($exception->getMessage(), 'timed out') !== false) {
            $errorLevel = self::ERROR_LEVEL_TIMEOUT;
            $message = '请求超时';
        }
        
        $error = [
            'type' => self::ERROR_TYPE_NETWORK,
            'level' => $errorLevel,
            'request_context' => $this->requestContext,
            'message' => $message,
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
            'timestamp' => time(),
        ];
        
        $this->errors[] = $error;
    }

    /**
     * 添加数据验证错误
     * @param string $message
     * @param array $details
     */
    public function addValidationError($message, $details = [])
    {
        $error = [
            'type' => self::ERROR_TYPE_VALIDATION,
            'level' => self::ERROR_LEVEL_VALIDATION,
            'request_context' => $this->requestContext,
            'message' => $message,
            'details' => $details,
            'timestamp' => time(),
        ];
        
        $this->errors[] = $error;
    }

    /**
     * 添加业务错误
     * @param string $message
     * @param mixed $data
     */
    public function addBusinessError($message, $data = null)
    {
        $error = [
            'type' => self::ERROR_TYPE_BUSINESS,
            'level' => self::ERROR_LEVEL_CLIENT,
            'request_context' => $this->requestContext,
            'message' => $message,
            'business_data' => $data,
            'timestamp' => time(),
        ];
        
        $this->errors[] = $error;
    }

    /**
     * 添加未知错误
     * @param string $message
     * @param Exception $exception
     */
    public function addUnknownError($message, $exception)
    {
        $error = [
            'type' => self::ERROR_TYPE_UNKNOWN,
            'level' => self::ERROR_LEVEL_UNKNOWN,
            'request_context' => $this->requestContext,
            'message' => $message,
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
            'timestamp' => time(),
        ];
        
        $this->errors[] = $error;
    }
    
    /**
     * 获取所有错误
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function clearErrors()
    {
        $this->errors = [];
    }
}
