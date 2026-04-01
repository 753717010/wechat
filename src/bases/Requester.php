<?php
/**
 * Author: 风哀伤
 * 基础请求类
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatCurlException;
use Cje\Wechat\helper\CurlHelper;
use GuzzleHttp\Client;

class Requester
{
    /**
     * @var Client HTTP客户端
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

    public function __construct($options = [])
    {
        $this->guzzleConfig = array_merge($this->guzzleConfig, $options);
    }

    /**
     * 设置超时时间
     * @param int $seconds
     * @return self
     */
    public function setTimeout($seconds)
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
    public function setHeaders(array $headers)
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
    public function setContext($key, $value)
    {
        $this->requestContext[$key] = $value;
        
        return $this;
    }

    /**
     * 设置Guzzle选项
     * @param array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        $this->guzzleConfig = array_merge($this->guzzleConfig, $options);
        
        return $this;
    }

    /**
     * 设置认证
     * @param string $username
     * @param string $password
     * @return self
     */
    public function setAuth($username, $password)
    {
        $this->requestOptions['auth'] = [$username, $password];
        
        return $this;
    }

    /**
     * 设置查询参数
     * @param array $params
     * @return self
     */
    public function setQueryParams(array $params)
    {
        $this->requestOptions['query'] = $params;
        
        return $this;
    }

    /**
     * 拼接请求地址
     * @param $api
     * @param $params
     * @return mixed|string
     */
    public function getUrl($api, $params = [])
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
    public function postByBody($uri, $data = [], $params = [], $contentType = 'application/json')
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
    public function postByJson($uri, $data = [], $params = [], $contentType = 'application/json')
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
    public function postByMultipart($uri, $data = [], $params = [])
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
    public function postByForm($uri, $data = [], $params = [])
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
    public function get($api, $params = [])
    {
        $url = $this->getUrl($api, $params);
        return $this->request('GET', $url);
    }

    /**
     * 执行请求
     * @param string $method
     * @param string $url
     * @return Response
     */
    private function request($method, $url)
    {
        $this->requestContext['request_url'] = $url;
        $this->requestContext['request_method'] = $method;
        $this->requestContext['request_time'] = date('Y-m-d H:i:s');
        $this->requestContext['request_options'] = $this->requestOptions;
        
        try {
            if (!$this->client) {
                $this->client = new Client($this->guzzleConfig);
            }
            
            $response = new Response($this->client->request($method, $url, $this->requestOptions), $this->requestContext);
        } catch (\Exception $e) {
            throw new WechatCurlException('网络连接失败', $e->getCode(), $e);
        }
        
        return $response;
    }
}
