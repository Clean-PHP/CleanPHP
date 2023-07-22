<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */

/**
 * Package: server\optimization\common
 * Class Release
 * Created By ankio.
 * Date : 2023/3/16
 * Time : 12:46
 * Description :
 */

namespace cleanphp\release;

use cleanphp\file\File;
use cleanphp\release\css\CompressCss;
use cleanphp\release\html\CompressHtml;
use cleanphp\release\js\CompressJs;

class Release
{
    static function checkFrame($dir)
    {
        File::traverseDirectory($dir, function ($file) {
            $functions = [
                '/catch\((ExitApp|Exception)/' => '直接捕获Exception或ExitApp异常可能导致无法正确退出App，请捕获具体异常',
                '/\s+(echo|var_dump|die|exit|print|printf)(\(|\s)/' => '输出内容请直接return，调试输出请使用内置函数，退出程序运行请使用App::exit函数',
                '/(\s|\(|=)(system|passthru|shell_exec|exec|popen|proc_open)\(/' => "可能导致系统命令执行，属于高危函数，请谨慎使用。",
                '/(\s|\(|=)(eval|assert|call_user_func|gzinflate|gzuncompress|gzdecode|str_rot13)\(/' => "可能导致任意代码执行，请谨慎使用。",
                '/(\s|\(|=)(require|require_once|include|include_once)\(/' => "可能导致任意文件包含，代码中请直接规范使用命名空间来避免包含文件。",
                '/\$_(GET|POST|REQUEST|COOKIE|SERVER|FILES)/' => "可能导致不可控的用户输入，请使用内置的arg函数获取用户数据。",
                '/(\$\w+)\(/' => "可能导致不可控的函数执行，请尽量明确执行函数。",
            ];
            if (substr($file, strrpos($file, ".php")) !== ".php") return;
            $content = strtolower(file_get_contents($file));
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

    static function package($name, $version,$file = "")
    {

        $raw_name = $name;
        $new = dirname(BASE_DIR) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "temp";

        $app_dir = $new . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR;

        File::copy(BASE_DIR, $new);


        $removed = [
            $new . DIRECTORY_SEPARATOR . "debug.lock" ,
            $new . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "storage",
            $new . DIRECTORY_SEPARATOR . "cleanphp" . DIRECTORY_SEPARATOR . "release",
            $new . DIRECTORY_SEPARATOR . "cleanphp" . DIRECTORY_SEPARATOR . "LICENSE",
            $new . DIRECTORY_SEPARATOR . "cleanphp" . DIRECTORY_SEPARATOR . "README.md"
        ];
        $base = dirname(BASE_DIR) ;
        $file = dirname(BASE_DIR) . DIRECTORY_SEPARATOR .$file;
        if(is_file($file)){
            include $file;
        }

        foreach ($removed as $item){
            File::del($item);
        }

        self::checkFrame($app_dir);

        $path = $app_dir . DIRECTORY_SEPARATOR . "config_example.php";
        $config = include_once $path;
        $config['frame']['version'] = $version;
        file_put_contents($path, '<?php return ' . var_export($config, true) . '; ');
        self::compress($new);
        $fileName = dirname(BASE_DIR) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . $name . "_" . $version . ".zip";
        File::zip($new, $fileName);
        echo "\n[项目打包程序]php程序已打包至$fileName";
        File::del($new);
    }

    static function compress($from, $to = "")
    {
        if (is_file($from)) {
            file_put_contents($to, php_strip_whitespace($from));
            return;
        }
        File::traverseDirectory($from, function ($file) {
            $fileInfo = pathinfo($file);
            if (!isset($fileInfo['extension']) || !is_file($file)) return;
            if ($fileInfo['extension'] === 'php') {
                file_put_contents($file, php_strip_whitespace($file));
            } elseif ($fileInfo['extension'] === 'css') {
                CompressCss::compress($file);
            } elseif ($fileInfo['extension'] === 'js') {
                CompressJs::compress($file);
            } elseif ($fileInfo['extension'] === 'html' || $fileInfo['extension'] === 'tpl') {
                CompressHtml::compress($file);
            }
        });
        echo "\n[信息]代码压缩完成！";
    }
}