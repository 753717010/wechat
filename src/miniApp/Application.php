<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/7/6
 * Time: 9:06 AM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\miniApp;

use Cje\Wechat\bases\FormatRequest;
use Cje\Wechat\bases\HttpClient;
use Cje\Wechat\helper\CacheHelper;
use Cje\Wechat\traits\ConfigTrait;
use Doctrine\Common\Cache\Cache;

class Application
{
    use ConfigTrait;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var Cache
     */
    protected $cache;

    public $accessTokenClass;

    public function getCache()
    {
        if (!$this->cache) {
            $this->cache = CacheHelper::create(CacheHelper::defaultConfig());
        }
        return $this->cache;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function getAccount()
    {
        if (!$this->account) {
            $this->account = new Account($this->config->all());
        }
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    public function getAccessTokenClass()
    {
        if (!$this->accessTokenClass) {
            $this->accessTokenClass = new AccessToken(
                $this->getAccount()->getAppId(),
                $this->getAccount()->getSecret(),
                $this->getCache()
            );
        }
        return $this->accessTokenClass;
    }

    /**
     * get请求
     * @param FormatRequest $formatRequest 格式化请求参数
     * @return HttpClient
     */
    public function get($formatRequest)
    {
        if ($formatRequest->getNeedAccessToken()) {
            $formatRequest->accessToken = $this->getAccessTokenClass()->getAccessToken();
        }
        return $formatRequest->getSend();
    }

    /**
     * post请求
     * @param FormatRequest $formatRequest 格式化请求参数
     * @return HttpClient
     */
    public function post($formatRequest)
    {
        if ($formatRequest->getNeedAccessToken()) {
            $formatRequest->accessToken = $this->getAccessTokenClass()->getAccessToken();
        }
        return $formatRequest->postSend();
    }

    public function jsCodeToSession($code)
    {
        return $this->get(new FormatRequest([
            'api' => 'sns/jscode2session',
            'params' => [
                'appid' => $this->account->getAppId(),
                'secret' => $this->account->getSecret(),
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ],
            'needAccessToken' => false
        ]));
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