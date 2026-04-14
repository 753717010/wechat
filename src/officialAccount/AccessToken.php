<?php
/**
 * 访问令牌管理类
 * 处理微信访问令牌的获取、缓存和验证
 * 
 * @author 风哀伤
 * @link 稳定accessToken：https://developers.weixin.qq.com/doc/subscription/api/base/api_getaccesstoken.html
 * @link 普通accessToken：https://developers.weixin.qq.com/doc/subscription/api/base/api_getstableaccesstoken.html
 */

namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Requester;
use Cje\Wechat\exception\InvalidArgumentException;
use Cje\Wechat\exception\WechatException;
use Cje\Wechat\helper\CacheHelper;
use Doctrine\Common\Cache\Cache;

class AccessToken
{
    /**
     * 应用ID
     * @var string
     */
    protected $appId;
    
    /**
     * 应用密钥
     * @var string
     */
    protected $appSecret;
    
    /**
     * 是否使用稳定版access_token
     * @var bool
     */
    protected $stable = false;
    
    /**
     * 缓存实例
     * @var Cache
     */
    private $cache;

    /**
     * 访问令牌
     * @var string|null
     */
    protected $accessToken;
    
    /**
     * 访问令牌是否有效
     * @var bool|null
     */
    protected $accessTokenOk;

    /**
     * 缓存键前缀
     */
    const PREFIX_CACHE_KEY = 'OFFICIAL_ACCOUNT_ACCESS_TOKEN_OF_';

    /**
     * 构造函数
     * 
     * @param string $appId 应用ID
     * @param string $appSecret 应用密钥
     * @param bool $stable 是否使用稳定版access_token
     * @param Cache|null $cache 缓存实例
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $appId,
        string $appSecret,
        bool $stable = false,
        ?Cache $cache = null
    ) {
        // 验证必要参数
        if (empty($appId)) {
            throw new InvalidArgumentException('appId不能为空');
        }
        if (empty($appSecret)) {
            throw new InvalidArgumentException('appSecret不能为空');
        }
        
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->stable = $stable;
        $this->cache = $cache ?? CacheHelper::create(CacheHelper::defaultConfig());
    }

    /**
     * 获取access_token的参数
     * @return array
     */
    public function accessTokenParams(): array
    {
        return [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential',
        ];
    }

    /**
     * 生成缓存键
     * @return string
     */
    public function key(): string
    {
        $params = array_merge($this->accessTokenParams(), [
            'stable' => $this->stable,
        ]);
        return self::PREFIX_CACHE_KEY . md5(json_encode($params));
    }

    /**
     * 生成验证缓存键
     * @return string
     */
    public function checkKey(): string
    {
        return 'CHECK_' . $this->key();
    }

    /**
     * 获取访问令牌
     * @param bool $refresh 是否强制刷新
     * @return string
     * @throws WechatException
     */
    public function getToken(bool $refresh = false): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        if (!$refresh) {
            $cachedToken = $this->cache->fetch($this->key());
            if ($cachedToken && $this->checkAccessToken()) {
                $this->accessToken = $cachedToken;
                return $this->accessToken;
            }
        }
        $this->setAccessToken();
        return $this->accessToken;
    }

    /**
     * 获取普通accessToken
     * @return string
     * @throws WechatException
     */
    public function getAccessToken(): string
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
     * @return string
     * @throws WechatException
     */
    public function getStableAccessToken(): string
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
     * @return string
     * @throws WechatException
     */
    public function setAccessToken(): string
    {
        return $this->stable ? $this->getStableAccessToken() : $this->getAccessToken();
    }

    /**
     * 检查accessToken有效性，若有效，则缓存3分钟
     * @return bool
     */
    private function checkAccessToken(): bool
    {
        if (!$this->accessToken) {
            return false;
        }
        
        if ($this->accessTokenOk) {
            return $this->accessTokenOk;
        }
        
        $cacheKey = $this->checkKey();
        $this->accessTokenOk = (bool)$this->cache->fetch($cacheKey);
        
        if ($this->accessTokenOk) {
            return $this->accessTokenOk;
        }
        
        $this->accessTokenOk = false;
        
        // 为避免其他地方重新调用了access_token导致缓存中的access_token失效，
        // 我们在检查access_token有效性时，先调用一次getcallbackip接口，
        // 如果调用成功，则说明access_token有效，否则说明access_token已过期
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
            // 忽略异常，返回false
        }
        
        return $this->accessTokenOk;
    }
}