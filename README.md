# 微信 SDK

一个功能强大、易于使用的微信 SDK，支持微信公众号和小程序的开发。

## 功能特性

- 支持微信公众号和小程序
- 访问令牌管理（普通版和稳定版）
- HTTP 请求处理
- 消息处理和解析
- 配置管理
- 缓存管理
- 依赖注入
- 事件系统
- 中间件系统
- 监控系统

## 安装

使用 Composer 安装：

```bash
composer require cje/wechat
```

## 配置

### 公众号配置

```php
$config = [
    'appId' => 'your-app-id',
    'appSecret' => 'your-app-secret',
    'token' => 'your-token',
    'encodingAESKey' => 'your-encoding-aes-key' // 可选
];

$app = new \Cje\Wechat\officialAccount\Application($config);
```

### 小程序配置

```php
$config = [
    'appId' => 'your-app-id',
    'appSecret' => 'your-app-secret'
];

$app = new \Cje\Wechat\miniApp\Application($config);
```

## 基本用法

### 获取访问令牌

```php
// 获取普通访问令牌
$accessToken = $app->getAccessToken();
echo $accessToken;

// 获取稳定版访问令牌（需要配置缓存）
$config['stable'] = true;
$app = new \Cje\Wechat\officialAccount\Application($config);
$accessToken = $app->getAccessToken();
echo $accessToken;
```

### 发送HTTP请求

```php
// 创建请求对象
$request = new class extends \Cje\Wechat\bases\Request {
    public function getApi(): string {
        return 'cgi-bin/user/get';
    }
    public function getMethod(): string {
        return 'GET';
    }
    public function getNeedAccessToken(): bool {
        return true;
    }
    public function build(): array {
        return ['next_openid' => ''];
    }
};

// 执行请求
$response = $app->get($request);
print_r($response->getContent());
```

### 处理消息

```php
// 获取服务器实例
$server = $app->getServer();

// 设置消息处理器
$server->on('text', function ($message) {
    return "你发送的消息是：{$message->Content}";
});

// 处理消息
$response = $server->serve();
echo $response;
```

## 高级功能

### 事件系统

```php
// 获取事件调度器
$dispatcher = $app->getEventDispatcher();

// 注册事件监听器
$dispatcher->listen('user.login', function ($event) {
    // 处理登录事件
    echo '用户登录了';
    print_r($event->getData());
});

// 触发事件
$event = new \Cje\Wechat\event\BaseEvent('user.login');
$event->setData(['user_id' => 123]);
$dispatcher->dispatch($event);
```

### 中间件系统

```php
// 注册中间件
$app->get('middlewareManager')->add(function ($request, $next) {
    // 请求前处理
    echo '请求开始';
    
    // 执行下一个中间件
    $response = $next($request);
    
    // 响应后处理
    echo '请求结束';
    
    return $response;
});

// 执行带中间件的请求
$response = $app->handle($request, function ($request) {
    // 处理请求
    return $app->get($request);
});
```

## 示例

查看 `demo.php` 文件获取更多示例代码。

## 贡献

欢迎贡献代码！请遵循以下步骤：

1. Fork 仓库
2. 创建一个新的分支
3. 提交你的更改
4. 发送 Pull Request

## 许可证

本项目使用 MIT 许可证。详见 LICENSE 文件。

## 联系方式

如有问题，请通过以下方式联系：

- GitHub: [https://github.com/cje/wechat](https://github.com/cje/wechat)
- Email: your-email@example.com
