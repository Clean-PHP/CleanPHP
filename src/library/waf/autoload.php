<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * File autoload.php
 * Created By ankio.
 * Date : 2022/11/26
 * Time : 15:46
 * Description :
 */

use core\event\EventManager;

if(\core\config\Config::getConfig("waf")){
    EventManager::addListener("__frame_init__", \library\waf\Waf::class);
}
