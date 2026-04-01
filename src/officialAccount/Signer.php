<?php
/**
 * Author: 风哀伤
 */
namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Signer as BasesSigner;

class Signer
{
    protected $token;

    /**
     * 构造函数
     * @param string $token 令牌
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * 验证签名
     * @param array $data 包含timestamp、nonce、token的数组
     * @param string $signature 签名
     * @return bool 是否验证通过
     */
    public function verify(array $data, $signature)
    {
        $data['token'] = $this->token;
        return BasesSigner::verify($data, $signature);
    }

    /**
     * 创建签名
     * @param array $data 包含timestamp、nonce、token的数组
     * @return string 签名
     */
    public function create(array $data)
    {
        $data['token'] = $this->token;
        return BasesSigner::create($data);
    }
}
