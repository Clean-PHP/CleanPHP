<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace library\rbac;


use core\config\Config;


class Role
{
    protected static ?Role $instance = null;
    private array $config;
    private bool $code = false;


    public function __construct($rbac = [])
    {
        if ($rbac === null) $rbac = [];
        $this->config = $rbac;
    }

    public static function init($rbac = null): ?Role
    {
        if (!isset(self::$instance) || self::$instance == null) {
            $rbac_ = [];
            if (!empty($rbac)) {
                foreach ($rbac as $item) {
                    $rbac_["id_" . $item["id"]] = $item;
                }
            }

            self::$instance = new Role(empty($rbac_) ? Config::getConfig("rbac") : $rbac_);
            self::$instance->code = true;
        }
        return self::$instance;
    }

    public function __destruct()
    {
        if (!$this->code)
            Config::getInstance("config")->set("rbac", $this->config);
    }

    /**
     * 添加角色
     * @param $name string 角色名称
     * @param $auth array 角色权限
     * @return int
     */
    public function add(string $name, array $auth): int
    {
        $id = sizeof($this->config);
        while (isset($this->config["id_" . $id])) $id++;

        $this->config["id_" . $id] = [
            "role_name" => $name,
            "auth" => $auth
        ];
        return $id;
    }

    /**
     * 更新某个角色的授权信息
     * @param $id
     * @param $auth array 角色列表
     * @return void
     */
    public function update($id, array $auth)
    {
        if (isset($this->config["id_" . $id])) {
            $this->config["id_" . $id] = [
                "role_name" => $this->config["id_" . $id]["role_name"],
                "auth" => $auth
            ];
        }
    }

    /**
     * 删除某个角色
     * @param $id
     * @return void
     */
    public function del($id)
    {
        if (isset($this->config["id_" . $id])) {
            unset($this->config["id_" . $id]);
        }
    }

    /**
     * 获取角色列表
     * @return mixed
     */
    public function list()
    {
        return $this->config;
    }

    /**
     * 获取指定id的角色信息
     * @param $id
     * @return array
     */
    public function get($id): array
    {

        $ids = explode(",", $id);
        $ret = [];
        foreach ($ids as $id) {
            if (isset($this->config["id_" . $id])) {
                $ret = array_merge($ret, $this->config["id_" . $id]['auth']);
            }
        }
        return $ret;
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
            if (in_array($val, $methods2)) {
                unset($methods[$key]);
            } else {
                $methods[$key] = "$m/$c/$val";
            }
        }
        return $methods;
    }

}