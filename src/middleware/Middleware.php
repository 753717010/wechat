<?php
/**
 * 中间件接口
 * 定义中间件的基本方法
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\middleware;

use Closure;

interface Middleware
{
    /**
     * 处理请求
     * 
     * @param mixed $request 请求对象
     * @param Closure $next 下一个中间件
     * @return mixed 响应对象
     */
    public function handle($request, Closure $next);
}
