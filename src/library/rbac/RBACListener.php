<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: library\rbac
 * Class RBACListener
 * Created By ankio.
 * Date : 2023/4/24
 * Time : 00:26
 * Description :
 */

namespace library\rbac;

use cleanphp\base\Controller;
use cleanphp\base\EventManager;
use cleanphp\base\Response;
use cleanphp\base\Variables;
use cleanphp\engine\EngineManager;
use cleanphp\file\Log;
use cleanphp\objects\StringBuilder;

class RBACListener
{
    const HTTP_FORBIDDEN = 403;
    const HTTP_MESSAGE = "Forbidden";
    const RBAC_ROLE_DENY_EVENT = "__rbac_role_deny__";

    function handler(Controller &$data)
    {
        $role_data = Role::init()->get(RBAC::getRole());
        //没有角色信息，就默认可以执行
        if (empty($role_data)) {
            return null;
        }
        $url = $this->getUrl();
        $allow = $this->checkUrl($url, $role_data);
        if ($allow) {
            return null;
        }

        Log::record("权限信息", print_r($role_data,true));
        //没有返回就是需要授权才能访问
        EventManager::trigger(self::RBAC_ROLE_DENY_EVENT, $role_data);
        $response = new Response();
        $response->code(self::HTTP_FORBIDDEN)
            ->render(EngineManager::getEngine()->renderMsg(true, self::HTTP_FORBIDDEN, self::HTTP_MESSAGE, "对不起，你没有访问权限。"))
            ->send();
    }

    function checkUrl($url, $role_data): bool
    {
      //  dumps($url, $role_data);
        foreach ($role_data as $item) {
            if ($item == "all") {
                return true;//所有都可以访问
            }
            if(str_starts_with($url,$item))return true;

        }
        return false;
    }

    function getUrl(): string
    {
        $module = Variables::get("__request_module__");
        $controller = Variables::get("__request_controller__");
        $action = Variables::get("__request_action__");
        return sprintf("%s/%s/%s", $module, $controller, $action);
    }
}
