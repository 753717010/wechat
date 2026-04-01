<?php
/**
 * Author: 风哀伤
 */

namespace Cje\Wechat\officialAccount;

use Cje\Wechat\exception\WechatException;

class Account
{
    protected $appId;
    protected $appSecret;
    protected $token;
    protected $encodingAESKey;

    public function __construct($appId, $appSecret, $token = null, $encodingAESKey = null)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->token = $token;
        $this->encodingAESKey = $encodingAESKey;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getEncodingAESKey()
    {
        return $this->encodingAESKey;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getSecret()
    {
        if ($this->appSecret === null) {
            throw new WechatException('No secret configured.');
        }
        return $this->appSecret;
    }
}
