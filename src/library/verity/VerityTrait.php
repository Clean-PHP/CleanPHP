<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

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
use core\objects\StringBuilder;

trait VerityTrait
{
    /**
     * @throws VerityException
     */
    public function onParseType(string $key, &$val, $demo): bool
    {
        if (!is_string($val)) return false;
        $rules = $this->getRules();
        if (!isset($rules[$key])) {
            $rule = null;
            foreach ($rules as $k => $v) {
                if ((new StringBuilder())->contains("|")) {
                    foreach (explode("|", $k) as $vv) {
                        if ($vv === $key) {
                            $rule = $v;
                            break;
                        }
                    }
                    if (!empty($rule)) break;
                }
            }
            if (empty($rule)) return false;
        } else {
            $rule = $rules[$key];
        }

        $msg = "字段 $key 验证失败！";
        $allow_empty = true;
        if (is_array($rule)) {
            $r = $rule;
            $rule = $r[0];
            if (sizeof($r) > 1) {
                $msg = $r[1];
                if (sizeof($r) > 2 && $r[2]) {
                    $allow_empty = false;
                }
            }
        }

        App::$debug && Log::record('Verity', sprintf(" 规则: %s 验证数据：%s", $rule, $val), Log::TYPE_WARNING);

        //检查空值
        if (($rule === VerityRule::NOT_NULL || !$allow_empty) && empty($val)) {
            throw new VerityException($msg, $key, $val);
        }
        //空值不验证,未曾通过校验直接抛异常
        if (!empty(strval($val)) && $val !== $demo && !VerityRule::check($rule, $val)) {
            throw new VerityException($msg, $key, $val);
        }
        return false;
    }
}