<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: extend\ankioTask\core
 * Class RBAC
 * Created By ankio.
 * Date : 2022/5/5
 * Time : 17:19
 * Description :
 */

namespace library\rbac;


use core\base\Session;

class RBAC
{
    /**
     * 给当前登录用户角色
     * @param $id string 角色id
     * @return void
     */
    public static function setRole(string $id)
    {
        Session::getInstance()->set("RBAC_ROLE", $id);
    }

    /**
     * 获取当前登录用户角色
     * @return string|null
     */
    public static function getRole(): ?string
    {
        Session::getInstance()->start();
        return Session::getInstance()->get("RBAC_ROLE") ?? "-1";
    }

}