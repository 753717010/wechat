<?php
/**
 * Author: 风哀伤
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
