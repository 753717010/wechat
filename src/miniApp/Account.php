<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:16 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\miniApp;

use Cje\Wechat\bases\BaseClass;
use Cje\Wechat\exception\WechatException;

class Account extends BaseClass
{
    protected $appId;
    protected $appSecret;

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
