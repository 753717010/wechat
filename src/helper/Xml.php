<?php
/**
 * Xml助手类
 * Author: 风哀伤
 */
namespace Cje\Wechat\helper;

use TheNorthMemory\Xml\Transformer;

class Xml
{
    /**
     * 解析xml
     * @param string $xml
     * @return array
     */
    public static function parse(string $xml): array
    {
        return Transformer::toArray($xml);
    }

    /**
     * 生成xml
     * @param array $data
     * @return string
     */
    public static function build(array $data): string
    {
        return Transformer::toXml($data);
    }
}
