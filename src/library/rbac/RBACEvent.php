<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\rbac
 * Class RBACEvent
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 21:14
 * Description :
 */

namespace library\rbac;



use core\App;
use core\base\Controller;
use core\base\Response;
use core\event\EventListener;
use core\event\EventManager;
use core\exception\ExitApp;
use core\objects\StringBuilder;

class RBACEvent implements EventListener
{

    /**
     * @param string $event
     * @param Controller $data
     * @return void|null
     * @throws ExitApp
     */
    public function handleEvent(string $event, &$data)
    {

        $role_data =  Role::getInstance()->get(RBAC::getRole());

        //没有角色信息，就默认可以执行
        if($role_data==null)return null;
        $json = $role_data["auth"];
        foreach ($json as $item){
            if($item=="all")return null;
            if($this->check("{$data->getModule()}",$item)) return null;
            if($this->check("{$data->getModule()}/{$data->getController()}",$item)) return null;
            if($this->check("{$data->getModule()}/{$data->getController()}/{$data->getAction()}",$item)) return null;
        }
        //没有返回就是需要授权才能访问
        if(!App::$debug){

            (new Response())->code(403)->render($data->eng()->renderMsg(true,403,"403 Forbidden",lang("对不起，你没有访问权限。")))->send();
        }else{
            dumps("当前为调试模式","对不起您没有访问权限","权限信息",$role_data);
            App::exit("对不起您没有访问权限");
        }
    }

    private function check($url,$item): bool
    {
        $url = trim($url,"/");if($url==="")$url="/";
        $item = trim($item,"/");if($item==="")$item="/";
        if($item==$url)return true;
        $builder = new StringBuilder($item);
        if($builder->contains($url."?")){
            parse_str($builder->findAndSubStart("?"),$array);
            $allow = false;
            foreach ($array as $key => $value){
                if(arg($key)==$value){
                    $allow = true;
                }
            }
            if($allow)return true;
        }
        return false;
    }
}