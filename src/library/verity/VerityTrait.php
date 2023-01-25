<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * File VerityTrait.php
 * Created By ankio.
 * Date : 2023/1/2
 * Time : 01:29
 * Description :
 */
namespace library\verity;
use core\App;
use core\file\Log;

Trait VerityTrait{
    /**
     * @throws VerityException
     */
    public function onParseType(string $key, &$val, $demo): bool
    {
        if(is_array($val))return false;
        $rules = $this->getRules();

        if(!isset($rules[$key])){
            return false;
        }
        $rule = $rules[$key];

        App::$debug && Log::record('Verity',sprintf(" 规则: %s 验证数据：%s",$rule,$val),Log::TYPE_WARNING);
        if (!empty(strval($val))&&!preg_match('/'.$rule.'/', strval($val))){
            throw new VerityException("字段 $key 验证失败！",$key);
        }
        return false;
    }
}