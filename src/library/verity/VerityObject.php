<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\verity
 * Class VerityObject
 * Created By ankio.
 * Date : 2022/11/21
 * Time : 00:01
 * Description :
 */

namespace library\verity;

use core\objects\ArgObject;

abstract class VerityObject extends ArgObject
{

    /**
     * 获取匹配规则
     * @return array
     */
    abstract function getRules():array;

    /**
     * @throws VerityException
     */
    public function onParseType(string $key, &$val, $demo): bool
    {
        $rules = $this->getRules();
        if(!isset($rules[$key])){
            return false;
        }
        $rule = $rules[$key];
        if (!preg_match($rule, strval($val))){
            throw new VerityException("字段 $key 验证失败！");
        }
       return false;
    }
}