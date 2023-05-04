<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:24 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\exception;

class WechatException extends \Exception
{
    public $response;

    public function __construct($message, $response = null)
    {
        $this->response = $response;
        parent::__construct($message);
    }
}
