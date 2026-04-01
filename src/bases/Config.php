<?php
/**
 * 配置类
 * Author: 风哀伤
 */

namespace Cje\Wechat\bases;

use Cje\Wechat\contracts\ConfigInterface;
use Cje\Wechat\exception\InvalidArgumentException;
use Cje\Wechat\helper\ArrayHelper;

class Config implements \ArrayAccess, ConfigInterface
{
    protected $config = [];

    /**
     * @var array<string>
     */
    protected $requiredKeys = [];

    /**
     * @param array<string, mixed> $config
     * @throws InvalidArgumentException
     */
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->checkMissingKeys();
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return ArrayHelper::has($this->config, $key);
    }

    public function set(string $key, $value = null)
    {
        ArrayHelper::set($this->config, $key, $value);
    }

    public function get($key, $default = null)
    {
        return ArrayHelper::get($this->config, $key, $default);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has(strval($offset));
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->get(strval($offset));
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set(strval($offset), $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->set(strval($offset), null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkMissingKeys()
    {
        if (empty($this->requiredKeys)) {
            return true;
        }

        $missingKeys = [];

        foreach ($this->requiredKeys as $key) {
            if (! $this->has($key)) {
                $missingKeys[] = $key;
            }
        }

        if (! empty($missingKeys)) {
            throw new InvalidArgumentException(sprintf("\"%s\" cannot be empty.\r\n", implode(',', $missingKeys)));
        }

        return true;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function merge(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }
}