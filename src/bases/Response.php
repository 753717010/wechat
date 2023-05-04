<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:16 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatCurlException;
use Cje\Wechat\exception\WechatException;

class Response
{
    /**
     * 原始响应
     * @var
     */
    public $raw;

    /**
     * 已解析响应
     * @var array
     */
    public $parseData;

    public function parse($raw)
    {
        $this->raw = $raw;

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            $error = function_exists('json_last_error_msg') ? json_last_error_msg() : json_last_error();

            throw new WechatCurlException($error);
        }
        $this->parseData = $data;
        return $this;
    }

    public function getData()
    {
        if (!$this->isSuccess()) {
            throw new WechatException($this->parseData['errmsg'], $this);
        }
        return $this->parseData;
    }

    public function isSuccess()
    {
        return isset($this->parseData['errcode']) && $this->parseData['errcode'] == 0;
    }
}
