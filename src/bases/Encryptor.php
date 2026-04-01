<?php
/**
 * Author: 风哀伤
 */
namespace Cje\Wechat\bases;

use Cje\Wechat\exception\InvalidArgumentException;
use Cje\Wechat\exception\WechatException;
use Cje\Wechat\helper\PKCS7Encoder;
use Cje\Wechat\helper\StringHelper;
use Cje\Wechat\helper\Xml;

class Encryptor
{
    protected $appid;
    protected $token;
    protected $aesKey;

    public function __construct(string $appId, string $token, string $aesKey)  
    {
        $this->appid = $appId;
        $this->token = $token;
        $this->aesKey = base64_decode($aesKey . '=', true) ?: '';
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * 解密数据
     * 将ciphertext使用AESKey进行AES解密，得到FullStr，字节长度为205。AES 采用 CBC 模式，秘钥长度为 32 个字节（256 位），数据采用 PKCS#7 填充； PKCS#7：K 为秘钥字节数（采用 32），Buf 为待加密的内容，N 为其字节数。Buf 需要被填充为 K 的整数倍。在 Buf 的尾部填充(K - N%K)个字节，每个字节的内容 是(K - N%K)
     * @param string $ciphertext 待解密数据
     * @throws RuntimeException 解密失败时抛出异常
     * @return string 解密后的字符串
     */
    public function decrypt(string $ciphertext)
    {
        $plaintext = PKCS7Encoder::unpadding(
            openssl_decrypt(
                base64_decode($ciphertext, true) ?: '',
                'aes-256-cbc',
                $this->aesKey,
                OPENSSL_NO_PADDING,
                substr($this->aesKey, 0, 16)
            ) ?: '',
            strlen($this->aesKey)
        );
        if (strlen($plaintext) < 16)
            return "";
        
        $content = substr($plaintext, 16, strlen($plaintext));
        $len_list = unpack("N", substr($content, 0, 4));
        $xml_len = $len_list[1];
        if ($this->appid && trim(substr($content, $xml_len + 4)) !== $this->appid) {
            throw new InvalidArgumentException('无效的应用id');
        }

        return substr($content, 4, $xml_len);
    }

    /**
     * 加密数据
     * @param array $array 待加密数据
     * @param string|null $nonce 随机字符串
     * @param int|null $timestamp 时间戳
     * @param string $messageType 消息类型，xml或json
     * @return string 加密后的字符串
     */
    public function encrypt(array $array, ?string $nonce = null, $timestamp = null, string $messageType = 'xml')
    {
        return $messageType === 'xml' ? $this->encryptXml($array, $nonce, $timestamp) : $this->encryptJson($array, $nonce, $timestamp);
    }

    /**
     * 加密xml数据
     * @param array $array 待加密数据
     * @param string|null $nonce 随机字符串
     * @param int|null $timestamp 时间戳
     * @return string 加密后的字符串
     */
    public function encryptXml(array $array, ?string $nonce = null, $timestamp = null)
    {
        $xml = Xml::build($array);
        $array = $this->getArray($xml, $nonce, $timestamp);

        return Xml::build($array);
    }

    /**
     * 加密json数据
     * @param array $array 待加密数据
     * @param string|null $nonce 随机字符串
     * @param int|null $timestamp 时间戳
     * @return string 加密后的字符串
     */
    public function encryptJson(array $array, ?string $nonce = null, $timestamp = null)
    {
        $json = json_encode($array, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new InvalidArgumentException('无效的json数据');
        }
        $array = $this->getArray($json, $nonce, $timestamp);

        $json = json_encode($array, JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new InvalidArgumentException('无效的json数据');
        }

        return $json;
    }

    /**
     * 消息包
     * @param string $plaintext 待加密数据
     * @param string|null $nonce 随机字符串
     * @param int|null $timestamp 时间戳
     * @return array 加密后的消息包
     */
    public function getArray(string $plaintext, ?string $nonce = null, $timestamp = null)
    {
        $ciphertext = $this->getCiphertext($plaintext);
        return [
            'Nonce' => $nonce ?: StringHelper::random(16),
            'TimeStamp' => $timestamp ?: time(),
            'Encrypt' => $ciphertext,
            'MsgSignature' => Signer::create([
                'token' => $this->token,
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'Encrypt' => $ciphertext,
            ]),
        ];
    }

    /**
     * 加密数据
     * @param string $plaintext 待加密数据
     * @return string 加密后的字符串
     */
    public function getCiphertext(string $plaintext)
    {
        try {
            if (empty($this->aesKey)) {
                throw new InvalidArgumentException('aes_key不能为空');
            }
            $content = PKCS7Encoder::padding(
                random_bytes(16) . pack('N', strlen($plaintext)) . $plaintext . $this->appid,
                strlen($this->aesKey)
            );
            $iv = substr($this->aesKey, 0, 16);
            $ciphertext = openssl_encrypt(
                $content,
                'aes-256-cbc',
                $this->aesKey,
                OPENSSL_NO_PADDING,
                $iv
            );
            return base64_encode($ciphertext);
        } catch (\Throwable $th) {
            throw new WechatException($th->getMessage());
        }
    }
}