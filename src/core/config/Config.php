<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

namespace core\config;


use core\App;
use core\base\Variables;
use core\exception\ExitApp;

/**
 * Class Config
 * @package core\config
 * Date: 2020/11/19 12:22 上午
 * Author: ankio
 * Description:配置管理器
 */
class Config
{
    private static ?Config $instance = null;//配置文件数据
    private ?array $file_data;//配置文件
    private ?string $file_name;//配置路径
    private string $path = "";

    public function __construct()
    {
        $this->path = Variables::getConfigPath();
    }

    /**
     * 获取路由表
     * @return array|null
     */
    static public function getRouteTable(): ?array
    {
        $result = Variables::get("__frame_config__");
        if ($result) {
            return $result["route"] ?? null;
        }
        return $result;
    }

    /**
     * 注册配置信息
     * @throws ExitApp
     */
    static public function register()
    {
        $conf = self::getInstance("config")->setLocation(Variables::getConfigPath())->getAll();
        Variables::set("__frame_config__", $conf);
        date_default_timezone_set(Config::getConfig('frame')['time_zone']??"Asia/Shanghai");
        $frame = self::getConfig("frame");
        if (!in_array("0.0.0.0", $frame['host']) && !App::$cli && !in_array($_SERVER["SERVER_NAME"], $frame['host'])) {
            App::exit(sprintf("您的域名绑定错误，当前域名为：%s , 请在 %s 中Host选项里添加该域名。", $_SERVER["SERVER_NAME"], Variables::getConfigPath() . "frame.yml"));
        }
    }

    /**
     * 获取配置文件数组
     * @return array|null
     */
    public function getAll(): ?array
    {
        return $this->file_data;
    }

    /**
     * 设置配置文件路径
     * @param string $path
     * @return $this
     */
    public function setLocation(string $path): Config
    {
        $this->path = $path;
        return $this->getConfigFile();
    }

    /**
     *  获取配置文件
     * @return $this
     */
    private function getConfigFile(): Config
    {
        $file = $this->path . $this->file_name;

        if (($data = Variables::get($file)) !== null)
            $this->file_data = $data;
        elseif (file_exists($file)) {
            $this->file_data = Spyc::YAMLLoad($file);
            if (!empty($this->file_data))
                Variables::set($file, $this->file_data);
        } else {
            $this->file_data = [];
        }
        return $this;
    }

    /**
     * 获取实例
     * @param string $file 存储的配置文件地址，请使用相对地址
     * @return static
     */
    public static function getInstance(string $file): Config
    {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        self::$instance->file_data = [];
        self::$instance->file_name = "$file.yml";
        return self::$instance->setLocation(Variables::getConfigPath());
    }

    /**
     * 获取配置
     * @return mixed|null
     */
    static public function getConfig($sub = "")
    {
        $result = Variables::get("__frame_config__");
        if ($result) {
            return $result[$sub] ?? null;
        }
        return $result;
    }

    /**
     * 获取配置文件里面一项
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->file_data[$key] ?? "";
    }

    /**
     * 设置整个配置文件数组
     * @param array $data
     */
    public function setAll(array $data)
    {
        $this->file_data = $data;
        $file = $this->path . $this->file_name;
        Variables::set($file, $this->file_data);
        file_put_contents($file, Spyc::YAMLDump($this->file_data));
    }

    /**
     * 设置单个配置
     * @param string $key 参数名称
     * @param  $val
     */
    public function set(string $key, $val)
    {
        $this->file_data[$key] = $val;
        $file = $this->path . $this->file_name;
        Variables::set($file, $this->file_data);
        file_put_contents($file, Spyc::YAMLDump($this->file_data));
    }
}
