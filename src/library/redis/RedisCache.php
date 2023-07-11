<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: library\cache
 * Class RedisCache
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 21:21
 * Description :
 */

namespace library\redis;

use cleanphp\App;
use cleanphp\base\Config;
use cleanphp\base\Variables;
use cleanphp\cache\CacheInterface;
use cleanphp\exception\ExtendError;
use cleanphp\file\Log;
use Redis;
use RedisException;

class RedisCache implements CacheInterface
{

    private ?Redis $redis = null;
    private ?string $cache_path = null;
    private ?int $cache_expire = null;

    public static function isEnabled(): bool
    {
        $config = Config::getConfig("redis");
        return !isset($config["host"]) || !isset($config["port"]) || !class_exists("\\Redis");
    }
    /**
     * @throws RedisCacheException|ExtendError
     */
    public function __construct()
    {
        $config = Config::getConfig("redis");
        if (!isset($config["host"]) || !isset($config["port"]))
            throw new RedisCacheException("Redis配置文件缺失，请在 config/config.yml 中添加配置");
        if (!class_exists("\\Redis")) {
            throw new ExtendError(sprintf("缺少Redis拓展，请在php.ini中启用，配置参考链接：%s", "https://redis.com.cn/topics/php-redis-extension.html"), "redis");
        }
        $this->redis = new Redis();
        try{
            $boolean = $this->redis->connect($config["host"], intval($config["port"]));
        }catch (RedisException $e){
            throw new RedisCacheException(sprintf("Redis连接失败，请检查Redis服务：%s:%s，错误信息：%s", $config["host"], $config["port"], $e->getMessage()));
        }

        if (!$boolean) {
            throw new RedisCacheException(sprintf("Redis连接失败，请检查Redis服务：%s:%s", $config["host"], $config["port"]));
        }
        $passwd = $config["password"] ?? null;
        if ($passwd) {
            try {
                $boolean = $this->redis->auth($passwd);
            } catch (RedisException $e) {
                $boolean = false;
            }
            if (!$boolean) {
                throw new RedisCacheException(sprintf("Redis认证失败，请检查Redis密码：%s:%s 密码：%s", $config["host"], $config["port"], $passwd));
            }
        }
    }

    /**
     * 初始化
     * @param int $exp_time
     * @param string $path
     * @return RedisCache|null
     */
    public static function init(int $exp_time = 0, string $path = ''): ?RedisCache
    {
        if ($path == '') $path = Variables::getCachePath();
        $cache = Variables::get("__cache__");
        if ($cache === null) {
            $cache = new self();
            $cache->setData($exp_time, $path);
            Variables::set("__cache__", $cache);
        }

        return $cache;
    }

    /**
     * 设置数据
     */
    function setData(int $exp_time, string $path): CacheInterface
    {
        if($exp_time<=0)$exp_time = null;
        $this->cache_expire = $exp_time;
        $this->cache_path = md5($path) . "_";

        return $this;
    }

    public function del(string $key)
    {
        App::$debug && Log::record("Redis", sprintf("删除缓存：%s", $key));
        $this->redis->del($this->cache_path . $key);
    }

    public function get(string $key): mixed
    {
        App::$debug && Log::record("Redis", sprintf("读取缓存：%s", $key));
        $string = $this->redis->get($this->cache_path . $key);
        if (is_string($string)) {
            return __unserialize($string);
        }
        return null;
    }

    public function empty()
    {
        App::$debug && Log::record("Redis", "清空所有缓存");
        $this->redis->del($this->redis->keys($this->cache_path . "*"));
    }

    public function set(string $key, $data): bool
    {
        App::$debug && Log::record("Redis", sprintf("设置缓存：%s", $key));
        $values = __serialize($data);
        return $this->redis->set($this->cache_path . $key, $values, $this->cache_expire);
    }

    /**
     * 对象销毁，redis关闭
     */
    public function __destruct()
    {
        $this->redis?->close();
    }
}