<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2023/5/4
 * Time: 1:33 PM
 * @copyright: ©2023 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\helper;

use Cje\Wechat\exception\WechatException;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;

class CacheHelper
{
    const CACHE_TARGET_FILE = 'file';
    const CACHE_TARGET_REDIS = 'redis';
    const CACHE_TARGET_MEMCACHED = 'memcached';
    const CACHE_TARGET_APCU = 'apcu';

    public static function create($array = [])
    {
        $target = !empty($array['target']) ? $array['target'] : static::CACHE_TARGET_FILE;
        switch ($target) {
            case static::CACHE_TARGET_FILE:
                $dir = !empty($array['dir']) ?
                    $array['dir'] : (dirname(__DIR__) . '/runtime/cache');
                $arrayObject = new FilesystemCache($dir);
                @chmod($dir, 0777);
                break;
            case static::CACHE_TARGET_REDIS:
                $host = !empty($array['host']) ? $array['host'] : '127.0.0.1';
                $port = !empty($array['port']) ? $array['port'] : 6379;
                $redis = new \Redis();
                $redis->connect($host, $port);
                if (!empty($array['password'])) {
                    $redis->auth($array['password']);
                }
                $arrayObject = new RedisCache();
                $arrayObject->setRedis($redis);
                break;
            case static::CACHE_TARGET_MEMCACHED:
                $host = !empty($array['host']) ? $array['host'] : '127.0.0.1';
                $port = !empty($array['port']) ? $array['port'] : 6379;
                $memcached = new \Memcached();
                if (!empty($array['username']) && !empty($array['password'])) {
                    $memcached->setSaslAuthData($array['username'], $array['password']);
                }
                $memcached->addServer($host, $port);
                $arrayObject = new MemcachedCache();
                $arrayObject->setMemcached($memcached);
                break;
            case static::CACHE_TARGET_APCU:
                $arrayObject = new ApcuCache();
                break;
            default:
                throw new WechatException('无效的cache target `' . $target . '`。');
        }
        return $arrayObject;
    }

    public static function defaultConfig()
    {
        return [
            'target' => static::CACHE_TARGET_FILE,
            'dir' => dirname(__DIR__) . '/runtime/cache'
        ];
    }
}
