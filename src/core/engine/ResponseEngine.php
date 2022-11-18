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

use core\exception\ControllerError;
use core\base\Response;
use core\exception\ExitApp;

abstract class ResponseEngine implements EngineBase
{


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

    public function onControllerError(ControllerError $error): bool
    {
        //默认不处理错误
        return false;
    }

    /**
     * 当被设置为默认渲染器的时候监听事件
     * @return void
     */
    function setByDefault()
    {

    }


}