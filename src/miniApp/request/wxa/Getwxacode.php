<?php
/**
 * Author: 风哀伤
 * 接口类
 * 获取小程序二维码
 * @link https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.get.html
 */
namespace Cje\Wechat\miniApp\request\wxa;

class Getwxacode extends \Cje\Wechat\bases\Request
{
    protected $api = 'wxa/getwxacode';
    protected $needAccessToken = true;

    /**
     * 页面路径，必须是已经发布的小程序存在的页面（否则报错），例如：pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
     * 扫码进入的小程序页面路径，最大长度 1024 个字符，不能为空，scancode_time为系统保留参数，不允许配置；对于小游戏，可以只传入 query 部分，来实现传参效果，如：传入 "?foo=bar"，即可在 wx.getLaunchOptionsSync 接口中的 query 参数获取到 {foo:"bar"}。
     */
    public $path;

    /**
     * 二维码的宽度，单位 px，最小 280px，最大 1280px
     */
    public $width = 430;

    /**
     * 是否自动配置线条颜色，如果为 false，则使用 line_color 作为二维码的线条颜色
     */
    public $auto_color = false;

    /**
     * 二维码的线条颜色，auto_color 为 false 时生效，默认值为 {"r":0,"g":0,"b":0}
     */
    public $line_color = [
        'r' => 0,
        'g' => 0,
        'b' => 0,
    ];

    /**
     * 是否需要透明底色，为 true 时，二维码背景透明，可用于叠加在有背景的图片上
     */
    public $is_hyaline = false;

    /**
     * 要打开的小程序版本。正式版为 release，体验版为 trial，开发版为 develop
     */
    public $env_version = 'release';

    public function build(): array
    {
        return [
            'path' => $this->path,
            'width' => $this->width,
            'auto_color' => $this->auto_color,
            'line_color' => $this->line_color,
            'env_version' => $this->env_version,
        ];
    }
}
