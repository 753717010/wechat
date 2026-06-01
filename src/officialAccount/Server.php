<?php
/**
 * Author: 风哀伤
 * 服务器类
 */
namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Encryptor;
use Cje\Wechat\exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

class Server
{
    /**
     * @var ServerRequest
     */
    protected $request;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var Signer
     */
    protected $signer;

    /**
     * 构造函数
     * @param ServerRequest $request
     * @param Encryptor $encryptor
     */
    public function __construct(ServerRequest $request, Signer $signer, ?Encryptor $encryptor = null)
    {
        $this->request = $request;
        $this->encryptor = $encryptor;
        $this->signer = $signer;
    }

    /**
     * 验证请求
     * @param array $data
     * @return bool
     */
    public function validate($data)
    {
        return true;
    }

    /**
     * 获取消息类
     * @return Message
     */
    public function getMessage()
    {
        return $this->encryptor ? $this->decryptMessage() : $this->noDecryptMessage();
    }

    /**
     * 获取无加密消息类
     * @return Message
     */
    public function noDecryptMessage()
    {
        $query = $this->request->getQueryParams();
        if (!isset($query['signature']) || !isset($query['timestamp']) || !isset($query['nonce'])) {
            throw new InvalidArgumentException('参数错误');
        }
        
        $data = [
            'timestamp' => $query['timestamp'],
            'nonce' => $query['nonce'],
        ];
        if (!$this->signer->verify($data, $query['signature'])) {
            throw new InvalidArgumentException('签名验证失败');
        }
        $body = $this->request->getBody()->getContents();
        $message = new Message($body);
        return $message;
    }

    /**
     * 获取解密消息类
     * @return Message
     */
    public function decryptMessage()
    {
        $body = $this->request->getBody()->getContents();
        $query = $this->request->getQueryParams();
        if (!isset($query['msg_signature']) || !isset($query['timestamp']) || !isset($query['nonce'])) {
            throw new InvalidArgumentException('参数错误');
        }
        $message = new Message($body);
        if (!$message->has('Encrypt')) {
            throw new InvalidArgumentException('参数错误');
        }
        
        $data = [
            'timestamp' => $query['timestamp'],
            'nonce' => $query['nonce'],
            'Encrypt' => $message->Encrypt,
        ];
        if (!$this->signer->verify($data, $query['msg_signature'])) {
            throw new InvalidArgumentException('签名验证失败');
        }
        
        $message->encryptStr = $this->encryptor->decrypt((string)$message->Encrypt);
        $message->merge($message->parse($message->encryptStr));
        return $message;
    }

    public function server($data = [])
    {
        $response = new Response(200, [], ($this->encryptor ? $this->encryptMessage($data) : $this->noEncryptMessage($data)));
        return $response;
    }

    public function encryptMessage($data = [])
    {
        $data = $this->encryptor->encrypt($data);
        return $data;
    }

    public function noEncryptMessage($data = [])
    {
        return $data;
    }
}
