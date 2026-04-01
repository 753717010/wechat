<?php
/**
 * Author: 风哀伤
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatException;

class Response
{
    /**
     * 原始响应
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    private $rawResponse;

    /**
     * 响应内容流
     * @var \Psr\Http\Message\StreamInterface|null
     */
    private $responseStream;

    /**
     * 缓存响应内容（避免多次读取流）
     * @var string|null
     */
    private $cachedResponseContent;

    /**
     * 请求上下文（用于错误追踪）
     * @var array
     */
    private $requestContext = [];

    /**
     * 错误对象
     * @var Error|null
     */
    private $error;

    public function __construct($raw = null, $requestContext = [])
    {
        $this->rawResponse = $raw;
        $this->responseStream = $this->rawResponse->getBody();
        $this->error = new Error($requestContext);
        $this->requestContext = $requestContext;
        $this->checkResponse();
    }

    public function checkResponse()
    {
        // 检查HTTP状态码
        $statusCode = $this->getStatusCode();
        
        if ($statusCode >= 400 && $statusCode < 500) {
            $this->error->addHttpError("HTTP客户端错误: {$statusCode}", $statusCode);
        } elseif ($statusCode >= 500) {
            $this->error->addHttpError("HTTP服务器错误: {$statusCode}", $statusCode);
        } elseif ($statusCode < 200 || $statusCode >= 300) {
            $this->error->addHttpError("HTTP非成功状态码: {$statusCode}", $statusCode);
        }
    }

    /**
     * 获取请求上下文
     * @return array
     */
    public function getRequestContext()
    {
        return $this->requestContext;
    }
    
    /**
     * 获取原始响应对象
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * 获取响应内容流
     * @return \Psr\Http\Message\StreamInterface|null
     */
    public function getResponseStream()
    {
        return $this->responseStream;
    }

    /**
     * 获取响应内容
     * @return string|null
     */
    public function getContent()
    {
        // 如果已经缓存了内容，直接返回
        if ($this->cachedResponseContent !== null) {
            return $this->cachedResponseContent;
        }
        
        if (!$this->responseStream) {
            return null;
        }
        
        try {
            // 保存当前位置（如果是可查找的流）
            $currentPosition = 0;
            $isSeekable = $this->responseStream->isSeekable();
            if ($isSeekable) {
                $currentPosition = $this->responseStream->tell();
                // 重置到开始位置
                $this->responseStream->rewind();
            }
            
            // 读取所有内容
            $this->cachedResponseContent = $this->responseStream->getContents();
            
            // 恢复原来的位置（如果是可查找的流）
            if ($isSeekable) {
                $this->responseStream->seek($currentPosition);
            }
            
            return $this->cachedResponseContent;
        } catch (\Exception $e) {
            $this->error->addValidationError('读取响应内容失败', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * 获取JSON解析后的数据
     * @param bool $asArray 是否返回数组
     * @param callable|null $validator 数据验证器
     * @return array|object|null
     */
    public function getJson($asArray = true, $validator = null)
    {
        if (!$this->isSuccess()) {
            throw new WechatException($this->getFirstError(), $this);
        }
        $content = $this->getContent();
        
        if (!$content) {
            $this->error->addValidationError('响应内容为空');
            return null;
        }
        
        try {
            $data = json_decode($content, $asArray);
            
            // 数据验证
            if ($validator && is_callable($validator)) {
                $validationResult = call_user_func($validator, $data);
                if ($validationResult !== true) {
                    $this->error->addValidationError('数据验证失败', is_string($validationResult) ? [$validationResult] : $validationResult);
                    return null;
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            $this->error->addValidationError('JSON解析失败', [
                'error' => $e->getMessage(),
                'content_preview' => substr($content, 0, 200),
            ]);
            return null;
        }
    }

    /**
     * 获取响应头
     * @param string|null $key 指定header key
     * @return array|string|null
     */
    public function getHeaders($key = null)
    {
        if (!$this->rawResponse) {
            return $key ? null : [];
        }
        
        $headers = $this->rawResponse->getHeaders();
        
        if ($key) {
            return $headers[$key] ?? null;
        }
        
        return $headers;
    }

    /**
     * 获取HTTP状态码
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->rawResponse ? $this->rawResponse->getStatusCode() : null;
    }

    /**
     * 检查请求是否成功（状态码2xx且无错误）
     * @return bool
     */
    public function isSuccess()
    {
        $statusCode = $this->getStatusCode();
        $hasHttpSuccess = $statusCode && $statusCode >= 200 && $statusCode < 300;
        $hasNoErrors = empty($this->getErrors());
        
        return $hasHttpSuccess && $hasNoErrors;
    }

    /**
     * 检查是否有错误
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->getErrors());
    }

    /**
     * 获取所有错误
     * @return array
     */
    public function getErrors()
    {
        return $this->error->getErrors();
    }

    /**
     * 获取第一个错误
     * @return array|null
     */
    public function getFirstError()
    {
        return !empty($this->getErrors()) ? $this->getErrors()[0] : null;
    }

    /**
     * 获取最后一个错误
     * @return array|null
     */
    public function getLastError()
    {
        return !empty($this->getErrors()) ? end($this->getErrors()) : null;
    }

    /**
     * 获取错误消息集合
     * @return array
     */
    public function getErrorMessages()
    {
        return array_map(function($error) {
            return $error['message'];
        }, $this->getErrors());
    }

    /**
     * 清空错误
     * @return self
     */
    public function clearErrors()
    {
        $this->error->clearErrors();
        return $this;
    }
}
