<?php
/**
 * Author: 风哀伤
 */
namespace Cje\Wechat\helper;

use InvalidArgumentException;

class AesCbc
{
    /**
     * 解密用户数据
     * @param string $sessionKey 会话密钥
     * @param string $iv 初始化向量
     * @param string $encryptedData 加密后的用户数据
     * @throws InvalidArgumentException 解密失败时抛出异常
     * @return string 解密后的字符串
     */
    public static function decrypt($sessionKey, $iv, $encryptedData)
    {
        $result = openssl_decrypt(
            base64_decode($encryptedData),
            "AES-128-CBC",
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );

        if ($result === false) {
            throw new InvalidArgumentException(openssl_error_string() ?: 'Decrypt AES CBC error.');
        }

        return $result;    
    }

    /**
     * 加密数据
     * @param string $data 待加密数据
     * @param string $key 加密密钥
     * @param string $iv 初始化向量
     * @throws InvalidArgumentException 加密失败时抛出异常
     * @return string 加密后的字符串
     */
    public function encrypt($data, $key, $iv)
    {
        $encrypted = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new InvalidArgumentException(openssl_error_string() ?: 'Encrypt AES CBC error.');
        }
        return base64_encode($encrypted);
    }
}
