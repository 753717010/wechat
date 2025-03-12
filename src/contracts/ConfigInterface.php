<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/6
 * Time: 1:25 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
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