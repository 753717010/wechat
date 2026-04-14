<?php
/**
 * 中间件管理器
 * 管理和执行中间件
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\middleware;

use Closure;

class MiddlewareManager
{
    /**
     * 中间件列表
     * @var array
     */
    protected $middlewares = [];

    /**
     * 添加中间件
     * 
     * @param string|Middleware $middleware 中间件类名或实例
     * @return self
     */
    public function add($middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 添加多个中间件
     * 
     * @param array $middlewares 中间件列表
     * @return self
     */
    public function addMany(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->add($middleware);
        }
        return $this;
    }

    /**
     * 执行中间件
     * 
     * @param mixed $request 请求对象
     * @param Closure $final 最终处理函数
     * @return mixed 响应对象
     */
    public function handle($request, Closure $final)
    {
        // 构建中间件栈
        $stack = array_reduce(
            array_reverse($this->middlewares),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    // 如果是中间件实例，直接调用
                    if ($middleware instanceof Middleware) {
                        return $middleware->handle($request, $next);
                    }
                    // 如果是类名，实例化后调用
                    elseif (is_string($middleware) && class_exists($middleware)) {
                        $middlewareInstance = new $middleware();
                        return $middlewareInstance->handle($request, $next);
                    }
                    // 如果是闭包，直接调用
                    elseif ($middleware instanceof Closure) {
                        return $middleware($request, $next);
                    }
                    return $next($request);
                };
            },
            $final
        );

        // 执行中间件栈
        return $stack($request);
    }

    /**
     * 清除所有中间件
     * 
     * @return self
     */
    public function clear(): self
    {
        $this->middlewares = [];
        return $this;
    }

    /**
     * 获取中间件列表
     * 
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
