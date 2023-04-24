<?php
/*
 *  Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\mail
 * Class Mail
 * Created By ankio.
 * Date : 2022/11/30
 * Time : 20:58
 * Description :
 */

namespace library\user\wechat;

use cleanphp\base\Config;
use cleanphp\file\Log;
use library\encryption\AESEncrypt;
use library\user\Api;

class Wechat
{
    /**
     * @param string $data 需要发送的内容
     * @return void
     */
    static function send(string $data, string $title)
    {
        Log::record("数据", Config::getConfig('wechat_type'));
        if (Config::getConfig('wechat_type') !== 'api') {
            (new WechatRobot())->markdown($data, $title);
        } else {
            $aes = new AESEncrypt(Api::getInstance()->secretKey);
            Api::getInstance()->request("/api/wechat/send", ["title" => $title, "data" => $aes->encryptWithOpenssl($data)]);
        }
    }
}