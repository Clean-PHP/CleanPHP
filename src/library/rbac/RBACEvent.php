<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\rbac
 * Class RBACEvent
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 21:14
 * Description :
 */

namespace library\rbac;


use core\base\Controller;
use core\base\Response;
use core\event\EventListener;
use core\event\EventManager;
use core\file\Log;
use core\objects\StringBuilder;

class RBACEvent implements EventListener
{

    /**
     * @param string $event
     * @param Controller $data
     * @return void|null
     */
    public function handleEvent(string $event, &$data)
    {

        $role_data = Role::init()->get(RBAC::getRole());
        //没有角色信息，就默认可以执行
        if (empty($role_data)) return null;
        foreach ($role_data as $item) {
            if ($item == "all") return null;//所有都可以访问
            if ($this->check("{$data->getModule()}", $item)) return null;
            if ($this->check("{$data->getModule()}/{$data->getController()}", $item)) return null;
            if ($this->check("{$data->getModule()}/{$data->getController()}/{$data->getAction()}", $item)) return null;
        }
        Log::record("权限信息", $role_data);
        //没有返回就是需要授权才能访问
        EventManager::trigger("__rbac_role_deny__", $role_data);

        (new Response())->code(403)->render($data->eng()->renderMsg(true, 403, "403 Forbidden", "对不起，你没有访问权限。"))->send();
    }

    private function check($url, $item): bool
    {
        $url = trim($url, "/");
        if ($url === "") $url = "/";
        $item = trim($item, "/");
        if ($item === "") $item = "/";
        if ($item == $url) return true;
        $builder = new StringBuilder($item);
        if ($builder->contains($url . "?")) {
            parse_str($builder->findAndSubStart("?"), $array);
            $allow = sizeof($array);
            foreach ($array as $key => $value) {
                if (parse_type($value, arg($key)) === $value) {
                    $allow--;
                }
            }
            return $allow <= 0;
        }
        return false;
    }
}