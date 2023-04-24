<?php
/*
 *  Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package=>app\utils
 * Class WechatRobot
 * Created By ankio.
 * Date=> 2022/11/26
 * Time=> 23:38
 * Description=>
 */

namespace library\user\wechat;

use cleanphp\base\Config;
use cleanphp\base\Json;
use cleanphp\file\Log;
use library\http\HttpClient;
use library\http\HttpException;

class WechatRobot
{

    private $access_token = "";

    public function __construct()
    {
        $http = HttpClient::init("https://qyapi.weixin.qq.com");
        try {
            $token = $http->get()->send(sprintf("/cgi-bin/gettoken?corpid=%s&corpsecret=%s", Config::getConfig('wechat_corpid'), Config::getConfig('wechat_corpsecret')));
            $json = Json::decode($token->getBody(), true);
            if (isset($json["access_token"])) {
                $this->access_token = $json["access_token"];
            }
        } catch (HttpException $e) {
            Log::record("http", $e->getMessage(), Log::TYPE_ERROR);
        }
    }


    public function markdown($text, $title)
    {
        $push = mb_substr($text, 0, 2048);
        if (Config::getConfig('wechat_type_receive') !== "markdown") {
            $push = strip_tags(Parsedown::instance()->parse($push), "<a>");

            $push = $title . "\n------------\n" . $push;

            try {
                HttpClient::init("https://qyapi.weixin.qq.com")->post([
                    "touser" => "@all",
                    "toparty" => "",
                    "totag" => "",
                    "msgtype" => "text",
                    "agentid" => Config::getConfig("wechat_agentid"),
                    "text" => [
                        "content" => $push
                    ],
                    "safe" => 0,
                    "enable_id_trans" => 0,
                    "enable_duplicate_check" => 0,
                    "duplicate_check_interval" => 1800
                ])->send("/cgi-bin/message/send", ['access_token' => $this->access_token]);
            } catch (HttpException $e) {
                Log::record("http", $e->getMessage(), Log::TYPE_ERROR);
            }
        } else {
            $push = "### " . $title . " \n\n" . $push;
            try {
                HttpClient::init("https://qyapi.weixin.qq.com")->post([
                    "touser" => "@all",
                    "toparty" => "",
                    "totag" => "",
                    "msgtype" => "markdown",
                    "agentid" => Config::getConfig("wechat_agentid"),
                    "markdown" => [
                        "content" => $push
                    ],
                    "enable_duplicate_check" => 0,
                    "duplicate_check_interval" => 1800
                ])->send("/cgi-bin/message/send", ['access_token' => $this->access_token]);
            } catch (HttpException $e) {
                Log::record("http", $e->getMessage(), Log::TYPE_ERROR);
            }
        }

    }

}