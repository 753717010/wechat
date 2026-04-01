<?php
/**
 * 消息基类
 * Author: 风哀伤
 */
namespace Cje\Wechat\bases;

use Cje\Wechat\exception\InvalidArgumentException;
use Cje\Wechat\helper\Xml;

/**
 * @property-read string $original
 */
class Message extends Config
{
    protected $original;

    public function __construct($original)
    {
        $this->original = $original;
        $this->config = self::parse($original);
    }
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * 解析数据
     * @return array<string, mixed>
     */
    public static function parse($original)
    {
        $content = trim($original);

        $parsed = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed) && ! empty($parsed)) {
            return $parsed;
        }

        $parsed = Xml::parse($content);

        if (is_array($parsed) && ! empty($parsed)) {
            return $parsed;
        }

        throw new InvalidArgumentException('无效的数据，数据格式应该是XML或者JSON');
    }
}
