<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/
declare(strict_types=1);
ignore_user_abort(true);

use cleanphp\App;

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    exit("请使用PHP 8.0 以上版本运行该应用");
}
define('APP_DIR', dirname(__FILE__, 2));//定义运行根目录
require_once APP_DIR . DIRECTORY_SEPARATOR . "cleanphp" . DIRECTORY_SEPARATOR . "App.php";
App::run(true);