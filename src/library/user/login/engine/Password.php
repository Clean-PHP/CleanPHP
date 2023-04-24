<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\login
 * Class Password
 * Created By ankio.
 * Date : 2022/11/26
 * Time : 15:46
 * Description :
 */

namespace library\user\login\engine;


use cleanphp\App;
use cleanphp\base\Config;
use cleanphp\base\EventManager;
use cleanphp\base\Request;
use cleanphp\base\Response;
use cleanphp\base\Session;
use cleanphp\base\Variables;
use cleanphp\cache\Cache;
use cleanphp\file\File;
use cleanphp\file\Log;
use library\encryption\EncryptionException;
use library\encryption\RSAEncrypt;
use library\user\login\captcha\Captcha;
use library\user\login\objects\LoginSession;

class Password extends BaseEngine
{

    function route($action): void
    {
        $content_type = 'text/html';
        $result = $this->renderJson(401, '未登录');
        switch (strtolower($action)) {
            case 'islogin':
            {
                if ($this->isLogin()) {
                    $result = $this->renderJson(401, '未登录');
                } else {
                    $result = $this->renderJson(200, '已登录');
                }
                break;
            }
            case 'login':
            {
                if (!$this->login()) {
                    $result = $this->renderJson(401, '登录失败');
                } else {
                    $result = $this->renderJson(200, '登录成功');
                }
                break;
            }
            case 'change':
            {
                if (!$this->change()) {
                    $result = $this->renderJson(401, '修改失败');
                } else {
                    $result = $this->renderJson(200, '修改成功');
                }
                break;
            }
            case 'logout':
            {
                $this->logout();
                $result = $this->renderJson(200, '成功退出');
                break;
            }
            case 'key':
            {
                $result = $this->renderJson(200, '获取成功', $this->publicKey());
                break;
            }
            case 'captcha':
            {
                (new Captcha())->create('login');
                break;
            }

        }
        (new Response())->render($result, 200, $content_type)->send();
    }

    function isLogin(): bool
    {
        /**
         * @var LoginSession $__login_data__
         */
        $__login_data__ = Session::getInstance()->get('__login_data__');
        if (empty($__login_data__)) return false;
        /**
         * @var $login2 LoginSession
         */
        $login2 = Cache::init()->get('__login_data__');
        if (empty($login2)) {
            $this->logout();
            return false;
        }
        //设备id不一致或者token不一致则表示没有登录
        if ($login2->device !== $__login_data__->device || $login2->token !== $__login_data__->token) {
            $this->logout();
            return false;
        }

        return true;
    }

    function logout(): void
    {
        Session::getInstance()->destroy();
        Cache::init()->del('login');
    }

    function login(): bool
    {
        if (!(new Captcha())->verify('login', arg("code"))) {
            return false;
        }
        $public = Variables::getStoragePath("key", "public.key");
        $private = Variables::getStoragePath("key", "private.key");
        $rsa = new RSAEncrypt();
        try {
            $rsa->initRSAPath($private, $public);
        } catch (EncryptionException $e) {
            App::$debug && Log::record('Encrypt', $e->getMessage(), Log::TYPE_ERROR);
            return false;
        }
        $passwd = $rsa->rsaPrivateDecrypt(arg("password"));
        $user = arg("username");
        $data = Config::getConfig('login');
        $hash = md5($data["username"] . $passwd);
        if (md5($data["username"] . $passwd) === $data["password"] && $user === $data["username"]) {
            $timeout = time() + 3600 * 24;
            $token = sha1($hash . md5($timeout));

            $ua = Request::getHeaderValue('User-Agent') ?? 'NO UA';
            $device = sha1(Request::getClientIP() . $ua);

            $login = new LoginSession(['token' => $token, 'id' => 0, 'device' => $device]);
            $login->extra = $user;
            Session::getInstance()->set('__login_data__', $login);
            Cache::init()->set('__login_data__', $login);
            EventManager::trigger("__login_success__");
            return true;
        }
        return false;
    }

    function change(): bool
    {
        $username = arg("username");
        $old = arg("old");
        $new = arg("new");
        $data = Config::getConfig('login');
        if (md5($data["username"] . $old) === $data["password"]) {
            Cache::init()->del("token");
            Session::getInstance()->destroy();
            $data["password"] = md5($username . $new);
            $data["username"] = $username;
            $all = Config::getConfig();
            $all['login'] = $data;
            Config::getInstance('config')->setAll($all);
            return true;
        }
        return false;
    }

    /**
     * 获取加密公钥
     * @return string
     */
    function publicKey(): string
    {
        $public = Variables::getStoragePath("key", "public.key");
        $private = Variables::getStoragePath("key", "private.key");
        if (is_file($public) && is_file($private)) {
            return file_get_contents($public);
        } else {
            $rsa = new RSAEncrypt();
            $rsa->create();
            $keys = $rsa->getKey();
            File::mkDir(Variables::getStoragePath("key"));
            file_put_contents($public, $keys["public_key"]);
            file_put_contents($private, $keys["private_key"]);
            return $keys["public_key"];
        }
    }

    function getLoginUrl($redirect = null)
    {
        return null;
    }
}