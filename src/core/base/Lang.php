<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class Lang
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 18:03
 * Description :
 */

namespace core\base;

use core\App;
use core\config\Config;
use core\file\Log;

class Lang
{

    private static array $lang_data = [];
    private static string $lang = "zh-cn";
    public static array $lang_map = ["zh"=>"zh-cn"];
    /**
     * 注册语言包
     * @return void
     */
    public static function register()
    {
        self::$lang = self::detect();
        $_file = Variables::getConfigPath("lang", self::$lang . ".yml");
        if (self::$lang !== "" && file_exists($_file)) {
            App::$debug && Log::record("Lang", sprintf("加载语言配置：%s.yml",self::$lang));
            //存在定义
            self::$lang_data = Config::getInstance(self::$lang)->setLocation(Variables::getConfigPath("lang", DS))->getAll();
        }else{
            App::$debug && Log::record("Lang", sprintf("语言配置：%s.yml 不存在",self::$lang),Log::TYPE_WARNING);
        }
    }

    /**
     * 自动侦测设置获取语言选择
     * @return string
     */
    public static function detect(): string
    {
        $lang_set = 'zh-cn';
        if (isset($_COOKIE["lang"])) {
            $lang_set = strtolower($_COOKIE["lang"]);
            App::$debug && Log::record("Lang", sprintf("通过Cookie获取语言：%s",self::$lang));
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // 自动侦测浏览器语言
            preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
            $lang_set = strtolower($matches[1]);
            App::$debug && Log::record("Lang", sprintf("通过Accept-Language获取语言：%s",self::$lang));
        }
        if(isset(self::$lang_map[$lang_set])){//如果获取映射，则立即
            $lang_set = self::$lang_map[$lang_set];
        }
        self::setLang($lang_set);
        return $lang_set;
    }

    /**
     * 设置语言
     * @param $lang string 语言名称
     * @return void
     */
    public static function setLang(string $lang = "zh-cn")
    {
        setcookie("lang", $lang, time() + 3600 * 24 * 30);
    }

    /**
     * 获取语言定义(不区分大小写)
     * @access public
     * @param string|null $name 语言变量
     * @param array $vars 变量替换
     * @return string
     */
    public static function get(string $name = null, ...$vars): string
    {
        if (empty($name)) {
            return "";
        }
        if (!isset(self::$lang_data[$name])) {//如果处于调试模式且没有找到数据
            if(App::$debug){
                self::$lang_data[$name] = $name;
                Config::getInstance(self::$lang)->setLocation(Variables::getConfigPath("lang", DS))->set($name, $name);
            }
            Log::record("Lang", sprintf("语言【%s】未在文件中（%s.yml）定义",$name,self::$lang),Log::TYPE_WARNING);
        }
        $value = self::$lang_data[$name] ?? $name;
        return sprintf($value, ...$vars);
    }


}
