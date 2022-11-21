<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

namespace core\engine;


use core\App;
use core\base\Error;
use core\config\Config;
use core\event\EventManager;
use core\exception\ControllerError;
use core\exception\ExitApp;
use core\file\File;
use core\file\Log;
use core\base\Controller;
use core\base\Response;
use core\base\Variables;
use core\objects\StringBuilder;

/**
 * Class View
 * @package core\mvc
 * Date: 2020/11/30 11:42 下午
 * Author: ankio
 * Description:视图渲染
 */
class ViewEngine extends ResponseEngine
{

    private string $layout = "";
    private bool $encode = true;
    private int $code = 200;
    private array $data = [];
    private string $left_delimiter = "<{";
    private string $right_delimiter = "}>";
    private string $compile_dir;
    private string $template_dir;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->compile_dir = Variables::getStoragePath("view");
        $this->template_dir = Variables::getViewPath();
    }

    /**
     * 获取响应码
     * @return int
     */
    function getCode(): int
    {
        return $this->code;
    }

    /**
     * 设置layout文件
     * @param ?string $file layout文件名
     * @return ViewEngine
     */
    public function setLayout(?string $file): ViewEngine
    {
        $this->layout = $file;
        return $this;
    }

    /**
     * 是否输出进行html编码
     * @return bool
     */
    public function isEncode(): bool
    {
        return $this->encode === true;
    }

    /**
     * 是否输出进行html编码
     * @param bool $encode 是否编码
     * @return ViewEngine
     */
    public function setEncode(bool $encode): ViewEngine
    {
        $this->encode = $encode;
        return $this;
    }

    /**
     * 设置模板数据数组
     * @param array $array 模板数据数组
     * @return ViewEngine
     */
    function setArray(array $array): ViewEngine
    {
        $this->data = $array;
        return $this;
    }


    function renderMsg(bool $err = false, int $code = 404, string $title = "",  $msg = "", int $time = 3, string $url = '', string $desc = "立即跳转"): string
    {
        parent::renderMsg($err, $code, $title, $msg, $time, $url, $desc);
        $array = [
            "data" => ["err" => $err, "code" => $code, "title" => $title, "msg" => $msg, "time" => $time, "url" => $url, "desc" => $desc],
            "tpl" => <<<TPL
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <style >* {
        padding: 0;
        margin: 0;
    }

    div {
        padding: 4px 48px;
    }

    a {
        color: #2E5CD5;
        cursor: pointer;
        text-decoration: none
    }

    a:hover {
        text-decoration: underline;
    }

    body {
        background: #fff;
        color: #333;
        font-size: 18px;
    }

    h1 {
        font-size: 100px;
        font-weight: normal;
        margin-bottom: 12px;
    }

    p {
        line-height: 1.6em;
        font-size: 42px
    }</style>
    <title>{$title}</title></head>
<body>
<div style="padding: 24px 48px;"><h1>{$err}</h1>
    <p><span style="font-size:32px;">{$title}</span></p>
    <p><span style="font-size:25px;">{$msg}</span></p>
    <span id="jump_box" style="font-size:25px;">

    </span>
</div>
<script>
    let wait = "{$time}";
    if (parseInt(wait) !== -1) {
        document.getElementById('jump_box').innerHTML = "还有<span id='jump'>{$time}</span>秒为您自动跳转，<a href='{$url}' target='_self'>{$desc}</a>"
        setInterval(function () {
            document.getElementById("jump").innerText = (--wait).toString();
            if (wait <= 0) {
                location.href = "{$url}";
            }
        }, 1000);
    } else if ("{$url}" !== "") {
        document.getElementById('jump_box').innerHTML = "<span id='jump'><a href='{$url}' target='_self'>{$desc}</a></span>"
    }
</script>
</body>
</html>

TPL
        ];
        EventManager::trigger("__view_render_msg__", $array, true);
        return $array["tpl"];
    }


    public function onControllerError(ControllerError $error): bool
    {
        $__module = $error->__module;
        $__controller = $error->__controller;
        $__action = $error->__action;
        $_controller_exist = $error->_controller_exist;
        //构建模板
        $tpl_name = $__controller . '_' . $__action;
        //构建控制器
        $controller = 'app\\controller\\' . $__module . '\\' . $__controller;
        //构建基类控制器
        $base = 'app\\controller\\' . $__module . '\\BaseController';

        $tpl = Variables::getViewPath($__module, $tpl_name . ".tpl");
        //模板存在，使用模板渲染
        $tpl_exist = file_exists($tpl);
        //确认使用的渲染引擎
        if (!$_controller_exist && class_exists($base) ){
            $controller = $base;
            $_controller_exist = true;
        }
        $result = null;
        $content_type = $this->getContentType();
        if ($_controller_exist) {
            /**
             * @var $obj Controller
             */
            $obj = new $controller($__module, $__controller, $__action);
            if (!$obj->eng() instanceof ViewEngine) {
                Error::err("模板引擎异常：不允许操作的类型",[],"ViewEngine");
            }
            $result = $obj->init();
            //获取初始化结果
            if ($result === null && $tpl_exist) {
                $content_type = $obj->eng()->getContentType();
                $result = $obj->eng()->setEncode(false)->render($tpl_name);
            }
        } elseif ($tpl_exist) {
            $result = $this->setEncode(false)->render($tpl_name);
        }
        //初始化如果有输出则直接输出，不执行函数。
        if (!$result)
            Error::err(sprintf("模板资源不存在：%s", $tpl),[],"ViewEngine");

        (new Response())->render($result, 200, $content_type)->send();
        return true;
    }

    function getContentType(): string
    {
        return 'text/html';
    }

    function render(...$data): string
    {

        App::$debug && Variables::set("__view_time_start__", microtime(true));
        $template_name = $data[0];
        if (!empty($this->layout)) {
            if ($template_name === $this->layout)
                Error::err("父模板不能与当前模板一致，会导致死循环。",[],"ViewEngine");
            $this->setData("__template_file", $template_name);
            $template_name = $this->layout;
        }
        $complied_file = $this->compile($template_name);
        ob_start();
        extract($this->data, EXTR_OVERWRITE);

        include $complied_file;

        App::$debug && Log::record("ViewEngine", sprintf("编译运行时间：%s 毫秒", round((microtime(true) - Variables::get("__view_time_start__", 0)) * 1000, 2)),Log::TYPE_WARNING);
        return ob_get_clean();
    }

    /**
     * 设置模板数据
     * @param string $key
     * @param $value
     * @return ViewEngine
     */
    function setData(string $key, $value): ViewEngine
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 模板编译
     * @param string $template_name 模板文件名
     * @return string
     * @throws ExitApp
     */
    public function compile(string $template_name): string
    {

        if (!is_dir($this->compile_dir)) mkdir($this->compile_dir, 0777, true);
        if (!is_dir($this->template_dir)) mkdir($this->template_dir, 0777, true);

        $__module = Variables::get("__controller_module__", "");
        $real_name = $template_name;
        $template_name = ($__module == '' ? '' : $__module . DS) . $template_name . '.tpl';
        //自动化模板名字
        $file = $this->template_dir . DS . $template_name;
        if (!file_exists($file)) {
            $file2 = Variables::getViewPath($real_name.'.tpl');
            if (!file_exists($file2)) {

                Error::err(sprintf("模板文件（%s）不存在", $file), [],"ViewEngine");
            }
            $file = $file2;
        }

        $complied_file = $this->compile_dir . DS . md5(realpath($file)) . '.' . filemtime($file) . '.' . basename($template_name) . '.php';

        if (!App::$debug && file_exists($complied_file)) {//调试模式下，直接重新编译
            return $complied_file;
        }

        $template_data = file_get_contents($file);
        $template_data = $this->_compile_struct($template_data);
        $template_data = $this->_compile_function($template_data);

        $template_data = '<?php use core\engine; if(!class_exists("' . str_replace("\\", "\\\\", ViewEngine::class) . '", false)) exit("模板文件禁止被直接访问.");?>' . $template_data;

        $template_data = $this->_clean_remark($template_data);
        $this->_clear_complied_file($template_name);
        $tmp_file = $complied_file . uniqid('_tpl', true);
        if (!file_put_contents($tmp_file, $template_data))
            Error::err(sprintf("写入 %s 文件失败", $tmp_file), [],"ViewEngine");

        $success = @rename($tmp_file, $complied_file);
        if (!$success) {
            if (is_file($complied_file)) @unlink($complied_file);
            $success = @rename($tmp_file, $complied_file);
            if (!$success)
                Error::err(sprintf("写入 %s 文件失败", $tmp_file), [],"ViewEngine");
        }
        return $complied_file;
    }

    /**
     * 翻译模板语法
     * @param string $template_data
     * @return string|string[]
     */
    private function _compile_struct(string $template_data)
    {
        $foreach_inner_before = '<?php if(!empty($1)){ $_foreach_$3_counter = 0; $_foreach_$3_total = count($1);?>';
        $foreach_inner_after = '<?php $_foreach_$3_index = $_foreach_$3_counter;$_foreach_$3_iteration = $_foreach_$3_counter + 1;$_foreach_$3_first = ($_foreach_$3_counter == 0);$_foreach_$3_last = ($_foreach_$3_counter == $_foreach_$3_total - 1);$_foreach_$3_counter++;?>';
        $pattern_map = [
            '<{\*([\s\S]+?)\*}>' => '<?php /* $1*/?>',
            '<{#(.*?)}>' => '<?php echo $1; ?>',
            '(<{((?!}>).)*?)(\$[\w\"\'\[\]]+?)\.(\w+)(.*?}>)' => '$1$3[\'$4\']$5',
            '(<{.*?)(\$(\w+)@(index|iteration|first|last|total))+(.*?}>)' => '$1$_foreach_$3_$4$5',
            '<{(\$[\$\w\.\"\'\[\]]+?)\snofilter\s*}>' => '<?php echo $1; ?>',
            '<{(\$[\$\w\"\'\[\]]+?)\s*=(.*?)\s*}>' => '<?php $1 =$2; ?>',
            '<{(\$[\$\w\.\"\'\[\]]+?)\s*}>' => '<?php echo htmlspecialchars($1, ENT_QUOTES, "UTF-8"); ?>',
            '<{if\s*(.+?)}>' => '<?php if ($1) : ?>',
            '<{else\s*if\s*(.+?)}>' => '<?php elseif ($1) : ?>',
            '<{else}>' => '<?php else : ?>',
            '<{break}>' => '<?php break; ?>',
            '<{continue}>' => '<?php continue; ?>',
            '<{\/if}>' => '<?php endif; ?>',
            '<{foreach\s*(\$[\$\w\.\"\'\[\]]+?)\s*as(\s*)\$([\w\"\'\[\]]+?)}>' => $foreach_inner_before . '<?php foreach( $1 as $$3 ) : ?>' . $foreach_inner_after,
            '<{foreach\s*(\$[\$\w\.\"\'\[\]]+?)\s*as\s*(\$[\w\"\'\[\]]+?)\s*=>\s*\$([\w\"\'\[\]]+?)}>' => $foreach_inner_before . '<?php foreach( $1 as $2 => $$3 ) : ?>' . $foreach_inner_after,
            '<{\/foreach}>' => '<?php endforeach; }?>',
            '<{include\s*file=(.+?)}>' => '<?php include $this->compile($1); ?>',
        ];

        foreach ($pattern_map as $p => $r) {
            $pattern = '/' . str_replace(["<{", "}>"], [$this->left_delimiter . '\s*', '\s*' . $this->right_delimiter], $p) . '/i';
            $count = 1;
            while ($count != 0) {
                $template_data = preg_replace($pattern, $r, $template_data, -1, $count);
            }
        }
        return $template_data;
    }

    /**
     * 函数编译
     * @param string $template_data
     * @return string|string[]|null
     */
    private function _compile_function(string $template_data)
    {
        $pattern = '/' . $this->left_delimiter . '(\w+)\s*(.*?)' . $this->right_delimiter . '/';
        return preg_replace_callback($pattern, [$this, '_compile_function_callback'], $template_data);
    }

    /**
     * 清除html注释
     * @param string $template_data
     * @return string
     */
    public function _clean_remark(string $template_data): string
    {
        $is_matched = preg_match_all('/<!--[\s\S]*?-->/', $template_data, $matches);
        if ($is_matched) {
            foreach ($matches[0] as $match) {
                if ((new StringBuilder($match))->startsWith("<!--[")) continue;//不删除IE标记
                $template_data = str_replace($match, "", $template_data);
            }
        }
        $is_rewrite = Config::getConfig("frame")["rewrite"];
        if($is_rewrite)
            $template_data = str_replace("../../public","/CLEAN_STATIC",$template_data);
        else{
            $template_data = str_replace("../../public","/?s=CLEAN_STATIC",$template_data);
        }

        if(!App::$debug) return str_replace(["\r","\n"], "", $template_data);//换行符清理了
        return $template_data;
    }

    /**
     * 清除过期的文件
     * @param string $template_name
     */
    private function _clear_complied_file(string $template_name)
    {
        $dir = scandir($this->compile_dir);
        if ($dir) {
            $part = md5(realpath($this->template_dir . DS . $template_name));
            foreach ($dir as $d) {
                if (substr($d, 0, strlen($part)) == $part) {
                    @unlink($this->compile_dir . DS . $d);
                }
            }
        }
    }


    function renderError(string $msg, array $traces, string $dumps,string $tag): string
    {
        Variables::set("__controller_module__", "");
        $tpl = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><{$msg nofilter}></title>
    <style type="">
    body {
	padding: 0;
	margin: 0;
	word-wrap: break-word;
	word-break: break-all;
	font-family: Courier,Arial,sans-serif;
	background: #ebf8ff;
	color: #5e5e5e
}

div,h2,p,span {
	margin: 0;
	padding: 0
}

ul {
	margin: 0;
	padding: 0;
	list-style-type: none;
	font-size: 0;
	line-height: 0
}

#body {
	
	margin: 0 auto
}

#main {
	width: 100%;
	max-width: 900px;
	margin: 13px auto 0 auto;
	padding: 0 0 35px 0
}

#contents {
	
	margin: 13px auto 0 auto;
	background: #FFF;
	padding: 10px
}

#contents h2 {
	display: block;
	background: #cff0f3;
	font: bold:20px;
	padding: 12px 30px;
	margin: 0 10px 22px 1px
}

#contents ul {
	padding: 0 18px 0 18px;
	font-size: 0;
	line-height: 0
}

#contents ul li {
	display: block;
	    padding: 10px 0 0;
	color: #8f8f8f;
	background-color: inherit;
	font: normal 14px Arial,Helvetica,sans-serif;
	margin: 0
}

#contents ul li span {
	display: block;
	color: #408baa;
	background-color: inherit;
	font: bold 14px Arial,Helvetica,sans-serif;
	padding: 0 0 10px 0;
	margin: 0
}

#oneborder {
	font: normal 14px Arial,Helvetica,sans-serif;
	border: #ebf3f5 solid 4px;
	margin: 0 18px;
	padding: 10px 20px;
	line-height: 23px;
	overflow:scroll;
	white-space:nowrap;
}

#oneborder span {
	padding: 0;
	margin: 0
}

#oneborder #current {
	background: #cff0f3
}

pre {
	white-space: pre-wrap
}
    </style>
</head>
<body>
<div id="main">
    <div id="contents">
    <{if $dump!==""}>
     <h2><pre>错误发生前已输出的内容：<br><{$dump}></pre></h2>
    <{/if}>
        
        <h2>
            <pre><{$msg nofilter}></pre>
        </h2>
        <{foreach $array as $key => $trace}>
            <ul>
                <li><span>
                <{$trace["title"]}></span>  
                <span style="color: #094e5a">
                <{$trace["func"]}></span>  
                </li>
            </ul>
            <div id="oneborder">
               
                <{foreach $trace["data"] as $singleLine}>
                    <{$singleLine nofilter}>
                <{/foreach}>
</div>
          
        <{/foreach}>
    </div>
</div>
<div style="clear:both;padding-bottom:50px;"></div>
</body>
</html>';
        $file = Variables::getCachePath("temp_error.tpl");
        if(!is_dir(Variables::getCachePath()))File::mkDir(Variables::getCachePath());

        if (!file_exists($file)||App::$debug) file_put_contents($file, $tpl);

        $this->setTplDir(Variables::getCachePath());
        $this->layout = '';
        $setArray = [];
        foreach ($traces as $key=>$trace) {
            if (is_array($trace) && !empty($trace["file"])) {
                $trace["keyword"] = $trace["keyword"] ?? "";
                $sourceLine = self::errorFile($trace["file"], $trace["line"], $trace["keyword"]);
                $trace["line"] = $sourceLine["line"];
                unset($sourceLine["line"]);
                if ($sourceLine) {
                    $setArray[] = [
                        "title" => sprintf("#%s %s(%s)",$key,$trace['file'],$trace['line']),
                        "func" => sprintf("%s%s%s",$trace["class"]??"",$trace["type"]??"",$trace['function']??""),
                        "line" => $trace["line"],
                        "data" => $sourceLine
                    ];
                }
            }
        }
        $this->data = ["msg" => $msg, "dump" => $dumps, "array" => $setArray];
        return $this->render("temp_error");
    }

    /**
     * 设置模板目录
     * @param string $dir
     */
    public function setTplDir(string $dir)
    {
        $this->template_dir = $dir;
    }

    /**
     * @param string $file 错误文件名
     * @param int $line 错误文件行,若为-1则指定msg查找
     * @param string $msg 当line为-1才有效
     * @return array
     */
    public static function errorFile(string $file, int $line = -1, string $msg = ""): array
    {
        if (!(file_exists($file) && is_file($file))) {
            return [];
        }
        $data = file($file);
        $count = count($data) - 1;
        $returns = [];
        if ($line == -1) {
            //查找文本
            for ($i = 0; $i <= $count; $i++) {
                if (strpos($data[$i], $msg) !== false) {
                    $line = $i + 1;
                    break;
                }
            }
        }
        $returns["line"] = $line;
        $start = $line - 5;
        if ($start < 1) {
            $start = 1;
        }
        $end = $line + 5;
        if ($end > $count) {
            $end = $count + 1;
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $line) {
                $returns[] = "<div id='current'>" . $i . ".&nbsp;" . self::highlightCode($data[$i - 1]) . "</div>";
            } else {
                $returns[] = $i . ".&nbsp;" . self::highlightCode($data[$i - 1]);
            }
        }
        return $returns;
    }

    /**
     * 高亮代码
     * @param string $code
     * @return bool|string|string[]
     */
    private static function highlightCode(string $code)
    {
        $code = preg_replace('/(\/\*\*)/', '///**', $code);
        $code = preg_replace('/(\s\*)[^\/]/', '//*', $code);
        $code = preg_replace('/(\*\/)/', '//*/', $code);
        if (preg_match('/<\?(php)?[^[:graph:]]/i', $code)) {
            $return = highlight_string($code, true);
        } else {
            $return = preg_replace('/(&lt;\?php&nbsp;)+/i', "",
                highlight_string("<?php " . $code, true));
        }
        return str_replace(['//*/', '///**', '//*'], ['*/', '/**', '*'], $return);
    }

    /**
     * 函数回调
     * @param $matches
     * @return string|string[]|null
     */
    private function _compile_function_callback($matches)
    {

        if (empty($matches[2])) return '<?php echo ' . $matches[1] . '();?>';

        if ($matches[1] !== "unset") {
            $replace = '<?php echo ' . $matches[1] . '($1);?>';
        } else {
            $replace = '<?php  ' . $matches[1] . '($1);?>';
        }
        $sync = preg_replace('/\((.*)\)\s*$/', $replace, $matches[2], -1, $count);
        if ($count) return $sync;

        $pattern_inner = '/\b([-\w]+?)\s*=\s*(\$[\w"\'\]\[\-_>\$]+|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|([->\w]+))\s*?/';
        if (preg_match_all($pattern_inner, $matches[2], $matches_inner, PREG_SET_ORDER)) {
            $params = "array(";
            foreach ($matches_inner as $m) $params .= '\'' . $m[1] . "'=>" . $m[2] . ", ";
            $params .= ")";
            return '<?php echo ' . $matches[1] . '(' . $params . ');?>';
        } else {
            Error::err(sprintf('%s 函数的参数不正确', $matches[1]),[],"ViewEngine");
        }
        return "";
    }
}
