<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * File helper.php
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 12:47
 * Description : 助手函数
 */

use core\App;
use core\base\Argument;
use core\base\Dump;
use core\base\Lang;
use core\base\Route;
use core\exception\ExitApp;

/**
 * 输出语言
 * @param string $str 语言名称
 * @param ...$args
 * @return string
 */
function lang(string $str, ...$args): string
{
    return Lang::get($str, ...$args);
}

if (!function_exists('mime_content_type')) {
    /**
     * 获取文件mime类型
     * @param $filename string 文件绝对路径
     * @return string
     */
    function mime_content_type(string $filename): string
    {

        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $array = explode('.', $filename);
        $ext = strtolower(array_pop($array));

        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

}
/**
 * 输出所有变量
 * @param ...$args
 * @return void
 * @throws ExitApp
 */
function dumps(...$args)
{
    $line = debug_backtrace()[0]['file'] . ':' . debug_backtrace()[0]['line'] . "\n";
    dump($args, false, $line);
}

/**
 * 输出变量内容
 * @param null $var 预输出的变量名
 * @param false $exit 输出变量后是否退出进程
 * @param string|null $line
 * @throws ExitApp
 */
function dump($var, bool $exit = false, string $line = null)
{
    if (!App::$debug) return;//不是调试模式就直接返回
    if ($line === null)
        $line = debug_backtrace()[0]['file'] . ':' . debug_backtrace()[0]['line'] . "\n";


    if (App::$cli) {
        echo $line;
        var_dump($var);
        if ($exit) {
            App::exit(lang("调用输出命令退出"));
        }
        return;
    }
    if ($line !== "") {

        echo <<<EOF
<style>pre {display: block;padding: 9.5px;margin: 0 0 10px;font-size: 13px;line-height: 1.42857143;color: #333;word-break: break-all;word-wrap: break-word;background-color:#f5f5f5;border: 1px solid #ccc;border-radius: 4px;}</style><div style="text-align: left">
<pre class="xdebug-var-dump" dir="ltr"><small>{$line}</small>\r\n
EOF;
    } else {
        echo <<<EOF
<style>pre {display: block;padding: 9.5px;margin: 0 0 10px;font-size: 13px;line-height: 1.42857143;color: #333;word-break: break-all;word-wrap: break-word;background-color:#f5f5f5;border: 1px solid #ccc;border-radius: 4px;}</style><div style="text-align: left"><pre class="xdebug-var-dump" dir="ltr">
EOF;
    }
    $dump = new Dump();
    echo $dump->dumpType($var);
    echo '</pre></div>';
    if ($exit) {
        App::exit(lang("调用输出命令退出"));
    }
}

/**
 * 传入样例类型，对目标进行类型转换
 * @param $sample string 样例
 * @param $data  mixed 需要转换的类型
 * @return bool|float|int|mixed|string
 */
function parse_type(string $sample, $data)
{
    if (is_int($sample)) return intval($data);
    elseif (is_string($sample)) return strval($data);
    elseif (is_bool($sample)) return boolval($data);
    elseif (is_float($sample)) return floatval($data);
    elseif (is_double($sample)) return doubleval($data);
    return $data;
}

/**
 * 从get参数中获取
 * @param ?string $key
 * @param mixed $default
 * @return bool|float|int|mixed|string|null
 */
function get(string $key = null, $default = null)
{
    return Argument::get($key, $default);
}

/**
 * 从post参数中获取
 * @param ?string $key
 * @param mixed $default
 * @return bool|float|int|mixed|string|null
 */
function post(string $key = null, $default = null)
{
    return Argument::post($key, $default);
}

/**
 * 从所有参数中获取
 * @param ?string $key
 * @param mixed $default
 * @return bool|float|int|mixed|string|null
 */
function arg(string $key = null, $default = null)
{
    return Argument::arg($key, $default);
}
/**
 * 生成符合路由规则的URL
 * @param string $m      模块名
 * @param string $c      控制器名
 * @param string $a      方法
 * @param array $param  参数数组
 *
 * @return string
 */
function url(string $m = 'index', string $c = 'main', string $a = 'index', array $param = []): string
{
    return Route::url(...func_get_args());
}
/**
 * 将Array转换为无引号的Json
 * @param array $arg
 * @return string
 */
function convert_json(array $arg): string
{
    $data = json_encode($arg,JSON_UNESCAPED_UNICODE);
    $data = substr($data,1,strlen($data)-2);
    return str_replace("\"","",$data);
}