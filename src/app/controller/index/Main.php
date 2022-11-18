<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: app\controller
 * Class Main
 * Created By ankio.
 * Date : 2022/11/11
 * Time : 16:28
 * Description :
 */

namespace app\controller\index;


use core\cache\Cache;
use core\base\Controller;
use core\base\Variables;

class Main extends Controller
{
    public function init()
    {
        //$this->eng()->setLayout('lp');
        Cache::init(3600, Variables::getCachePath("test1"))->set("key1", "123456");

        Cache::init(3600, Variables::getCachePath("test2"))->set("key2", "erwerwerwerr");
        Cache::init(3600, Variables::getCachePath("test3"))->set("key4", "12erwerewrw3456");
        Cache::init(3600, Variables::getCachePath("test4"))->set("key3", ["123ewrwerrwe456", "ssss"]);
        return parent::init();
    }

    function aa()
    {
        dump(Cache::init(3600, Variables::getCachePath("test1"))->get("key1"));
        dump(Cache::init(3600, Variables::getCachePath("test2"))->get("key2"));
        dump(Cache::init(3600, Variables::getCachePath("test3"))->get("key4"));
        dump(Cache::init(3600, Variables::getCachePath("test4"))->get("key3"));
        $this->setContentType("text/html");
        return $this->render("1", "2", "3", "4");
    }


    function ab()
    {
        //new Db(new Mysql());
        return $this->eng()->render("index");
    }


}