<?php
/**
 * Author: 风哀伤
 */

namespace Cje\Wechat\miniApp;

class Application extends \Cje\Wechat\officialAccount\Application
{
    /**
     * 小程序登录
     * @param string $code 登录凭证（code）
     * @return array|null 登录成功返回包含openid、session_key、unionid的数组，失败返回null
     */
    public function jsCodeToSession($code)
    {
        return $this->getRequester()->get(
            'sns/jscode2session',
            [
                'appid' => $this->getAccount()->getAppId(),
                'secret' => $this->getAccount()->getSecret(),
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ]
        )->getJson();
    }
}