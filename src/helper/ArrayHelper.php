<?php
/**
 * Created By PhpStorm
 * User: 风哀伤
 * Date: 2025/3/6
 * Time: 1:32 PM
 * @copyright: ©2025 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace Cje\Wechat\helper;

class ArrayHelper
{
    /**
     * @param  array<int|string, mixed>  $array
     */
    public static function exists(array $array, $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * @param  array<string|int, mixed>  $array
     * @param  string|int|array<string|int, mixed>|null  $keys
     */
    public static function has(array $array, $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (empty($array)) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', (string) $key) as $segment) {
                if (static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  array<string|int, mixed>  $array
     * @return array<string, mixed>
     */
    public static function set(array &$array, $key, $value)
    {
        if (! is_string($key)) {
            $key = (string) $key;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    public static function get($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::get($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_object($array) && property_exists($array, $key)) {
            return $array->$key;
        }

        if (static::exists($key, $array)) {
            return $array[$key];
        }

        if ($key && ($pos = strrpos($key, '.')) !== false) {
            $array = static::get($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (static::exists($key, $array)) {
            return $array[$key];
        }
        if (is_object($array)) {
            try {
                return $array->$key;
            } catch (\Exception $e) {
                if ($array instanceof \ArrayAccess) {
                    return $default;
                }
                throw $e;
            }
        }

        return $default;
    }
}