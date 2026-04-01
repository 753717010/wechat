<?php
/**
 * Author: 风哀伤
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
        $this->init();
    }

    public function init() {}

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