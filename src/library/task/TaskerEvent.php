<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\task
 * Class TaskerEvent
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 19:39
 * Description :
 */

namespace library\task;

use core\event\EventListener;

class TaskerEvent implements EventListener
{

    public function handleEvent(string $event, &$data)
    {
        TaskerServer::start();
    }
}