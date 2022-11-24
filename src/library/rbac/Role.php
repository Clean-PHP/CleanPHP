<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

namespace library\rbac;


use core\config\Config;


class Role
{
    protected static ?Role $instance= null;
    private array $config;
    public function __construct($rbac = [])
    {
        if($rbac===null)$rbac=[];
        $this->config = $rbac;
    }

    public function __destruct()
    {
        Config::getInstance("config")->set("rbac",$this->config);
    }

    public static function getInstance(): ?Role
    {
        if(!isset(self::$instance)||self::$instance==null) {
            self::$instance  = new Role(Config::getConfig("rbac"));
        }
        return self::$instance;
    }

    /**
     * 添加角色
     * @param $name string 角色名称
     * @param $auth array 角色权限
     * @return int|void
     */
    public function add(string $name, array $auth){
        $id = sizeof( $this->config);
        while (isset( $this->config["id_".$id]))$id++;

        $this->config["id_".$id]=[
            "role_name"=>$name,
            "auth"=>$auth
        ];
        return $id;
    }

    /**
     * 更新某个角色的授权信息
     * @param $id
     * @param $auth array 角色列表
     * @return void
     */
    public function update($id, array $auth){
        if(isset($this->config[$id])){
            $this->config["id_".$id]=[
                "role_name"=>$this->config["id_".$id]["role_name"],
                "auth"=>$auth
            ];
        }
    }

    /**
     * 删除某个角色
     * @param $id
     * @return void
     */
    public function del($id){
        if(isset($this->config["id_".$id])){
            unset($this->config["id_".$id]);
        }
    }

    /**
     * 获取角色列表
     * @return mixed
     */
    public function list(){
        return $this->config;
    }

    /**
     * 获取指定id的角色信息
     * @param $id
     * @return mixed|null
     */
    public function get($id){

        if(isset($this->config["id_".$id])){
            return $this->config["id_".$id];
        }
        return null;
    }

    /**
     * 获取api
     * @param $m string 模块
     * @param $c string 控制器
     * @return array
     */
    public function getApi(string $m, string $c): array
    {
        $name = "controller\\$m\\$c";
        $methods = get_class_methods($name);
        $methods2 = get_class_methods("controller\\$m\\BaseController");
        foreach ($methods as $key => $val) {
            if (in_array($val,$methods2)) {
                unset($methods[$key]);
            }else{
                $methods[$key]="$m/$c/$val";
            }
        }
        return $methods;
    }

}