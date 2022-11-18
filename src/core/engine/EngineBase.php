<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Interface EngineBase
 * Created By ankio.
 * Date : 2022/11/13
 * Time : 00:08
 * Description :
 */

namespace core\engine;

use core\exception\ControllerError;
use core\exception\ExitApp;

interface EngineBase
{
    /**
     * 渲染的输出类型
     * @return string
     */
    function getContentType(): string;

    /**
     * 响应代码
     * @return int
     */
    function getCode(): int;

    /**
     * 渲染数据
     * @param $data
     * @return string
     * @throws ExitApp
     */
    function render(...$data): string;

    /**
     * 跳转提示类
     * @param false $err 是否错误
     * @param int $code 错误代码（200、403、404等）
     * @param string $title 错误标题
     * @param mixed $msg 数据
     * @param int $time 跳转时间
     * @param string $url 跳转URL
     * @param string $desc 跳转描述
     */
    function renderMsg(bool $err = false, int $code = 404, string $title = "",  $msg = "", int $time = 3, string $url = '', string $desc = "立即跳转"): string;

    /**
     * 错误渲染
     * @param string $msg 错误信息
     * @param array $traces 堆栈
     * @param string $dumps 错误发生之前的输出信息
     * @throws ExitApp
     */
    function renderError(string $msg, array $traces, string $dumps,string $tag);
    /**
     * 被设置为默认渲染器的时候
     * @return mixed
     */
    function setByDefault();

    /**
     * 当控制器错误的时候
     * @return bool 返回true表示处理错误，返回false表示不处理
     * @throws ExitApp
     */
    function onControllerError(ControllerError $error): bool;
}