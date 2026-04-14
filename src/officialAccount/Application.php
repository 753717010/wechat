<?php
/**
 * 公众号应用核心类
 * 处理公众号相关的所有操作，包括访问令牌管理、消息处理、服务器配置等
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Encryptor;
use Cje\Wechat\bases\Request;
use Cje\Wechat\bases\Response;
use Cje\Wechat\container\Container;
use Cje\Wechat\event\EventDispatcher;
use Cje\Wechat\exception\InvalidArgumentException;
use Cje\Wechat\middleware\MiddlewareManager;
use Cje\Wechat\traits\CacheTrait;
use Cje\Wechat\traits\ConfigTrait;
use Closure;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * 公众号应用核心类
 * @property-read Container $container 容器实例
 * @property-read Config $config 配置工具
 * @property-read EventDispatcher $eventDispatcher 事件调度器
 * @property-read MiddlewareManager $middlewareManager 中间件管理器
 * @property-read ServerRequest $serverRequest 服务器请求
 * @property-read Encryptor $encryptor 加密解密工具
 * @property-read Account $account 账号信息
 * @property-read AccessToken $accessToken 访问令牌
 * @property-read Server $server 服务器配置工具
 * @property-read Cache $cache 缓存工具
 * @property-read Signer $signer 签名工具
 * @property-read Requester $requester 请求工具
 */
class Application
{
    use CacheTrait;
    use ConfigTrait;

    /**
     * 容器实例
     * @var Container
     */
    protected $container;

    /**
     * 初始化应用
     * 检查必要的配置项并注册服务
     */
    public function init()
    {
        // 检查必要的配置项
        $this->validateConfig();

        // 初始化容器
        $this->container = Container::create();

        // 注册服务
        $this->registerServices();
    }

    /**
     * 验证配置
     */
    protected function validateConfig()
    {
        $required = ['appId', 'appSecret'];
        foreach ($required as $key) {
            if (!$this->config->has($key) || empty($this->config->get($key))) {
                throw new InvalidArgumentException("配置项 '{$key}' 不能为空");
            }
        }
    }

    /**
     * 注册服务
     */
    protected function registerServices()
    {
        // 注册基本服务
        $this->registerBasicServices();
        // 注册核心服务
        $this->registerCoreServices();
    }

    /**
     * 注册基本服务
     */
    protected function registerBasicServices()
    {
        $this->container->singleton('config', function () {
            return $this->config;
        });

        $this->container->singleton('cache', function () {
            return $this->getCache();
        });

        $this->container->singleton('serverRequest', function () {
            return ServerRequest::fromGlobals();
        });

        $this->container->singleton('requester', function () {
            return new \Cje\Wechat\bases\Requester();
        });

        // 注册事件调度器
        $this->container->singleton('eventDispatcher', function () {
            return new EventDispatcher();
        });

        // 注册中间件管理器
        $this->container->singleton('middlewareManager', function () {
            return new MiddlewareManager();
        });
    }

    /**
     * 注册核心服务
     */
    protected function registerCoreServices()
    {
        // 注册账号
        $this->container->singleton('account', function (Container $container) {
            $config = $container->make('config');
            return new Account(
                $config->get('appId'),
                $config->get('appSecret'),
                $config->get('token', null),
                $config->get('encodingAESKey', null)
            );
        });

        // 注册签名器
        $this->container->singleton('signer', function (Container $container) {
            $account = $container->make('account');
            return new Signer($account->getToken());
        });

        // 注册加密器
        $this->container->singleton('encryptor', function (Container $container) {
            $account = $container->make('account');
            return new Encryptor(
                $account->getAppId(),
                $account->getToken(),
                $account->getEncodingAESKey()
            );
        });

        // 注册服务器
        $this->registerServer();

        // 注册访问令牌
        $this->container->singleton('accessTokenClass', function (Container $container) {
            $config = $container->make('config');
            $account = $container->make('account');
            $cache = $container->make('cache');
            return new AccessToken(
                $account->getAppId(),
                $account->getSecret(),
                (bool)$config->get('stable', false),
                $cache
            );
        });
    }

    /**
     * 获取容器实例
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    public function registerServer()
    {
        $this->container->singleton('server', function (Container $container) {
            $serverRequest = $container->make('serverRequest');
            $signer = $container->make('signer');
            $account = $container->make('account');
            $encryptor = $account->getEncodingAESKey() ? $container->make('encryptor') : null;
            return new Server($serverRequest, $signer, $encryptor);
        });
    }

    /**
     * 设置服务器请求
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @return self
     */
    public function setServerRequest(\Psr\Http\Message\ServerRequestInterface $serverRequest): self
    {
        // 更新容器中的serverRequest服务
        $this->set('serverRequest', $serverRequest);
        // 重新绑定服务器服务，确保使用最新的serverRequest
        $this->registerServer();
        return $this;
    }

    /**
     * 获取访问令牌
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessTokeClass->getToken(
            $this->account->getAppId(),
            $this->account->getSecret(),
            (bool)$this->config->get('stable', false)
        );
    }

    /**
     * 执行POST请求
     * @param Request $request
     * @return Response
     */
    public function httpPost(Request $request): Response
    {
        $requester = $this->get('requester');
        if ($request->getNeedAccessToken()) {
            $requester->setQueryParams([
                'access_token' => $this->getAccessToken(),
            ]);
        }
        return $requester->postByBody($request->getApi(), $request->build());
    }

    /**
     * 执行GET请求
     * @param Request $request
     * @return Response
     */
    public function httpGet(Request $request): Response
    {
        $requester = $this->get('requester');
        if ($request->getNeedAccessToken()) {
            $requester->setQueryParams([
                'access_token' => $this->getAccessToken(),
            ]);
        }
        return $requester->get($request->getApi(), $request->build());
    }

    /**
     * 触发事件
     * @param string $event 事件名称
     * @param array $data 事件数据
     * @return mixed
     */
    public function event(string $event, array $data = [])
    {
        return $this->get('eventDispatcher')->dispatch($event, $data);
    }

    /**
     * 注册中间件
     * @param mixed $middleware 中间件
     * @return self
     */
    public function middleware($middleware): self
    {
        $this->get('middlewareManager')->add($middleware);
        return $this;
    }

    /**
     * 注册多个中间件
     * @param array $middlewares 中间件列表
     * @return self
     */
    public function middlewares(array $middlewares): self
    {
        $this->get('middlewareManager')->addMany($middlewares);
        return $this;
    }

    /**
     * 执行中间件处理
     * @param mixed $request 请求对象
     * @param callable $callback 回调函数
     * @return mixed
     */
    public function handle($request, callable $callback)
    {
        return $this->get('middlewareManager')->handle($request, $callback);
    }

    /**
     * 魔术方法，设置属性
     * @param string $name 属性名
     * @param mixed $value 属性值
     */
    public function __set(string $name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->set($name, $value);
        }
    }

    /**
     * 魔术方法，获取属性
     * @param string $name 属性名
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->get($name);
    }



    /**
     * 设置服务
     * @param string $name 服务名称
     * @param mixed $value 服务实例或闭包
     */
    public function set(string $name, $value)
    {
        $this->container->bind($name, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        }, true);
    }

    /**
     * 执行GET请求或获取服务
     * @param string $name 服务名称
     * @param array $parameters 构造参数（仅当获取服务时使用）
     * @return Response|mixed
     */
    public function get(string $name, array $parameters = [])
    {
        return $this->container->make($name, $parameters);
    }

    /**
     * 魔术方法，检查属性是否存在
     * @param string $name 属性名
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->container->has($name);
    }
}