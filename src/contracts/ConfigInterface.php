<?php
/**
 * 配置接口
 * Author: 风哀伤
 */

namespace Cje\Wechat\contracts;

interface ConfigInterface extends \ArrayAccess
{
    /**
     * @return array<string,mixed>
     */
    public function all(): array;

    public function has(string $key): bool;

    public function set(string $key, $value = null);

    /**
     * @param  array<string>|string  $key
     */
    public function get($key, $default = null);
}