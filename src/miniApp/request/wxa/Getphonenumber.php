<?php
/**
 * Author: 风哀伤
 * 接口类
 * 获取用户手机号
 * @link https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/phone-number/wxacode.getphonenumber.html
 */
namespace Cje\Wechat\miniApp\request\wxa;

class Getphonenumber extends \Cje\Wechat\bases\Request
{
    protected $api = 'wxa/getphonenumber';
    protected $needAccessToken = true;

    /**
     * 调用接口凭证
     */
    public $code;

    public function build(): array
    {
        return [
            'code' => $this->code,
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }
}
