<?php
/**
 * Author: 风哀伤
 * 缓存trait
 */
namespace Cje\Wechat\traits;

use Cje\Wechat\helper\CacheHelper;
use  Doctrine\Common\Cache\Cache;

trait CacheTrait
{
    /**
     * 缓存类
     * @var Cache
     */
    protected $cache;

    /**
     * 获取缓存类
     * @return Cache
     */
    public function getCache()
    {
        if (!$this->cache) {
            $this->cache = CacheHelper::create(CacheHelper::defaultConfig());
        }
        return $this->cache;
    }

    /**
     * 设置缓存类
     * @param Cache $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }
}
