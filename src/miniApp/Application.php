<?php
/**
 * 小程序应用核心类
 * 处理小程序相关的所有操作，包括登录验证、访问令牌管理等
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\miniApp;

use Cje\Wechat\exception\InvalidArgumentException;

class Application extends \Cje\Wechat\officialAccount\Application
{
    /**
     * 小程序登录
     * 通过code获取openid、session_key等信息
     * 
     * @param string $code 登录凭证（code）
     * @return array|null 登录成功返回包含openid、session_key、unionid的数组，失败返回null
     * @throws InvalidArgumentException
     */
    public function jsCodeToSession(string $code): ?array
    {
        if (empty($code)) {
            throw new InvalidArgumentException('登录凭证code不能为空');
        }
        
        $response = $this->get('requester')->get(
            'sns/jscode2session',
            [
                'appid' => $this->get('account')->getAppId(),
                'secret' => $this->get('account')->getSecret(),
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ]
        );
        
        $result = $response->getJson();
        
        // 检查是否返回错误
        if (isset($result['errcode']) && $result['errcode'] !== 0) {
            return null;
        }
        
        return $result;
    }
}