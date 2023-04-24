<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\login
 * Class Login
 * Created By ankio.
 * Date : 2022/11/28
 * Time : 16:48
 * Description :
 */

namespace library\user\login;

use cleanphp\base\Config;
use cleanphp\base\Session;
use library\user\login\engine\BaseEngine;
use library\user\login\engine\Password;
use library\user\login\objects\LoginSession;

class Login
{

    private static ?BaseEngine $instance = null;


    static function route($action)
    {
        Session::getInstance()->start();
        self::getInstance()->route($action);
    }

    static private function getInstance()
    {
        $class = "library\\user\\login\\engine\\" . Config::getConfig('login_type');
        if (empty(self::$instance)) {
            if (class_exists($class)) {
                self::$instance = new $class();
            } else {
                self::$instance = new Password();
            }
        }
        return self::$instance;
    }

    static function isLogin(): bool
    {
        return self::getInstance()->isLogin();
    }

    static function logout()
    {
        self::getInstance()->logout();
    }

    static function getLoginUrl($redirect)
    {
        return self::getInstance()->getLoginUrl($redirect);
    }

    /**
     * @return ?LoginSession
     */
    static function getLoginData(): ?LoginSession
    {
        return Session::getInstance()->get('__login_data__');
    }


}