<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\useragent
 * Class UserAgent
 * Created By ankio.
 * Date : 2023/7/13
 * Time : 21:08
 * Description :
 */

namespace library\useragent;

use cleanphp\base\Request;

class UserAgent
{
    static function parse($ua): array
    {
        $Os = Os::get($ua);
        $OsImg = self::img("os/", $Os['code'], $Os['title']);
        $OsName = $Os['title'];
        
        $Browser = Browser::get($ua);
        $BrowserImg = self::img("browser/", $Browser['code'], $Browser['title']);
        $BrowserName = $Browser['title'];

        return [
          $OsName,
          $OsImg,
          $BrowserName,
          $BrowserImg
        ];
    }

    private static function img($type, $name, $title) {
        $size = "18px";
        $url_img = Request::getAddress()."/" ;
        $img = "<img nogallery class='icon-ua' src='" . $url_img . $type . $name . ".svg' title='" . $title . "' alt='" . $title . "' height='" . $size . "' style='vertical-align:-2px;margin-right:0.3rem;margin-left:0.3rem' />";
        return $img;
    }
}