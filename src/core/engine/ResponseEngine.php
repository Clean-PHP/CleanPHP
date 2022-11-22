<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\engine
 * Class ResponseEngine
 * Created By ankio.
 * Date : 2022/11/11
 * Time : 17:30
 * Description :
 */

namespace core\engine;

use core\base\Controller;
use core\base\Response;
use core\exception\ExitApp;

abstract class ResponseEngine
{

    /**
     * 渲染的输出类型
     * @return string
     */
    abstract function getContentType(): string;

    /**
     * 响应代码
     * @return int
     */
    abstract function getCode(): int;

    /**
     * 渲染数据
     * @param $data
     * @return string
     */
    abstract function render(...$data): string;


    /**
     * 错误渲染
     * @param string $msg 错误信息
     * @param array $traces 堆栈
     * @param string $dumps 错误发生之前的输出信息
     * @throws ExitApp
     */
    abstract function renderError(string $msg, array $traces, string $dumps, string $tag);

    /**
     * 被设置为默认渲染器的时候
     */
    function setByDefault()
    {

    }


    /**
     * @throws ExitApp
     */
    function renderMsg(bool $err = false, int $code = 404, string $title = "", $msg = "", int $time = 3, string $url = '', string $desc = "立即跳转"): string
    {
        if ($time == 0) {
            Response::location($url);
        }
        return "";
    }

    /**
     * 当控制器错误的时候
     * @return string|null
     */
    public function onControllerError(): ?string
    {
        //默认不处理错误
        return null;
    }


}