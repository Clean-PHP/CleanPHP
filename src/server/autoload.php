<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * File autoload.php
 * Created By ankio.
 * Date : 2023/3/16
 * Time : 12:26
 * Description :
 */

spl_autoload_register(function ($raw) {
    $real_class = str_replace("\\", DIRECTORY_SEPARATOR, $raw) . ".php";
    //拼接类名文件
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . $real_class;
    //存在就加载
    if (file_exists($file)) {
        include_once $file;
    }
}, true, true);
//注册第三方库的自动加载
