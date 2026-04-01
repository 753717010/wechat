<?php
/**
 * Author: 风哀伤
 * 稳定accessToken：https://developers.weixin.qq.com/doc/subscription/api/base/api_getaccesstoken.html
 * 普通accessToken：https://developers.weixin.qq.com/doc/subscription/api/base/api_getstableaccesstoken.html
 */

namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Requester;
use Cje\Wechat\exception\WechatException;
use Cje\Wechat\helper\CacheHelper;
use Doctrine\Common\Cache\Cache;

class AccessToken
{
    protected $appId;
    protected $appSecret;
    protected $stable = false;
    /**
     * @var Cache $cache
     */
    private $cache;

    protected $accessToken;
    protected $accessTokenOk;

    const PREFIX_CACHE_KEY = 'OFFICIAL_ACCOUNT_ACCESS_TOKEN_OF_';

    public function __construct(
        $appId,
        $appSecret,
        $stable = false,
        $cache = null
    ) {
        $this->appSecret = $appSecret;
        $this->appId = $appId;
        $this->stable = $stable;
        $this->cache = $cache ?? CacheHelper::create(CacheHelper::defaultConfig());
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
            'grant_type' => 'client_credential',
        ];
    }

    public function key()
    {
        $params = array_merge($this->accessTokenParams(), [
            'stable' => $this->stable,
        ]);
        return self::PREFIX_CACHE_KEY . md5(json_encode($params));
    }

    public function checkKey()
    {
        return 'CHECK_' . $this->key();
    }

    public function getToken($refresh = false)
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
     * 获取accessToken
     * @return mixed
     * @throws WechatException
     */
    public function getAccessToken()
    {
        $cacheKey = $this->key();
        $cacheKeyOk = $this->checkKey();
        $response = (new Requester())->get('cgi-bin/token', $this->accessTokenParams());
        
        $data = $response->getJson();
        if (isset($data['errcode']) && $data['errcode'] !== 0) {
            throw new WechatException($data['errmsg'], $data);
        }
        $this->accessToken = $data['access_token'];
        $this->accessTokenOk = true;
        $this->cache->save($cacheKey, $this->accessToken, 7000);
        $this->cache->save($cacheKeyOk, true, 180);
        return $this->accessToken;
    }

    /**
     * 获取稳定accessToken
     * @return mixed
     * @throws WechatException
     */
    public function getStableAccessToken()
    {
        $cacheKey = $this->key();
        $cacheKeyOk = $this->checkKey();
        $response = (new Requester())->postByJson('cgi-bin/stable_token', array_merge($this->accessTokenParams(), [
            'force_refresh' => true,
        ]));
        $data = $response->getJson();
        if (isset($data['errcode']) && $data['errcode'] !== 0) {
            throw new WechatException($data['errmsg'], $data);
        }
        $this->accessToken = $data['access_token'];
        $this->accessTokenOk = true;
        $this->cache->save($cacheKey, $this->accessToken, 7000);
        $this->cache->save($cacheKeyOk, true, 180);
        return $this->accessToken;
    }

    /**
     * 设置accessToken
     * @return mixed
     * @throws WechatException
     */
    public function setAccessToken()
    {
        return $this->stable ? $this->getStableAccessToken() : $this->getAccessToken();
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
            $response = (new Requester())->get('cgi-bin/getcallbackip', [
                'access_token' => $this->accessToken
            ]);
            $data = $response->getJson();
            if (isset($data['errcode']) && $data['errcode'] !== 0) {
                throw new WechatException($data['errmsg'], $data);
            }
            $this->accessTokenOk = true;
            $this->cache->save($cacheKey, true, 180);
        } catch (\Exception $e) {
        }
        return $this->accessTokenOk;
    }
}