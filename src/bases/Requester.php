<?php
/**
 * 基础请求类
 * 处理HTTP请求，支持多种请求格式和请求重试
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatCurlException;
use Cje\Wechat\helper\CurlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class Requester
{
    /**
     * HTTP客户端
     * @var Client
     */
    protected $client;

    /**
     * 请求上下文信息
     * @var array
     */
    private $requestContext = [];

    /**
     * Guzzle配置
     * @var array
     */
    private $guzzleConfig = [
        'base_uri' => 'https://api.weixin.qq.com',
        'verify' => false,
        'timeout' => 30,
        'connect_timeout' => 10,
        'http_errors' => false, // 不自动抛出HTTP错误
    ];

    /**
     * 请求选项
     * @var array
     */
    private $requestOptions = [];

    /**
     * 最大重试次数
     * @var int
     */
    private $maxRetries = 3;

    /**
     * 构造函数
     * 
     * @param array $options Guzzle配置选项
     */
    public function __construct($options = [])
    {
        $this->guzzleConfig = array_merge($this->guzzleConfig, $options);
        $this->initClient();
    }

    /**
     * 初始化HTTP客户端
     * 添加请求重试中间件
     */
    private function initClient()
    {
        $stack = HandlerStack::create();
        
        // 添加重试中间件
        $stack->push(Middleware::retry(function ($retries, $request, $response, $exception) {
            // 只对网络错误和500错误进行重试
            return $retries < $this->maxRetries && (
                $exception instanceof \GuzzleHttp\Exception\ConnectException ||
                ($response && $response->getStatusCode() >= 500)
            );
        }, function ($retries) {
            // 指数退避策略
            return 1000 * pow(2, $retries);
        }));
        
        $this->guzzleConfig['handler'] = $stack;
        $this->client = new Client($this->guzzleConfig);
    }

    /**
     * 重置请求选项
     */
    private function resetRequestOptions()
    {
        $this->requestOptions = [];
    }

    /**
     * 设置超时时间
     * @param int $seconds
     * @return self
     */
    public function setTimeout(int $seconds): self
    {
        $this->guzzleConfig['timeout'] = $seconds;
        $this->guzzleConfig['connect_timeout'] = $seconds;
        
        return $this;
    }

    /**
     * 设置请求头
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->requestOptions['headers'] = $headers;
        
        return $this;
    }

    /**
     * 设置请求上下文（用于错误追踪）
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setContext(string $key, $value): self
    {
        $this->requestContext[$key] = $value;
        
        return $this;
    }

    /**
     * 设置Guzzle选项
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->guzzleConfig = array_merge($this->guzzleConfig, $options);
        // 重新初始化客户端
        $this->initClient();
        
        return $this;
    }

    /**
     * 设置认证
     * @param string $username
     * @param string $password
     * @return self
     */
    public function setAuth(string $username, string $password): self
    {
        $this->requestOptions['auth'] = [$username, $password];
        
        return $this;
    }

    /**
     * 设置查询参数
     * @param array $params
     * @return self
     */
    public function setQueryParams(array $params): self
    {
        $this->requestOptions['query'] = $params;
        
        return $this;
    }

    /**
     * 拼接请求地址
     * @param string $api
     * @param array $params
     * @return string
     */
    public function getUrl(string $api, array $params = []): string
    {
        return CurlHelper::appendParams(CurlHelper::getUrl($this->guzzleConfig['base_uri'], $api), $params);
    }

    /**
     * POST请求 - Body格式
     * @param string $uri
     * @param mixed $data
     * @param array $params
     * @param string $contentType
     * @return Response
     */
    public function postByBody(string $uri, $data = [], array $params = [], string $contentType = 'application/json'): Response
    {
        $url = $this->getUrl($uri, $params);
        $this->requestOptions['body'] = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->requestOptions['headers']['Content-Type'] = $contentType;
        
        return $this->request('POST', $url);
    }

    /**
     * POST请求 - JSON格式
     * @param string $uri
     * @param mixed $data
     * @param array $params
     * @return Response
     */
    public function postByJson(string $uri, $data = [], array $params = []): Response
    {
        $url = $this->getUrl($uri, $params);
        $this->requestOptions['json'] = $data;
        
        return $this->request('POST', $url);
    }

    /**
     * POST请求 - Multipart格式
     * @param string $uri
     * @param array $data
     * @param array $params
     * @return Response
     */
    public function postByMultipart(string $uri, array $data = [], array $params = []): Response
    {
        $url = $this->getUrl($uri, $params);
        $this->requestOptions['multipart'] = $data;
        
        return $this->request('POST', $url);
    }

    /**
     * POST请求 - Form格式
     * @param string $uri
     * @param array $data
     * @param array $params
     * @return Response
     */
    public function postByForm(string $uri, array $data = [], array $params = []): Response
    {
        $url = $this->getUrl($uri, $params);
        $this->requestOptions['form_params'] = $data;
        return $this->request('POST', $url);
    }

    /**
     * GET请求
     * @param string $api
     * @param array $params
     * @return Response
     */
    public function get(string $api, array $params = []): Response
    {
        $url = $this->getUrl($api, $params);
        return $this->request('GET', $url);
    }

    /**
     * 执行请求
     * @param string $method
     * @param string $url
     * @return Response
     * @throws WechatCurlException
     */
    private function request(string $method, string $url): Response
    {
        $this->requestContext['request_url'] = $url;
        $this->requestContext['request_method'] = $method;
        $this->requestContext['request_time'] = date('Y-m-d H:i:s');
        $this->requestContext['request_options'] = $this->requestOptions;
        
        try {
            $response = new Response($this->client->request($method, $url, $this->requestOptions), $this->requestContext);
        } catch (\Exception $e) {
            $errorMsg = sprintf('网络连接失败: %s', $e->getMessage());
            throw new WechatCurlException($errorMsg, $e->getCode(), $e, $this->requestContext);
        } finally {
            // 重置请求选项，避免参数污染
            $this->resetRequestOptions();
        }
        
        return $response;
    }
}
