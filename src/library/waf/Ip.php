<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\waf
 * Class ip
 * Created By ankio.
 * Date : 2023/1/1
 * Time : 15:22
 * Description :
 */

namespace library\waf;

use core\file\Log;

class Ip
{
    public string $ip = "";
    public int $count_per_minute = 0;//每分钟请求次数
    public int $last_time = 0;//上次请求时间
    public int $malice_time = 0;//恶意请求次数
    public array $request = [];//ip的恶意请求包
    public int $black_timeout = 0;//小黑屋时间
    public string $reason = "";
    public bool $is_unlock = true;
    public function __construct($ip){
        $this->ip = $ip;
    }

    public function record(): bool
    {
        if($this->black_timeout>time()){
            $this->is_unlock = false;
            return true;
        }else{
            if(!$this->is_unlock){
                $this->is_unlock = true;
                //解封了
                $this->count_per_minute = 0;//解封就重置
            }

        }
        if(time() - $this->last_time <= 60){//非连续访问不认为是攻击
            $this->count_per_minute++;
        }else{
            //访问间隔降低
            $this->count_per_minute=0;
        }
        $this->last_time = time();
        if($this->count_per_minute>120){
            $this->reason = "请求频率过快【友情提醒：根据《中华人民共和国网络安全法》本站已对本次请求进行取证，并保留追究您责任的权利。】";
            Log::record("WAF","每分钟请求超过20个，封禁！",Log::TYPE_WARNING);
            $this->setBlack();
            return false;
        }
        $req = new IpRequest();
        $deny = false;
        foreach ($this->parseRule() as $name => $regex) {
            if (preg_match("/{$regex}/i", urldecode($req->toString()))) {
                $deny = true;//这是恶意请求
                $this->setBlack();
                $this->request[] = $req;
                $this->reason = "存在恶意请求【友情提醒：根据《中华人民共和国网络安全法》本站已对本次请求进行取证，并保留追究您责任的权利。】";
                Log::record("WAF","恶意请求封禁：$name ---> $regex",Log::TYPE_WARNING);
                break;
            }
        }
        return $deny;
    }

    private function setBlack(){
        $this->malice_time++;
        $this->black_timeout = 7200 * $this->malice_time + time();
    }

    private  function parseRule(): array
    {
        $res = [];
        $content = json_decode(file_get_contents(__DIR__.DS."rules".DS."headers.json"));
        foreach ($content as $k => $item) {
            if (is_string($k)) {
                $res[$k] = $this->fixRegex($item);
            } elseif (isset($item[3])) {
                if ($item[0]) $res[$item[2]] =$this->fixRegex($item[1]);
            }
        }
        return $res;
    }

    /**
     * 修正正则表达式
     *
     * @param $regex
     *
     * @return string|string[]|null
     */
    private  function fixRegex($regex)
    {
        return preg_replace_callback('/([\\\\]?\/)/', function ($item) {
            if($item[1] === '/')return '\/';
            return $item[1];
        }, $regex);
    }

}