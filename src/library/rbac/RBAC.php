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


use cleanphp\base\Session;

class RBAC
{
    /**
     * 设置当前登录用户角色，在用户登录成功的时候进行配置
     * @param $id string 角色id
     * @return void
     */
    public static function setRole(string $id): void
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