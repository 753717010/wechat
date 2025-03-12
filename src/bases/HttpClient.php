<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/12
 * Time: 3:13 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatCurlException;

class HttpClient
{
    protected $header;
    protected $reqDatas;
    protected $rspDatas;
    protected $raw;
    /**
     * 发起 POST 请求.
     *
     * @param $url
     * @param array $data post请求参数
     * @return self
     * @throws WechatCurlException
     */
    public function post($url, $data, $header = [])
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header
        ];
        $this->header = $header;
        $this->reqDatas = $data;
        return $this->execute($options);
    }

    /**
     * @param $url
     * @param $params
     * @return self
     * @throws WechatCurlException
     */
    public function get($url, $header = [])
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $header
        ];
        $this->header = $header;
        return $this->execute($options);
    }

    public function execute($options)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        # 处理系统的报错

        if ($response === false || curl_errno($ch)) {
            curl_close($ch);

            throw new WechatCurlException(curl_error($ch));
        }

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            curl_close($ch);

            throw new WechatCurlException($response, $httpStatusCode);
        }

        curl_close($ch);

        $this->raw = $response;

        $data = json_decode($response, true);
        if (!is_array($data)) {
            $error = function_exists('json_last_error_msg') ? json_last_error_msg() : json_last_error();

            throw new WechatCurlException($error);
        }
        $this->rspDatas = $data;
        return $this;
    }

    /**
     * 本次请求的http头信息
     */
    public function getHeaders()
    {
        return $this->header;
    }

    /**
     * 本次请求的数据
     */
    public function getReqDatas()
    {
        return $this->reqDatas;
    }

    /**
     * 本次请求的应答数据
     */
    public function getRspDatas()
    {
        return $this->rspDatas;
    }

    /**
     * 原始应答
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
