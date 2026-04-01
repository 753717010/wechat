<?php
/**
 * Author: 风哀伤
 */
namespace Cje\Wechat\helper;

class StringHelper
{
    public static function random(int $length = 16)
    {
        return substr(bin2hex(random_bytes(32)), 0, $length);
    }
}