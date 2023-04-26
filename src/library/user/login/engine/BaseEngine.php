<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace library\user\login\engine;

use cleanphp\base\Request;
use cleanphp\engine\JsonEngine;

abstract class BaseEngine
{

    /**
     * 判断是否登录
     * @return bool
     */
    abstract function isLogin(): bool;

    /**
     * 退出登录
     * @return void
     */
    abstract function logout(): void;

    /**
     * 登录模块进行路由
     * @param $action
     * @return void
     */
    abstract function route($action): void;

    abstract function getLoginUrl($redirect);

    /**
     * 获取设备特征
     * @return string
     */
    function getDevice(): string
    {
        $ip = Request::getHeaderValue('Client-Ip') ?? Request::getClientIP();
        $ua = Request::getHeaderValue('User-Agent') ?? 'NO UA';
        return md5($ip . $ua);
    }

    function renderJson($code, $msg, $data = null): string
    {
        return (new JsonEngine())->render($code, $msg, $data);
    }
}