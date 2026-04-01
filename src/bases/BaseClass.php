<?php
/**
 * Author: 风哀伤
 */

namespace Cje\Wechat\bases;

class BaseClass
{
    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }
}