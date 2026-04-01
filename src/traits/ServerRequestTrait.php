<?php
/**
 * 服务器请求特征
 * Auth: 风哀伤
 */
namespace Cje\Wechat\traits;

use Cje\Wechat\officialAccount\Server;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 服务器请求特征
 */
trait ServerRequestTrait
{
    /**
     * @var Server
     */
    protected $serverRequest;

    /**
     * 获取服务器请求
     * @return ServerRequestInterface
     */
    public function getServerRequest()
    {
        if (!$this->serverRequest) {
            $this->serverRequest = ServerRequest::fromGlobals();
        }
        return $this->serverRequest;
    }

    /**
     * 设置服务器请求
     * @param ServerRequestInterface $serverRequest
     */
    public function setServerRequest(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;

        return $this;
    }
}
