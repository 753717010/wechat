<?php
/**
 * Author: 风哀伤
 * 接口类
 */

namespace Cje\Wechat\bases;

abstract class Request extends \Cje\Wechat\bases\BaseClass
{
    protected $needAccessToken;
    protected $api;

    public function getNeedAccessToken()
    {
        return $this->needAccessToken;
    }

    public function getApi()
    {
        return $this->api;
    }

    abstract public function build();
}