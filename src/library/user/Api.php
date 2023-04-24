<?php
/*
 *  Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\user
 * Class BaseApi
 * Created By ankio.
 * Date : 2022/11/30
 * Time : 21:16
 * Description :
 */

namespace library\user;

use cleanphp\base\Config;
use cleanphp\base\Json;
use cleanphp\base\Request;
use cleanphp\file\Log;
use library\http\HttpClient;
use library\http\HttpException;
use library\user\login\Sign;

class Api
{
    private static $instance = null;
    public string $url = "";
    public string $appId = "";
    public string $secretKey = "";


    public function __construct()
    {
        $config = Config::getConfig('sso');
        if (!empty($config)) {
            $this->url = $config['url'];
            $this->appId = $config['appId'];
            $this->secretKey = $config['secretKey'];
        }
    }

    static function getInstance(): ?Api
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function request($url, $data = [])
    {
        try {
            $headers = [
                'Client-Ip' => Request::getClientIP(),
                'User-Agent' => Request::getHeaderValue('User-Agent') ?? 'NO UA'
            ];
            $data['t'] = time();
            $data['appid'] = $this->appId;
            Log::record('Sign', json_encode($data));
            $response = HttpClient::init($this->url)->setHeaders($headers)->post(Sign::sign($data, $this->secretKey), 'form')->send($url);
            Log::record('API', $response->getBody());
            return Json::decode($response->getBody(), true);
        } catch (HttpException $e) {
            Log::record("API", $e->getMessage(), Log::TYPE_ERROR);
            return ['code' => 500, 'msg' => '服务器错误'];
        }
    }
}