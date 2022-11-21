<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Class Async
 * Created By ankio.
 * Date : 2022/11/18
 * Time : 18:03
 * Description :
 */

namespace core\process;

use Closure;
use core\App;
use core\base\Error;
use core\base\Request;
use core\base\Response;
use core\base\Variables;
use core\cache\Cache;
use core\event\EventManager;
use core\exception\ExitApp;
use core\file\Log;


class Async
{
    private static bool $in_task = false;

    static function register(){
      EventManager::addListener("__route_end__",AsyncEvent::class);
    }

    /**
     * 启动一个异步任务
     * @param Closure $function 任务函数
     * @param int $timeout 异步任务的最长运行时间,单位为秒
     * @return ?string
     * @throws ExitApp
     */
    static function  start(Closure $function,int $timeout = 300): ?string
    {

        $key = uniqid("async_");

        Log::recordFile("Async","异步任务启动：$key");

        $cache = Cache::init(300,Variables::getCachePath("async",DS));
        $cache->set($key."_function",$function);
        $cache->set($key."_timeout",$timeout);
        $url = url("async","task","start",["key"=>$key]);
        $url_array = parse_url($url);
        $query = [];
        if (isset($url_array["query"]))
            parse_str($url_array["query"], $query);
        $port = intval($_SERVER["SERVER_PORT"]);
        $scheme = Response::getHttpScheme()==="https://"?"ssl://":"";
        $contextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        $context = stream_context_create($contextOptions);
        $fp = stream_socket_client($scheme . $url_array['host'] . ":" . $port, $errno, $err_str, 5, STREAM_CLIENT_CONNECT, $context);
        if ($fp === false) {
            Error::err('异步任务处理失败，可能超出服务器处理上限: ' . $err_str,[],"Async");
            return null;
        }

        if ($query !== [])
            $get_path = $url_array['path'] . "?" . http_build_query($query);
        else
            $get_path = $url_array['path'];

        $header = "GET " . $get_path;
        $header .= " HTTP/1.1" . PHP_EOL;
        $header .= "Host: " . $url_array['host'] . PHP_EOL;
        $token = md5($key);
        $cache->set($token."_token",$key);
        $header .= "Token: " . $token . PHP_EOL;
        $header .= "User-Agent: Async/1.0.0.1 " . PHP_EOL;
        $header .= "Connection: Close" . PHP_EOL;
        $header .=  PHP_EOL;
        fwrite($fp, $header);
        fclose($fp);
        App::$debug && Log::record("Async","异步任务已下发：$key");
        return $key;
    }

    /**
     * 后台运行
     * @param int $time 超时时间
     * @param string $outText
     * @return void
     */
    public static function noWait(int $time = 0, string $outText = "")
    {
        ignore_user_abort(true); // 后台运行，不受前端断开连接影响
        set_time_limit($time);
        ob_end_clean();
        ob_start();
        header("Connection: close");
        header("HTTP/1.1 200 OK");
        if ($outText !== "") {
            echo $outText;
        }
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush();//输出当前缓冲
        flush();
        if (function_exists("fastcgi_finish_request")) {
            fastcgi_finish_request(); /* 响应完成, 关闭连接 */
        }
    }

    /**
     *  响应后台异步请求
     * @return void
     * @throws ExitApp
     */
    public static function response()
    {
        self::$in_task = true;
        if (!self::checkToken($key)) {
            Log::record("Async","Token检查失败！");
            App::exit("您无权访问该资源。");
        }
        $cache = Cache::init(300,Variables::getCachePath("async",DS));
        $function = $cache->get($key."_function");
        $timeout =  $cache->get($key."_timeout");
        $cache->del($key."_function");
        $cache->del($key."_timeout");

        Variables::set("__async_task_id__",$key);
        self::noWait($timeout);
        App::$debug && Log::record("Async","异步任务开始执行");
        $function();
        App::exit("异步任务执行完毕");
    }

    /**
     * 判断当前执行环境是否为异步任务
     * @return bool
     */
    public static function isInTask(): bool
    {
        return self::$in_task;
    }
    /**
     * 进行Token检查
     * @param $task_key
     * @return bool
     */
    private static function checkToken(&$task_key): bool
    {

        if(Request::getClientIP() !== "127.0.0.1") return false;

        $token = Request::getHeaderValue("Token")??"";

        $key = Cache::init(300,Variables::getCachePath("async",DS))->get($token."_token");
        Cache::init(300,Variables::getCachePath("async",DS))->del($token."_token");

        if($key === null||$key!==get("key",""))return false;

        $task_key = $key;

        return true;
    }




}