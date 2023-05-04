<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 2:26 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\helper;

class CurlHelper
{
    public static function getUrl($gateway, $api)
    {
        return rtrim($gateway) . '/' . ltrim($api);
    }

    /**
     * 将数组附加到url后面形成get参数
     * @param $url
     * @param $params
     * @return mixed|string
     */
    public static function appendParams($url, $params = [])
    {
        if (!is_array($params)) {
            return $url;
        }
        if (!count($params)) {
            return $url;
        }
        $url = trim($url, '?');
        $url = trim($url, '&');
        $queryString = self::paramsToQueryString($params);
        if (mb_stripos($url, '?')) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }

    /**
     * 将数组转化成url参数的形式
     * @param $params
     * @return string
     */
    public static function paramsToQueryString($params = [])
    {
        if (!is_array($params)) {
            return '';
        }
        if (!count($params)) {
            return '';
        }
        $str = '';
        foreach ($params as $k => $v) {
            $v = urlencode($v);
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }
}