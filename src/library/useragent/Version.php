<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\useragent
 * Class Version
 * Created By ankio.
 * Date : 2023/7/13
 * Time : 21:06
 * Description :
 */

namespace library\useragent;

class Version
{
    static function get($ua, $title) {
        // Grab the browser version if it's present
        preg_match('/' . $title . '[\ |\/|\:]?([.0-9a-zA-Z]+)/i', $ua, $regmatch);
        return (is_null($regmatch[1])) ? '' : $regmatch[1];
    }
}