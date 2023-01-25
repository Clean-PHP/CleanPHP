<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\captcha
 * Class Tencent
 * Created By ankio.
 * Date : 2022/11/30
 * Time : 17:42
 * Description :
 */

namespace library\captcha;

class Tencent
{
    function check($ticket,$rand): bool
    {
        return $this->check_ticket($ticket, $rand) === 1;
    }
    private function check_ticket($ticket, $rand): int
    {
        $url = 'https://cgi.urlsec.qq.com/index.php?m=check&a=gw_check&callback=url_query&url=https%3A%2F%2Fwww.qq.com%2F'.rand(111111,999999).'&ticket='.urlencode($ticket).'&randstr='.urlencode($rand);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $http_header[] = "Accept: application/json";
        $http_header[] = "Accept-Language: zh-CN,zh;q=0.8";
        $http_header[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        curl_setopt($ch, CURLOPT_REFERER, 'https://urlsec.qq.com/check.html');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);

        $arr = $this->jsonp_decode($ret, true);
        if(isset($arr['reCode']) && $arr['reCode'] == 0){ //验证通过
            return 1;
        }elseif(isset($arr['reCode']) && $arr['reCode'] == -109){ //验证码错误
            return 0;
        }else{ //接口已失效
            return -1;
        }
    }
    private function jsonp_decode($jsonp, $assoc = false)
    {
        $jsonp = trim($jsonp);
        if(isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $begin = strpos($jsonp, '(');
            if(false !== $begin)
            {
                $end = strrpos($jsonp, ')');
                if(false !== $end)
                {
                    $jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
                }
            }
        }
        return json_decode($jsonp, $assoc);
    }
}