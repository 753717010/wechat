<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 2:19 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatCurlException;
use Cje\Wechat\helper\CurlHelper;

class Requester
{
    protected $gateway;

    /**
     * @param array $options
     */
    public $options = [];

    public function __construct($options = [], $gateway = 'https://api.weixin.qq.com')
    {
        $this->gateway = $gateway;

        $this->options = $options + [
                CURLOPT_FAILONERROR => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ];
    }

    /**
     * 拼接请求地址
     * @param $api
     * @param $params
     * @return mixed|string
     */
    public function getUrl($api, $params = [])
    {
        return CurlHelper::appendParams(CurlHelper::getUrl($this->gateway, $api), $params);
    }

    /**
     * 发起 POST 请求.
     *
     * @param $api
     * @param array $data post请求参数
     * @param array $params query请求参数
     * @return bool|string
     * @throws WechatCurlException
     */
    public function post($api, $data, $params = [])
    {
        $options = $this->options + [
                CURLOPT_URL => $this->getUrl($api, $params),
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data
            ];
        return $this->execute($options);
    }


    /**
     * @param $api
     * @param $params
     * @return bool|string
     * @throws WechatCurlException
     */
    public function get($api, $params = [])
    {
        $options = $this->options + [
                CURLOPT_URL => $this->getUrl($api, $params)
            ];
        return $this->execute($options);
    }

    public function execute($options)
    {
        $ch = curl_init();

        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);

            throw new WechatCurlException(curl_error($ch), curl_errno($ch));
        }

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            curl_close($ch);

            throw new WechatCurlException($response, $httpStatusCode);
        }

        curl_close($ch);

        return $response;
    }
}
