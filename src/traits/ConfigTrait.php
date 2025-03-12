<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/6
 * Time: 1:57 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\traits;

use Cje\Wechat\bases\Config;
use Cje\Wechat\contracts\ConfigInterface;
use Cje\Wechat\exception\InvalidArgumentException;

trait ConfigTrait
{
    protected $config;

    /**
     * @param  array<string,mixed>|ConfigInterface  $config
     *
     * @throws InvalidArgumentException
     */
    public function __construct($config)
    {
        $this->config = is_array($config) ? new Config($config) : $config;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }
}