<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace cleanphp\base;


use cleanphp\cache\Cache;
use SessionHandlerInterface;
use SessionIdInterface;

/**
 * Class Session
 * @package cleanphp\web
 * Date: 2020/11/29 12:24 上午
 * Author: ankio
 * Description:Session操作类
 */
class Session
{
    private static ?Session $instance = null;
    private static bool $isStart = false;

    private static Cache $cache;
    /**
     * 获取实例
     * @return Session
     */
    public static function getInstance(): Session
    {
        if (is_null(self::$instance)) {
            self::$instance = new Session();

        }

        return self::$instance;
    }


    /**
     * 启动session
     * @param int $cacheTime Session缓存时间，默认会话有效
     * @return void
     */
    public function start(int $cacheTime = 0, string $sessionName = 'PHPSESSID'): void
    {
        if (self::$isStart) {
            return; // 如果已经启动会话，直接返回
        }

        // 获取配置项
        $sessionConfig = Config::getConfig("frame")["session"] ?? $sessionName;

        // 设置会话名称
        ini_set("session.name", $sessionConfig);

        if ($cacheTime !== 0) {
            // 设置会话的最大生存时间和Cookie参数
            ini_set('session.gc_maxlifetime', $cacheTime);
        }
        self::$cache = Cache::init($cacheTime,Variables::getCachePath("session",DS));
        session_set_save_handler(new SessionHandler(self::$cache), true);
        session_set_cookie_params($cacheTime, '/',null,true,true);
        // 启动会话
        session_start();
        self::$isStart = true;
    }


    /**
     * 获取sessionId
     * @return string
     */
    public function id(): string
    {
        return session_id();
    }



    /**
     * 设置session
     * @param string $name session名称
     * @param mixed $value
     * @param int $expire 过期时间,单位秒
     */
    public function set(string $name, mixed $value, int $expire = 0): void
    {

        if ($expire != 0) {
            $expire = time() + $expire;
            $_SESSION[$name . "_expire"] = $expire;

        }
        $_SESSION[$name] = __serialize($value);

    }


    /**
     * 获取session
     * @param string $name 要获取的session名
     * @param mixed|null $default 默认值
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if (!isset($_SESSION[$name])) {
            return $default;
        }
        $value = $_SESSION[$name];
        if (!isset($_SESSION[$name . "_expire"])) {
            return __unserialize($value);
        }
        $expire = $_SESSION[$name . "_expire"];
        if ($expire == 0 || $expire > time()) {
            return __unserialize($value);
        } else {
            //超时后销毁变量
            unset($_SESSION[$name]);
            unset($_SESSION[$name . "_expire"]);
        }
        return null;
    }


    /**
     * 删除session
     * @param string $name 要删除的session名称
     */
    public function delete(string $name): void
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
        if (isset($_SESSION[$name . "_expire"])) {
            unset($_SESSION[$name . "_expire"]);
        }
    }

    public function destroy(): void
    {
       session_destroy();
    }
    function __destruct()
    {
        session_write_close();
    }
}