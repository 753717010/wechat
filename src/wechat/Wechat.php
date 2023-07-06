<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:16 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\wechat;

class Wechat
{
    protected $appId;
    protected $appSecret;

    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getSecret()
    {
        return $this->appSecret;
    }
}
