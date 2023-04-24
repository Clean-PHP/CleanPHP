<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: server\optimization\common
 * Class Release
 * Created By ankio.
 * Date : 2023/3/16
 * Time : 12:46
 * Description :
 */

namespace server\optimization\common;

use cleanphp\base\Config;
use server\optimization\css\CompressCss;
use server\optimization\html\CompressHtml;
use server\optimization\js\CompressJs;

class Release
{
    static function checkFrame()
    {
        FileUtils::doFile(FileUtils::getAllFile(BASE_DIR), function ($file) {
            if (substr($file, strrpos($file, ".php")) !== ".php") return;
            $content = strtolower(file_get_contents($file));
            $functions = [
                '/catch\((ExitApp|Exception)/' => '直接捕获Exception或ExitApp异常可能导致无法正确退出App，请捕获具体异常',
                '/\s+(echo|var_dump|die|exit|print|printf)(\(|\s)/' => '输出内容请直接return，调试输出请使用内置函数，退出程序运行请使用App::exit函数',
                '/(\s|\(|=)(system|passthru|shell_exec|exec|popen|proc_open)\(/' => "可能导致系统命令执行，属于高危函数，请谨慎使用。",
                '/(\s|\(|=)(eval|assert|call_user_func|gzinflate|gzuncompress|gzdecode|str_rot13)\(/' => "可能导致任意代码执行，请谨慎使用。",
                '/(\s|\(|=)(require|require_once|include|include_once)\(/' => "可能导致任意文件包含，代码中请直接规范使用命名空间来避免包含文件。",
                '/\$_(GET|POST|REQUEST|COOKIE|SERVER|FILES)/' => "可能导致不可控的用户输入，请使用内置的arg函数获取用户数据。",
                '/(\$\w+)\(/' => "可能导致不可控的函数执行，请尽量明确执行函数。",
            ];

            foreach ($functions as $key => $value) {
                preg_match_all($key, $content, $matches);
                if (sizeof($matches) != 0) {
                    if (sizeof($matches[0]) != 0) {
                        $f = str_replace(BASE_DIR, "", $file);
                        echo "------------------------------------------------------------------------------------------------\n";
                        echo "[ - ] " . str_replace("\n", "", str_replace("(", "", trim($matches[0][0]))) . "调用检测\n";
                        echo "[ - ] 文件 => $f \n";
                        echo "[ - ] 处理建议 => $value \n";
                        echo "------------------------------------------------------------------------------------------------\n";
                    }

                }
            }
        });
    }

    static function package($param)
    {
        self::checkFrame();
        $new = dirname(BASE_DIR,) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "temp";
        FileUtils::copyDir(BASE_DIR, $new);
        FileUtils::del($new . DIRECTORY_SEPARATOR . "clean");
        FileUtils::del($new . DIRECTORY_SEPARATOR . "storage");
        if (in_array('--with-cli', $param)) {
            FileUtils::del($new . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR . "optimization");
        } else {
            FileUtils::del($new . DIRECTORY_SEPARATOR . "server");
        }
        FileUtils::del($new . DIRECTORY_SEPARATOR . "Makefile");

        $app = $new . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "index.php";
        file_put_contents($app, str_replace("App::run(true);", "App::run(false);", file_get_contents($app)));
        $frame = Config::getInstance("config")->setLocation($new . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR)->get("frame");
        $hosts = $frame["host"];
        echo "\n[项目打包程序]目前绑定域名如下：";
        for ($i = 0; $i < sizeof($hosts); $i++) {
            $host = $hosts[$i];
            echo "\n$host";
            if (!in_array("--ignore", $param)) {
                $fh = fopen('php://stdin', 'r');
                echo "\n[项目打包程序]如需修改请输入新的域名,不修改请留空，删除请输入-1：";
                $str = fread($fh, 1000);
                fclose($fh);
                if (strpos($str, "-1") === 0) {
                    echo "\n[项目打包程序]删除域名 {$hosts[$i]}";
                    unset($hosts[$i]);
                } else if (strpos($str, "\n") === 0) {
                    echo "\n[项目打包程序]{$hosts[$i]}无需修改。";
                } else {
                    $hosts[$i] = str_replace("\n", "", $str);
                    echo "\n[项目打包程序]域名修改为  {$hosts[$i]} 。";
                }
            }
        }
        $frame["host"] = $hosts;
        $app_name = $frame["app_name"];
        $ver_code = $frame["ver_code"];
        $ver_name = $frame["ver_name"];
        if (!in_array("--ignore", $param)) {
            $fh = fopen('php://stdin', 'r');
            echo "\n[项目打包程序]项目名称（ $app_name ），不修改请留空：";
            $str = fread($fh, 1000);
            if (strpos($str, "\n") === 0) {
                echo "\n[项目打包程序]无需修改。";
            } else {
                $frame["app_name"] = str_replace("\n", "", $str);
                echo "\n[项目打包程序]修改项目名称为：{$frame["app_name"]}";
            }
            fclose($fh);

            $fh = fopen('php://stdin', 'r');
            echo "\n[项目打包程序]更新版本号（ $ver_code ），不修改请留空：";
            $str = fread($fh, 1000);
            if (strpos($str, "\n") === 0) {
                echo "\n[项目打包程序]无需修改。";
            } else {
                $frame["ver_code"] = str_replace("\n", "", $str);
                echo "\n[项目打包程序]修改项目名称为：{$frame["ver_code"]}";
            }
            fclose($fh);

            $fh = fopen('php://stdin', 'r');
            echo "\n[项目打包程序]更新版本名（ $ver_name ），不修改请留空：";
            $str = fread($fh, 1000);
            if (strpos($str, "\n") === 0) {
                echo "\n[项目打包程序]无需修改。";
            } else {
                $frame["ver_name"] = str_replace("\n", "", $str);
                echo "\n[项目打包程序]修改项目名称为：{$frame["ver_name"]}";
            }
            fclose($fh);
        }
        Config::getInstance("config")->setLocation($new . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR)->set("frame", $frame);

        if (in_array("--compress", $param)) {
            self::compress($new);
        }
        if (in_array("--single", $param)) {
            $name = "single_{$app_name}_{$ver_name}_{$ver_code}";
            (new Single($name))->run($new);
            if (in_array("--compress", $param)) {
                self::compress(dirname(BASE_DIR) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "$name.php", dirname(BASE_DIR) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "compress_$name.php");
            }
        } else {
            if (in_array("--compress", $param)) {
                $app_name = "compress_$app_name";
            }
            $fileName = dirname(BASE_DIR) . "/dist/" . $app_name . "_" . $ver_name . "(" . $ver_code . ").zip";

            FileUtils::zip($new, $fileName);
            echo "\n[项目打包程序]php程序已打包至$fileName";
            FileUtils::del($new);
        }
    }

    static function compress($from, $to = "")
    {
        if (is_file($from)) {
            file_put_contents($to, php_strip_whitespace($from));
            return;
        }
        $dir_array = array();
        FileUtils::scanDirs($from, scandir($from), $dir_array);
        if (is_array($dir_array) && count($dir_array) > 0) {
            foreach ($dir_array as $dir) {
                $files = scandir($dir);
                if (!is_array($files) || count($files) < 1) continue;
                foreach ($files as $file) {
                    if (is_dir($file) || $file === '.' || $file === '..') continue;
                    $file = $dir . DIRECTORY_SEPARATOR . $file;
                    $fileInfo = pathinfo($file);
                    if (!isset($fileInfo['extension'])) continue;
                    if (!is_file($file)) continue;
                    if ($fileInfo['extension'] === 'php') {
                        file_put_contents($file, php_strip_whitespace($file));
                    } elseif ($fileInfo['extension'] === 'css') {
                        CompressCss::compress($file);
                    } elseif ($fileInfo['extension'] === 'js') {
                        CompressJs::compress($file);
                    } elseif ($fileInfo['extension'] === 'html' || $fileInfo['extension'] === 'tpl') {
                        CompressHtml::compress($file);
                    }

                }
            }
        }
        echo "\n[信息]代码压缩完成！";
    }
}