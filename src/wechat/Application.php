<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/7/6
 * Time: 9:06 AM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\wechat;

use Cje\Wechat\bases\Requester;
use Cje\Wechat\bases\Response;
use Cje\Wechat\helper\CacheHelper;
use Doctrine\Common\Cache\Cache;

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

    /**
     * @var Cache
     */
    public $cacheObject;

    public $accessTokenClass;

    public function __construct($wechat, $cache = null, $requester = null, $response = null)
    {
        $this->wechat = $wechat;
        $this->cacheObject = $cache === null ? CacheHelper::create(CacheHelper::defaultConfig()) : $cache;
        $this->requester = $requester === null ? new Requester() : $requester;
        $this->response = $response === null ? new Response() : $response;
    }

    public function getAccessTokenClass()
    {
        if (!$this->accessTokenClass) {
            $this->accessTokenClass = new AccessToken(
                $this->wechat->getAppId(),
                $this->wechat->getSecret(),
                $this->cacheObject,
                $this->requester,
                $this->response
            );
        }
        return $this->accessTokenClass;
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
        $accessToken && $params['access_token'] = $params['access_token'] ?? $this->getAccessTokenClass()->getAccessToken();
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
    public function post($api, $data, $params = [], $accessToken = true)
    {
        $accessToken && $params['access_token'] = $params['access_token'] ?? $this->getAccessTokenClass()->getAccessToken();
        $raw = $this->requester->post($api, $data, $params);
        return $this->response->parse($raw);
    }

    /**
     * post请求不做响应解析
     * @param $api
     * @param $data
     * @param $params
     * @return bool|string
     * @throws \Cje\Wechat\exception\WechatCurlException
     */
    public function execute($api, $data, $params = [], $accessToken = true)
    {
        $accessToken && $params['access_token'] = $params['access_token'] ?? $this->getAccessTokenClass()->getAccessToken();
        return $this->requester->post($api, $data, $params);
    }

    public function jsCodeToSession($code)
    {
        return $this->get('sns/jscode2session', [
            'appid' => $this->wechat->getAppId(),
            'secret' => $this->wechat->getSecret(),
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ], false);
    }

    public function decrypt($sessionKey, $iv, $encryptedData)
    {
        $result = openssl_decrypt(
            base64_decode($encryptedData),
            "AES-128-CBC",
            base64_decode($sessionKey),
            1,
            base64_decode($iv)
        );
        return json_decode($result, true);
    }
}