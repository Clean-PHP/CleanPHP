<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class Variables
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 20:56
 * Description :
 */

namespace core\base;


class Variables
{
    /**
     * @var string[] 内部变量
     */
    private static array $inner_arrays = [
        "path_controller" => APP_DIR . DS . 'app' . DS . 'controller' . DS,
        "path_storage" => APP_DIR . DS . 'storage' . DS,
        "path_cache" => APP_DIR . DS . 'storage' . DS . 'cache' . DS,
        "path_logs" => APP_DIR . DS . 'storage' . DS . 'logs' . DS,
        "path_extend" => APP_DIR . DS . 'extend' . DS,
        "path_config" => APP_DIR . DS . 'config' . DS,
        "path_model" => APP_DIR . DS . 'app' . DS . 'model' . DS,
        "path_lib" => APP_DIR . DS . 'library' . DS,
        "path_view" => APP_DIR . DS . 'app' . DS . 'view' . DS,
        "path_public" => APP_DIR . DS . 'public' . DS,
    ];

    /**
     * 获取变量
     * @param string $key
     * @param null $default 默认值
     * @return mixed|null
     */
    public static function get(string $key, $default = null)
    {
        return $GLOBALS[$key] ?? $default;
    }

    /**
     * 设置变量
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value)
    {
        $GLOBALS[$key] = $value;
    }

    /**
     * 删除变量
     * @param string $key
     * @return void
     */
    public static function del(string $key)
    {
        if (isset($GLOBALS[$key])) unset($GLOBALS[$key]);
    }

    /**
     * 获取控制器路径
     * @param string ...$path
     * @return string
     */
    public static function getControllerPath(string ...$path): string
    {
        return self::setPath(self::getInner("path_controller"), ...$path);
    }

    /**
     * 构造路径
     * @param string $start
     * @param ?array $args
     * @return string
     */
    public static function setPath(string $start, ...$args): string
    {
        $ret = '';
        foreach ($args as $k => $v)
            if (is_string($v)) {
                if ($k == 0) {
                    $ret .= $v;
                } else {
                    $ret .= DS . $v;
                }
            }

        return str_replace(DS . DS, DS, $start . DS . $ret);
    }

    /**
     * 获取内部变量
     * @param $key
     * @return string
     */
    private static function getInner($key): string
    {
        return self::$inner_arrays[$key] ?? "";
    }

    /**
     * 获取存储文件的路径
     * @param string ...$path
     * @return string
     */
    public static function getStoragePath(string ...$path): string
    {
        return self::setPath(self::getInner("path_storage"), ...$path);
    }

    /**
     * 获取缓存路径
     * @param string ...$path
     * @return string
     */
    public static function getCachePath(string ...$path): string
    {
        return self::setPath(self::getInner("path_cache"), ...$path);
    }

    /**
     * 获取日志路径
     * @param string ...$path
     * @return string
     */
    public static function getLogPath(string ...$path): string
    {

        return self::setPath(self::getInner("path_logs"), ...$path);
    }

    /**
     * 获取拓展路径
     * @param string ...$path
     * @return string
     */
    public static function getExtendPath(string ...$path): string
    {
        return self::setPath(self::getInner("path_extend"), ...$path);
    }

    /**
     * 获取配置文件路径
     * @param string ...$path
     * @return string
     */
    public static function getConfigPath(string ...$path): string
    {
        return self::setPath(self::getInner("path_config"), ...$path);
    }

    /**
     * 获取模型路径
     * @param string ...$path
     * @return string
     */
    public static function getModelPath(string ...$path): string
    {
        return self::setPath(self::getInner("path_model"), ...$path);
    }

    /**
     * 获取第三方库的路径
     * @param string ...$path
     * @return string
     */
    public static function getLibPath(string ...$path): string
    {
        return self::setPath(self::getInner("path_lib"), ...$path);
    }


    /**
     * 获取视图路径
     * @param string ...$path
     * @return string
     */
    public static function getViewPath(string ...$path): string
    {
        return self::setPath(self::getInner("path_view"), ...$path);
    }


}