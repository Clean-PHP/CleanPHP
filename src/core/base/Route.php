<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

namespace core\base;


use core\App;
use core\config\Config;
use core\event\EventManager;
use core\exception\ExitApp;
use core\file\Log;
use core\objects\StringBuilder;

/**
 * Class Route
 * @package core\web
 * Date: 2020/11/22 11:24 下午
 * Author: ankio
 * Description:路由类
 */
class Route
{
    /**
     * 路由URL生成
     * @param string $m 模块
     * @param string $c 控制器
     * @param string $a 执行方法
     * @param array $params 附加参数
     * @return string
     */
    public static function url(string $m, string $c, string $a, array $params = []): string
    {
        $is_rewrite = Config::getConfig("frame")["rewrite"]??false;

        $route = "$m/$c/$a";

        $param_str = empty($params) ? '' : '?' . http_build_query($params);


        $url = Request::getAddress() . "/";

        $default = $url . $route;
        $default = strtolower($default) . $param_str;


        $array = str_replace("<m>", $m, Config::getRouteTable());
        $array = str_replace("<c>", $c, $array);
        $array = str_replace("<a>", $a, $array);
        $array = array_flip(array_unique($array));

        //var_dump($array,$route);
        $route_find = $route;
        if (isset($array[$route])) {
            //处理参数部分
            $route_find = $array[$route];
            $route_find = str_replace("<m>", $m, $route_find);
            $route_find = str_replace("<c>", $c, $route_find);
            $route_find = str_replace("<a>", $a, $route_find);


            foreach ($params as $key => $val) {
                if (strpos($route_find, "<$key>") !== false) {
                    $route_find = str_replace("<$key>", $val, $route_find);
                    unset($params[$key]);
                }

            }
        }

        if(!$is_rewrite){
            $params['s'] = $route_find;
            $route_find = "";
        }
        if ($route_find === $route || strpos($route_find, '<') !== false) {
            $ret_url = $default;
        } else {

            $param_str = empty($params) ? '' : '?' . http_build_query($params);
            $ret_url = $url . $route_find . $param_str;
        }

        if (strrpos($ret_url, "?") === strlen($ret_url) - 1) {
            return substr($ret_url, 0, strlen($ret_url) - 1);
        }
        return $ret_url;

    }

    /**
     * 路由重写
     * @throws ExitApp
     */
    public static function rewrite(): array
    {
        App::$debug && Variables::set('__route_start__', microtime(true));

        $url = self::getQuery();

        self::beforeRoute($url);

        EventManager::trigger("__route_before__", $url);//路由之前

        $array = self::parseUrl($url);

        if (!isset($array['m']) || !isset($array['a']) || !isset($array['c'])) {
            Error::err("路由不完整，缺少模块或控制器或执行方法！",[],"Route");
        }


        EventManager::trigger("__route_end__", $array);//路由之后

        $__module = $array['m'];
        $__controller = ($array['c']);
        $__action = ($array['a']);


        App::$debug && Log::record("Route", sprintf("路由结果：%s/%s/%s", $__module,$__controller,$__action));

        unset($array['m']);
        unset($array['c']);
        unset($array['a']);

        $_GET = array_merge($_GET, $array);

        App::$debug && Log::record("Route", sprintf("路由总耗时：%s 毫秒", round((microtime(true) - Variables::get("__route_start__", 0)) * 1000, 2)),Log::TYPE_WARNING);

        return [$__module, $__controller, $__action];
    }

    /**
     * 获取路径
     * @return string
     */
    private static function getQuery(): string
    {
        $query = Variables::get("__route_query__");
        if ($query === null) {
            $is_rewrite = Config::getConfig("frame")["rewrite"];
            if ($is_rewrite) {
                $query = $_SERVER['REQUEST_URI'] ?? "/";
            } else {
                $query = "/";
                if (isset($_GET['s'])) {
                    $query = $_GET['s'];
                    unset($_GET['s']);
                }
            }
            if (($index = strpos($query, '?')) !== false) {
                $query = substr($query, 0, $index);
            }
            $query = strtolower(trim($query, "/"));
            if ($query === "") $query = "/";
            Variables::set("__route_query__", $query);
        }

        return $query;
    }

    /**
     * 事件
     * @throws ExitApp
     */
    private static function beforeRoute($data)
    {
        if ((new StringBuilder($data))->startsWith('clean_static')) {
            $uri = str_replace('clean_static', "", $data);
            $path = Variables::setPath(APP_DIR,'app', "public", str_replace("..", ".", $uri));
           self::renderStatic($path);
        }
    }

    /**
     * @throws ExitApp
     */
    public static function renderStatic($path){
        if (file_exists($path)) {
            $type = file_type($path);
            //\dump($type,true);
            (new Response())->render(self::replaceStatic(file_get_contents($path)), 200,$type)->send();
        } else {
            Error::err(sprintf("找不到指定的静态资源：%s",$path),[],"Route");
        }
    }

    /**
     * 替换静态文件
     * @param string $content
     * @return string|string[]
     */
    public static function replaceStatic(string $content){
        $is_rewrite = Config::getConfig("frame")["rewrite"];
        $replaces = Variables::get("__static_replace__","../../public");
        if($is_rewrite)
            $template_data = str_replace($replaces,"/clean_static",$content);
        else{
            $template_data = str_replace($replaces,"/?s=clean_static",$content);
        }

        return $template_data;
    }

    /**
     * 路由匹配
     * @param string|null $query
     * @return array
     */
    public static function parseUrl(string $query = null): array
    {

        $array = [];
        if ($query === null) {
            $query = self::getQuery();
        }
        App::$debug && Log::record("Route", sprintf("路由地址：%s", $query));
        //修改匹配
        ini_set('pcre.recursion_limit', 200);

        foreach (Config::getRouteTable() as $_rule => $mapper) {
            empty($_rule) && $_rule = "/";
            $rule = strtolower($_rule);
            $rule = '@^' . str_ireplace(
                    ['\\\\', '/', '<', '>', '.'],
                    ['', '\/', '(?P<', '>[\x{4e00}-\x{9fa5}a-zA-Z0-9_\.-\/]+)', '\.'],
                    $rule)
                . '$@u';
            if (preg_match($rule, $query, $matches)) {
                $route = explode("/", trim($mapper));
                if (isset($route[2])) {
                    [$array["m"], $array["c"], $array["a"]] = $route;
                }
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) $array[$k] = $v;
                }
                break;
            }
        }

        return $array;
    }



}





