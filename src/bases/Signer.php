<?php
/**
 * Author: 风哀伤
 */
namespace Cje\Wechat\bases;

class Signer
{
    /**
     * 签名数据
     * 按照字典序排序，将参数字符串拼接成一个字符串，进行sha1计算签名
     * @param array $data 待签名数据
     * @return string 签名后的字符串
     */
    public static function create(array $data)
    {
        sort($data, SORT_STRING);
        return sha1(implode($data));
    }

    /**
     * 验证签名
     * @param array $data 待验证数据
     * @param string $sign 待验证签名
     * @return bool 是否验证通过
     */
    public static function verify(array $data, $sign)
    {
        return self::create($data) === $sign;
    }
}
