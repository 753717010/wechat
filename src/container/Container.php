<?php
/**
 * 依赖注入容器
 * 管理对象的创建和依赖关系
 * 
 * @author 风哀伤
 */

namespace Cje\Wechat\container;

use Closure;
use Exception;

class Container
{
    /**
     * 容器中的服务
     * @var array
     */
    protected $services = [];

    /**
     * 服务实例
     * @var array
     */
    protected $instances = [];

    /**
     * 绑定服务
     * 
     * @param string $name 服务名称
     * @param Closure|string $concrete 服务实现
     * @param bool $shared 是否共享实例
     * @return self
     */
    public function bind(string $name, $concrete, bool $shared = false): self
    {
        $this->services[$name] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];

        return $this;
    }

    /**
     * 绑定共享服务
     * 
     * @param string $name 服务名称
     * @param Closure|string $concrete 服务实现
     * @return self
     */
    public function singleton(string $name, $concrete): self
    {
        return $this->bind($name, $concrete, true);
    }
    /**
     * 当前正在解析的服务
     * @var array
     */
    protected $resolving = [];

    /**
     * 解析服务
     * 
     * @param string $name 服务名称
     * @param array $parameters 构造参数
     * @return mixed
     * @throws Exception
     */
    public function make(string $name, array $parameters = [])
    {
        // 检查循环依赖
        if (in_array($name, $this->resolving)) {
            throw new Exception("Circular dependency detected while resolving '{$name}'");
        }
    
        // 标记当前服务正在解析
        $this->resolving[] = $name;
        // 如果服务不存在，抛出异常
        if (!isset($this->services[$name])) {
            throw new Exception("Service '{$name}' not found");
        }

        $service = $this->services[$name];
        try {
            // 如果是共享服务且已实例化，直接返回
            if ($service['shared'] && isset($this->instances[$name])) {
                return $this->instances[$name];
            }
    
            // 解析服务
            $instance = $this->resolve($service['concrete'], $parameters);
    
            // 如果是共享服务，保存实例
            if ($service['shared']) {
                $this->instances[$name] = $instance;
            }
    
            return $instance;
        } finally {
            // 解析完成后，从解析列表中移除
            $key = array_search($name, $this->resolving);
            if ($key !== false) {
                unset($this->resolving[$key]);
            }
        }
    }

    /**
     * 解析服务实现
     * 
     * @param Closure|string $concrete 服务实现
     * @param array $parameters 构造参数
     * @return mixed
     * @throws Exception
     */
    protected function resolve($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        // 反射创建实例
        $reflector = new \ReflectionClass($concrete);

        // 检查类是否可实例化
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class '{$concrete}' is not instantiable");
        }

        // 获取构造函数
        $constructor = $reflector->getConstructor();

        // 如果没有构造函数，直接实例化
        if (!$constructor) {
            return $reflector->newInstance();
        }

        // 获取构造参数
        $dependencies = $constructor->getParameters();

        // 解析依赖
        $instances = $this->resolveDependencies($dependencies, $parameters);

        // 实例化类
        return $reflector->newInstanceArgs($instances);
    }

    /**
     * 解析依赖
     * 
     * @param array $dependencies 依赖参数
     * @param array $parameters 构造参数
     * @return array
     * @throws Exception
     */
    protected function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $instances = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->getName();

            // 如果提供了参数，使用提供的参数
            if (isset($parameters[$name])) {
                $instances[] = $parameters[$name];
                continue;
            }

            // 获取参数类型
            $type = $dependency->getType();

            if (!$type || $type->isBuiltin()) {
                // 如果没有类型提示或为内置类型，使用默认值
                if ($dependency->isOptional()) {
                    $instances[] = $dependency->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve dependency '{$name}'");
                }
            } else {
                // 解析类型提示的依赖
                $class = $type->getName();
                $uClass = lcfirst($class);
                if ($this->has($class)) {
                    $instances[] = $this->make($class);
                } elseif ($this->has($uClass)) {
                    $instances[] = $this->make($uClass);
                } else {
                    throw new Exception("Cannot resolve dependency '{$name}'");
                }
            }
        }

        return $instances;
    }

    /**
     * 绑定接口到实现
     * 
     * @param string $interface 接口名称
     * @param string $implementation 实现类名称
     * @param bool $shared 是否共享实例
     * @return self
     */
    public function bindInterface(string $interface, string $implementation, bool $shared = false): self
    {
        return $this->bind($interface, $implementation, $shared);
    }



    /**
     * 检查服务是否存在
     * 
     * @param string $name 服务名称
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * 创建容器实例
     * 
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }
}
