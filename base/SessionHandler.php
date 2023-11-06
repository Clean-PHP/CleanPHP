<?php

namespace cleanphp\base;

use cleanphp\cache\Cache;
use SessionHandlerInterface;

class SessionHandler implements SessionHandlerInterface
{

    private  Cache $cache;

    public function __construct(&$cache)
    {
        $this->cache = $cache;
    }


    public function close(): bool
    {
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return true;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $result = $this->cache->get($id);
        if($result===null){
            return '';
        }
        return $result;
    }

    public function write(string $id, string $data): bool
    {
        $this->cache->set($id,$data);
        return true;
    }

    public function destroy(string $id): bool
    {
        $this->cache->del($id);
        return true;
    }
    function __destruct()
    {
        session_write_close();
    }
}