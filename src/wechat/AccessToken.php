<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/7/5
 * Time: 7:23 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\wechat;

use Cje\Wechat\bases\Requester;
use Cje\Wechat\bases\Response;
use Cje\Wechat\exception\WechatException;
use Cje\Wechat\helper\CacheHelper;
use Doctrine\Common\Cache\Cache;

class AccessToken
{
    protected $appId;
    protected $appSecret;
    protected $requester;
    protected $response;
    /**
     * @var Cache $cache
     */
    private $cache;

    protected $accessToken;
    protected $accessTokenOk;

    public function __construct(
        $appId,
        $appSecret,
        $cache = null,
        $requester = null,
        $response = null
    ) {
        $this->appSecret = $appSecret;
        $this->appId = $appId;
        $this->cache = $cache ?? CacheHelper::create(CacheHelper::defaultConfig());
        $this->requester = $requester === null ? new Requester() : $requester;
        $this->response = $response === null ? new Response() : $response;
    }

    /**
     * 获取access_token的参数
     * @return array
     */
    public function accessTokenParams()
    {
        return [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
        ];
    }

    public function key()
    {
        return 'ACCESS_TOKEN_OF_' . md5(json_encode($this->accessTokenParams()));
    }

    public function checkKey()
    {
        return 'CHECK_ACCESS_TOKEN_OF_' . md5(json_encode($this->accessTokenParams()));
    }
    /**
     * 获取accessToken
     * @param $refresh
     * @return mixed
     * @throws WechatException
     */
    public function getAccessToken($refresh = false)
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        if (!$refresh) {
            $this->accessToken = $this->cache->fetch($this->key());
            if ($this->accessToken && $this->checkAccessToken()) {
                return $this->accessToken;
            }
        }
        $this->setAccessToken();
        return $this->accessToken;
    }

    /**
     * 设置accessToken
     * @return mixed
     * @throws WechatException
     */
    public function setAccessToken()
    {
        if (!$this->appId) {
            throw  new WechatException('appId 不能为空。');
        }
        if (!$this->appSecret) {
            throw  new WechatException('appSecret 不能为空。');
        }
        $cacheKey = $this->key();
        $cacheKeyOk = $this->checkKey();
        $raw = $this->requester->get('cgi-bin/token', array_merge([
            'grant_type' => 'client_credential'
        ], $this->accessTokenParams()));
        $response = $this->response->parse($raw);
        if (isset($response->parseData['errcode']) && $response->parseData['errcode'] !== 0) {
            throw new WechatException($response->parseData['errmsg'], $response);
        }
        $this->accessToken = $response->parseData['access_token'];
        $this->accessTokenOk = true;
        $this->cache->save($cacheKey, $this->accessToken, 7000);
        $this->cache->save($cacheKeyOk, true, 180);
        return $this->accessToken;
    }

    /**
     * 检查accessToken有效性，若有效，则缓存3分钟
     * @return bool|mixed
     */
    private function checkAccessToken()
    {
        if (!$this->accessToken) {
            return false;
        }
        if ($this->accessTokenOk) {
            return $this->accessTokenOk;
        }
        $cacheKey = $this->checkKey();
        $this->accessTokenOk = $this->cache->fetch($cacheKey);
        if ($this->accessTokenOk) {
            return $this->accessTokenOk;
        }
        $this->accessTokenOk = false;
        try {
            $raw = $this->requester->get('cgi-bin/getcallbackip', ['access_token' => $this->accessToken]);
            $response = $this->response->parse($raw);
            if ($response->isSuccess()) {
                $this->accessTokenOk = true;
                $this->cache->save($cacheKey, true, 180);
            }
        } catch (\Exception $e) {
        }
        return $this->accessTokenOk;
    }
}