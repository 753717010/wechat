<?php
/**
 * Author: 风哀伤
 * 小程序accessToken
 * 稳定accessToken：https://developers.weixin.qq.com/miniprogram/dev/server/API/mp-access-token/api_getaccesstoken.html
 * 普通accessToken：https://developers.weixin.qq.com/miniprogram/dev/server/API/mp-access-token/api_getstableaccesstoken.html
 */

namespace Cje\Wechat\miniApp;

class AccessToken extends \Cje\Wechat\officialAccount\AccessToken
{
    const PREFIX_CACHE_KEY = 'MINI_APP_ACCESS_TOKEN_OF_';
}