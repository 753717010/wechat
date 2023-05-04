<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:13 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat;

use Cje\Wechat\bases\Requester;
use Cje\Wechat\bases\Response;
use Cje\Wechat\wechat\Wechat;

class Application
{
    /**
     * 微信配置
     * @var Wechat
     */
    public $wechat;

    /**
     * 请求发送器
     * @var Requester
     */
    public $requester;
    /**
     * 响应解析器
     * @var Response
     */
    public $response;

    public function __construct($wechat, $requester = null, $response = null)
    {
        $this->wechat = $wechat;
        $this->requester = $requester === null ? new Requester() : $requester;
        $this->response = $response === null ? new Response() : $response;
    }

    public function getAccessToken()
    {
        return $this->wechat->getAccessToken();
    }

    /**
     * get请求
     * @param string $api 请求地址
     * @param array $params query参数
     * @param boolean $accessToken 是否需要access_token参数
     * @return Response
     */
    public function get($api, $params = [], $accessToken = true)
    {
        $accessToken && $params['access_token'] = $params['access_token'] ?? $this->getAccessToken();
        $raw = $this->requester->get($api, $params);
        return $this->response->parse($raw);
    }

    /**
     * post请求
     * @param string $api 请求地址
     * @param array $data post请求参数
     * @param array $params query参数
     * @return Response
     */
    public function post($api, $data, $params = [])
    {
        $params['access_token'] = $params['access_token'] ?? $this->getAccessToken();
        $raw = $this->requester->post($api, $data, $params);
        return $this->response->parse($raw);
    }

    /**
     * post请求不做响应解析
     * @param $api
     * @param $data
     * @param $params
     * @return bool|string
     * @throws exception\WechatCurlException
     */
    public function execute($api, $data, $params = [])
    {
        $params['access_token'] = $params['access_token'] ?? $this->getAccessToken();
        return $this->requester->post($api, $data, $params);
    }
}
