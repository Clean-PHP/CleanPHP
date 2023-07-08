<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace library\rbac;


use cleanphp\base\Config;


class Role
{
    protected static ?Role $instance = null;
    private array $config;
    /**
     * @var true
     */
    private bool $code;


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
                    $rbac_[$item["id"]] = $item;
                }
            }
            self::$instance = new Role(empty($rbac_) ? Config::getConfig("rbac") : $rbac_);
            self::$instance->code = true;
        }
        return self::$instance;
    }

    public static function getList()
    {
        if(self::$instance==null)return null;
        return self::$instance->list();
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
            if (isset($this->config[$id])) {
                $ret = array_merge($ret, $this->config[$id]['auth']);
            }
        }
        return $ret;
    }
}