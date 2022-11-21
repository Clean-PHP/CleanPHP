<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/
namespace library\verity;
/**
 * Class Verity
 * Created By ankio.
 * Date : 2022/1/14
 * Time : 10:35 上午
 * Description : 验证类，可以验证常用输入
 */
class VerityRule
{
    const NUMBER = "^[0-9]*$";
    const CHINESE = "^[\u4e00-\u9fa5]{0,}$";
    const ENGLISH_NUMBER = "^[A-Za-z0-9]+$";
    const ENGLISH_NUMBER_ = "^\w+$";
    const MAIL = "^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$";
    const PHONE = "^1[1-9][0-9]{9}$";
    const CARD  = "^([0-9]){7,18}(x|X)?$";
    const QQ = "[1-9][0-9]{4,}";
    const IP = "\d+\.\d+\.\d+\.\d+";
    /**
     * 规则校验
     * @param $rule string
     * @param $string string
     * @return false|int
     */
    public function check(string $rule, string $string){
        return preg_match('/'.$rule.'/', $string);
    }

}

