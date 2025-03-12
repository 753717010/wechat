<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/12
 * Time: 3:30 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
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