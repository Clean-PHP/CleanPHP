<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * File autoload.php
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 21:13
 * Description :
 */
\core\event\EventManager::addListener("__on_controller_create__",\library\rbac\RBACEvent::class);
