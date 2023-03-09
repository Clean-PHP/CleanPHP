<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace library\verity;

use core\exception\WarningException;

/**
 * Class Verity
 * Created By ankio.
 * Date : 2022/1/14
 * Time : 10:35 上午
 * Description : 验证类，可以验证常用输入
 */
class VerityRule
{
    const NOT_NULL = 0;
    const NUMBER = "^\d+$";
    const NUMBER_ = "^(-)?\d+$";
    const DOMAIN = "^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$";
    const CHINESE = "^[\u4e00-\u9fa5]{0,}$";
    const ENGLISH_NUMBER = "^[A-Za-z0-9]+$";
    const ENGLISH_NUMBER_ = "^\w+$";
    const CHINA_ENG_NUMBER = '^[\w\d\x{4e00}-\x{9fa5}]+$';
    const CHINA_ENG_NUMBER_DOT = '^[\w\d\x{4e00}-\x{9fa5}\.\-]+$';
    const MAIL = "^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$";
    const PHONE = "^1[1-9][0-9]{9}$";
    const CARD = "^([0-9]){7,18}(x|X)?$";
    const QQ = "^[1-9][0-9]{4,}$";
    const IPV4 = "^\d+\.\d+\.\d+\.\d+$";

    /**
     * 规则校验
     * @param $rule string
     * @param $string string
     * @return false|int
     */
    public static function check(string $rule, string $string)
    {
        try {
            return preg_match('/' . $rule . '/u', $string);
        } catch (WarningException $exception) {
            throw new VerityException("正则匹配出错：$rule", "", $string);
        }
    }

}

