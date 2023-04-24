<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * File autoload.php
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 21:13
 * Description :
 */

use cleanphp\base\EventManager;
use library\rbac\RBACListener;

EventManager::addListener("__on_controller_create__", function ($event, &$data) {
    (new RBACListener())->handler($data);
});
