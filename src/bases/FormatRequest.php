<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/12
 * Time: 3:29 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\helper\CurlHelper;

class FormatRequest extends BaseClass
{
    public const BASE_URI = 'https://api.weixin.qq.com';

    protected $api;
    protected $data;
    protected $params;
    protected $header;
    protected $needAccessToken = true;
    public $accessToken;
    protected $is_file;

    /**
     * 拼接请求地址
     * @return mixed|string
     */
    public function getUrl()
    {
        $this->needAccessToken && $this->params['access_token'] = $this->params['access_token'] ?? $this->accessToken;
        return CurlHelper::appendParams(CurlHelper::getUrl(self::BASE_URI, $this->api), $this->params);
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->is_file)) {
            $this->header[] = 'Content-Type:application/json';
        } else {
            $this->header[] = 'Content-Type:multipart/form-data';
        }
        return $this->header;
    }

    /**
     * @return array|string
     */
    public function getData()
    {
        if (empty($this->is_file)) {
            return json_encode($this->data);
        } else {
            return $this->data;
        }
    }

    public function getNeedAccessToken()
    {
        return $this->needAccessToken;
    }

    public function getSend()
    {
        $client = new HttpClient();
        $client->get($this->getUrl(), $this->getHeader());
        return $client;
    }

    public function postSend()
    {
        $client = new HttpClient();
        $client->post($this->getUrl(), $this->getData(), $this->getHeader());
        return $client;
    }
}
