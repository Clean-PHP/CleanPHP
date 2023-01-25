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

use library\language\Lang;

Lang::register();
if(!function_exists("lang")){
    /**
     * 输出语言
     * @param string $str 语言名称
     * @param ...$args
     * @return string
     */
    function lang(string $str, ...$args): string
    {
        return Lang::get($str, ...$args);
    }
}
