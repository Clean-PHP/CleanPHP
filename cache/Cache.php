<?php
/*
 * Package: cleanphp\cache
 * Class Cache
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 21:24
 * Description :
 */

namespace cleanphp\cache;

use cleanphp\App;
use cleanphp\base\Variables;
use cleanphp\file\File;
use cleanphp\file\Log;

class Cache implements CacheInterface
{
    private static ?CacheInterface $drive = null;
    private static array $cache = [];
    private string $cache_path = "";
    private int $cache_expire = 3600;

    public static function setDriver(CacheInterface $drive): void
    {
        Variables::del("__cache__"); // 以前的对象需要释放
        App::$debug && Log::record("Cache", sprintf("设置缓存器：%s", get_class($drive)));
        self::$drive = $drive;
    }

    public function del(string $key): void
    {
        $filename = $this->fileName($key);
        File::del($filename);
    }

    private function fileName(string $key): string
    {
        $new = filter_characters($key) . "_" . md5($key);
        if (mb_strlen($new) > 100) {
            $new = mb_substr($new, 0, 50) . md5($key);
        }
        return $this->cache_path . $new;
    }

    public static function init(int $exp_time = 0, ?string $path = null): ?Cache
    {
        $path = $path ?? Variables::getCachePath();
        if (self::$drive) {
            return self::$drive::init($exp_time, $path);
        }

        if (!isset(self::$cache[$path])) {
            self::$cache[$path] = new self();
        }

        if (self::$cache[$path]->cache_expire !== $exp_time || self::$cache[$path]->cache_path !== $path) {
            self::$cache[$path]->setData($exp_time, $path);
        }
        return self::$cache[$path];
    }

    public function setData(int $exp_time, string $path): CacheInterface
    {
        if ($this->cache_path === $path && $this->cache_expire === $exp_time) {
            return $this;
        }

        $this->cache_expire = $exp_time;
        $this->cache_path = $path;

        File::mkDir($this->cache_path);

        return $this;
    }

    public function get(string $key): mixed
    {
        $filename = $this->fileName($key);
        if (!is_file($filename)) {
            return null;
        }

        if ($this->cache_expire === 0 || time() < (filemtime($filename) + $this->cache_expire)) {
            $data = file_get_contents($filename);
            if ($data === false) {
                return null;
            }
            $result = __unserialize($data);

            if ($result === false) {
                return null;
            }

            return $result;
        } else {
            $this->del($key);
            return null;
        }
    }

    public function set(string $key, mixed $data): void
    {
        file_put_contents($this->fileName($key), __serialize($data));
    }

    public function empty(): void
    {
        App::$debug && Log::record("Cache", "清空所有缓存");
        File::empty($this->cache_path);
    }

    public function emptyPath($path): void
    {
        foreach (scandir($this->cache_path) as $item) {
            if (str_starts_with($item, ".")) {
                continue;
            }
            if (str_contains($item, $path)) {
                File::del($this->cache_path . $item);
            }
        }
    }
}
