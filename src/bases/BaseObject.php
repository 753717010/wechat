<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:26 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\exception\WechatException;

class BaseObject
{
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }
        throw new WechatException('无效的设置方法');
    }

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        throw new WechatException('无效的属性');
    }
}