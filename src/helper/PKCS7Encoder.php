<?php
/**
 * PKCS7Encoder
 * 实现PKCS7填充
 * @author 风哀伤
 * @version 1.0.0
 */
namespace Cje\Wechat\helper;

class PKCS7Encoder
{
    const BLOCK_SIZE = 16;

    /**
     * 填充字符串
     * @param string $text 待填充字符串
     * @param int $blockSize 块大小
     * @return string 填充后的字符串
     */
    public static function padding($text, $blockSize = self::BLOCK_SIZE)
    {
		$block_size = $blockSize;
		$text_length = strlen($text);
		//计算需要填充的位数
		$amount_to_pad = $block_size - ($text_length % $block_size);
		if ($amount_to_pad == 0) {
			$amount_to_pad = $block_size;
		}
		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = "";
		for ($index = 0; $index < $amount_to_pad; $index++) {
			$tmp .= $pad_chr;
		}
		return $text . $tmp;
    }

    /**
     * 移除填充字符串
     * @param string $text 待移除填充字符串
     * @param int $blockSize 块大小
     * @return string 移除填充后的字符串
     */
    public static function unpadding($text, $blockSize = self::BLOCK_SIZE)
    {
		$pad = ord(substr($text, -1));
		if ($pad < 1 || $pad > $blockSize) {
			$pad = 0;
		}
		return substr($text, 0, (strlen($text) - $pad));
    }
}