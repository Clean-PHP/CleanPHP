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

use core\event\EventManager;
use library\rbac\RBACEvent;

EventManager::addListener("__on_controller_create__", RBACEvent::class);
