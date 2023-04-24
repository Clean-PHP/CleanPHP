<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace library\user\login\engine;

use cleanphp\base\EventManager;
use cleanphp\base\Json;
use cleanphp\base\Request;
use cleanphp\base\Response;
use cleanphp\base\Session;
use cleanphp\file\Log;
use library\user\Api;
use library\user\login\captcha\Captcha;
use library\user\login\objects\CallbackObject;
use library\user\login\objects\LoginSession;
use library\user\login\Sign;
use library\verity\VerityException;


class SSO extends BaseEngine
{
    function route($action): void
    {
        // dump($action,true);
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
            case 'logout':
            {
                $this->logout();
                $result = $this->renderJson(200, '成功退出');
                break;
            }
            case 'callback':
            {
                try {
                    $object = new CallbackObject(arg(), Api::getInstance()->secretKey);
                    $result = $this->callback($object);
                    if ($result === true) {
                        EventManager::trigger("__login_success__");
                        Response::location($object->redirect);
                    }
                } catch (VerityException $e) {
                    $result = $this->renderJson(403, $e->getMessage());
                }
                //进行回调
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
        if ($__login_data__->device === $this->getDevice() && $__login_data__->refresh_time + 60 > time()) {
            return true;
        }
        $data = $this->request('api/login/islogin', ['token' => $__login_data__->token]);

        if ($data['code'] === 200) {
            $__login_data__->refresh_time = time();
            Session::getInstance()->set('__login_data__', $__login_data__, time() + 600);
            return true;
        } else {
            return false;
        }
    }

    //检查是否登录，1分钟检查一次，频率过高会导致cc攻击

    private function request($url, $data = [])
    {
        return Api::getInstance()->request($url, $data);
    }

    function logout(): void
    {
        /**
         * @var LoginSession $__login_data__
         */
        $__login_data__ = Session::getInstance()->get('__login_data__');
        if (!empty($__login_data__)) {
            $this->request('api/login/logout', ['token' => $__login_data__->token]);
        }
        Session::getInstance()->destroy();
    }

    private function callback(CallbackObject $object)
    {
        $result = $this->request('api/login/replace', ['code' => $object->code]);
        Log::record('SSO', Json::encode($result));
        if (isset($result['code']) && $result['code'] === 200) {
            $__login_data__ = new LoginSession(['device' => $this->getDevice(), 'token' => $result['data']['token'], 'refresh_time' => time()]);
            $__login_data__->extra = $result['data'];
            Session::getInstance()->set('__login_data__', $__login_data__, time() + 600);
            return true;
        } else {
            return $result['msg'];
        }
    }

    /**
     * 获取登录地址
     * @param string $redirect
     * @return string
     */
    function getLoginUrl($redirect = null): string
    {
        $url = Sign::sign([
            'ts' => time(),
            'id' => Api::getInstance()->appId,
            'host' => Response::getHttpScheme() . Request::getDomain(),
            'redirect' => $redirect,
            't' => 'fingerprint'
        ], Api::getInstance()->secretKey);
        return Api::getInstance()->url . '?' . http_build_query($url);
    }
}