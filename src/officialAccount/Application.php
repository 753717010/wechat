<?php
/**
 * Author: 风哀伤
 */

namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Encryptor;
use Cje\Wechat\exception\InvalidArgumentException;
use Cje\Wechat\traits\CacheTrait;
use Cje\Wechat\traits\ConfigTrait;
use Cje\Wechat\traits\RequesterTrait;
use Cje\Wechat\traits\ServerRequestTrait;

class Application
{
    use CacheTrait;
    use ConfigTrait;
    use RequesterTrait;
    use ServerRequestTrait;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var AccessToken
     */
    protected $accessTokenClass;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Signer
     */
    protected $signer;

    /**
     * 获取账号类
     * @return Account
     */
    public function getAccount()
    {
        if (!$this->account) {
            $this->account = new Account(
                $this->config->get('appId'),
                $this->config->get('appSecret'),
                $this->config->get('token', null),
                $this->config->get('encodingAESKey', null)
            );
        }
        return $this->account;
    }

    /**
     * 设置账号类
     * @param Account $account
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * 获取加密类
     * @return Encryptor
     * @throws InvalidArgumentException
     */
    public function getEncryptor()
    {
        if (! $this->encryptor) {
            $aesKey = $this->getAccount()->getEncodingAESKey();
            $token = $this->getAccount()->getToken();
            if (empty($token)) {
                throw new InvalidArgumentException('token不能为空');
            }

            if (empty($aesKey)) {
                throw new InvalidArgumentException('encodingAESKey不能为空');
            }

            $this->encryptor = new Encryptor(
                $this->getAccount()->getAppId(),
                $token,
                $aesKey
            );
        }

        return $this->encryptor;
    }

    /**
     * 设置加密类
     * @param Encryptor $encryptor
     */
    public function setEncryptor(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    /**
     * 获取签名类
     * @return Signer
     */
    public function getSigner()
    {
        if (!$this->signer) {
            $token = $this->getAccount()->getToken();
            if (empty($token)) {
                throw new InvalidArgumentException('token不能为空');
            }
            $this->signer = new Signer($token);
        }
        return $this->signer;
    }
     /**
     * 设置签名类
     * @param Signer $signer
     */
    public function setSigner(Signer $signer)
    {
        $this->signer = $signer;

        return $this;
    }

    public function getServer()
    {
        if (!$this->server) {
            $this->server = new Server(
                $this->getServerRequest(),
                $this->getSigner(),
                $this->getAccount()->getEncodingAESKey() ? $this->getEncryptor() : null
            );
        }
        return $this->server;
    }

    public function setServer(Server $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * 获取accessToken类
     * @return AccessToken
     */
    public function getAccessTokenClass()
    {
        if (!$this->accessTokenClass) {
            $this->accessTokenClass = new AccessToken(
                $this->getAccount()->getAppId(),
                $this->getAccount()->getSecret(),
                $this->config->get('stable', false),
                $this->getCache()
            );
        }
        return $this->accessTokenClass;
    }

    /**
     * 执行请求
     * @param Request $request
     * @return Response
     */
    public function post($request)
    {
        $requester = $this->getRequester();
        if ($request->getNeedAccessToken()) {
            $requester->setQueryParams([
                'access_token' => $this->getAccessTokenClass()->getToken(),
            ]);
        }
        return $requester->postByBody($request->getApi(), $request->build());   
    }

    /**
     * 执行请求
     * @param Request $request
     * @return Response
     */
    public function get($request)
    {
        $requester = $this->getRequester();
        if ($request->getNeedAccessToken()) {
            $requester->setQueryParams([
                'access_token' => $this->getAccessTokenClass()->getToken(),
            ]);
        }
        return $requester->get($request->getApi(), $request->build());   
    }
}