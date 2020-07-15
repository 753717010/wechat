<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/26
 * Time: 9:24
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace luweiss\Wechat;

/**
 * Class WechatImmediateDelivery
 * @package luweiss\Wechat
 * @property Wechat $wechat
 * 即时配送相关接口
 */
class WechatImmediateDelivery extends WechatBase
{
    public $wechat;
    public $appkey; // 一般为商家在登录配送公司开放平后分配的相应的appkey值
    public $AppSecret; // 一般为商家在登录配送公司开放平后分配的相应的秘钥
    public $shopid; // 商家id， 由配送公司分配的appkey
    public $shop_order_id; // 唯一标识订单的 ID，由商户生成, 不超过128字节
    public $shop_no; // 商家门店编号，在配送公司登记，美团、闪送必填
    public $delivery_id; // 配送公司ID
    public $openid; // 下单用户的openid

    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getAccessToken($refresh = false)
    {
        return $this->wechat->getAccessToken($refresh);
    }

    /**
     * @return string
     * 运力侧签名delivery_sign
     */
    public function getDeliverySign()
    {
        $data = $this->shopid . $this->shop_order_id . $this->AppSecret;
        return SHA1($data);
    }

    /**
     * @return array
     * 获取配置信息
     */
    public function getConfig()
    {
        return [
            'appkey' => $this->appkey,
            'AppSecret' => $this->AppSecret,
            'shopid' => $this->shopid,
            'shop_order_id' => $this->shop_order_id,
            'shop_no' => $this->shop_no,
            'delivery_id' => $this->delivery_id,
            'openid' => $this->openid,
            'delivery_sign' => $this->getDeliverySign()
        ];
    }

    /**
     * @param array $result
     * @return array
     * @throws WechatException
     */
    protected function getClientResult($result)
    {
        if (!isset($result['resultcode'])) {
            throw new WechatException(
                '返回数据格式不正确: ' . json_encode($result, JSON_UNESCAPED_UNICODE)
            );
        }
        if ($result['resultcode'] !== 0) {
            $msg = 'returnCode: ' . $result['resultcode'] . ', returnMsg: ' . $result['resultmsg'];
            throw new WechatException($msg, 0, null, $result);
        }
        return $result;
    }

    /**
     * @param $api
     * @param $arg
     * @return array
     * @throws WechatException
     */
    public function send($api, $arg)
    {
        $res = $this->getClient()->setPostDataType(WechatHttpClient::POST_DATA_TYPE_BODY)->post($api, json_encode($arg, JSON_UNESCAPED_UNICODE));
        return $this->getClientResult($res);
    }

    /**
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.getBindAccount.html
     * 拉取已绑定账号
     */
    public function getBindAccount()
    {
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/shop/get';
        return $this->send($api, [
            'access_token' => $this->getAccessToken()
        ]);
    }

    /**
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.getAllImmeDelivery.html
     * 获取已支持的配送公司列表接口
     */
    public function getAllImmeDelivery()
    {
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/delivery/getall';
        return $this->send($api, [
            'access_token' => $this->getAccessToken()
        ]);
    }

    /**
     * @param array $args ['sender', 'receiver', 'cargo', 'order_info', 'shop', 'sub_biz_id']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.preAddOrder.html
     * 预下配送单接口
     */
    public function preAddOrder($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/pre_add?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['sender', 'receiver', 'cargo', 'order_info', 'shop', 'sub_biz_id']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.addOrder.html
     * 下配送单接口
     */
    public function addOrder($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/add?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['sender', 'receiver', 'cargo', 'order_info', 'shop', 'sub_biz_id']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.reOrder.html
     * 重新下配送单接口
     */
    public function reOrder($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/readd?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['waybill_id', 'tips', 'remark']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.addTip.html
     * 可以对待接单状态的订单增加小费。需要注意：订单的小费，以最新一次加小费动作的金额为准，故下一次增加小费额必须大于上一次小费额
     */
    public function addTip($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/addtips?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['waybill_id', 'cancel_reason_id', 'cancel_reason']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.preCancelOrder.html
     * 预取消配送单接口
     */
    public function preCancelOrder($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/precancel?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['waybill_id', 'cancel_reason_id', 'cancel_reason']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.cancelOrder.html
     * 取消配送单接口
     */
    public function cancelOrder($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/cancel?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['waybill_id', 'remark']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.abnormalConfirm.html
     * 异常件退回商家商家确认收货接口
     */
    public function abnormalConfirm($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/confirm_return?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['waybill_id', 'remark']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.getOrder.html
     * 拉取配送单信息
     */
    public function getOrder($args)
    {
        $args = array_merge($args, $this->getConfig());
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/order/get?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }

    /**
     * @param array $args ['waybill_id', 'remark']
     * @return array
     * @throws WechatException
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/immediate-delivery/by-business/immediateDelivery.mockUpdateOrder.html
     * 模拟配送公司更新配送单状态, 该接口只用于沙盒环境，即订单并没有真实流转到运力方
     */
    public function mockUpdateOrder($args)
    {
        $args['shopid'] = 'test_shop_id';
        $api = 'https://api.weixin.qq.com/cgi-bin/express/local/business/test_update_order?access_token=' . $this->getAccessToken();
        return $this->send($api, $args);
    }
}
