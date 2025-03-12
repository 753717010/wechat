<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/6
 * Time: 1:26 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
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

    public function all(): array
    {
        return $this->config;
    }

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

    public function offsetExists($offset)
    {
        return $this->has(strval($offset));
    }

    public function offsetGet($offset)
    {
        return $this->get(strval($offset));
    }

    public function offsetSet($offset, $value)
    {
        $this->set(strval($offset), $value);
    }

    public function offsetUnset($offset)
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
}