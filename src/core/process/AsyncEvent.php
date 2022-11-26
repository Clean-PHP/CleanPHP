<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: core\process
 * Class AsyncEvent
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 16:08
 * Description :
 */

namespace core\process;

use core\base\Variables;
use core\event\EventListener;
use core\exception\ExitApp;

class AsyncEvent implements EventListener
{

    /**
     * @inheritDoc
     * @throws ExitApp
     */
    public function handleEvent(string $event, &$data)
    {
        $array = $data;
        if($array["m"] === "async" && $array["c"] === "task" && $array["a"] === "start"){
            Variables::set("__frame_log_tag__", "async_");
            Async::response();
        }
    }
}