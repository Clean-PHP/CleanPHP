<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\engine
 * Class JsonEngine
 * Created By ankio.
 * Date : 2022/11/11
 * Time : 17:58
 * Description :
 */

namespace core\engine;


use core\event\EventManager;
use core\file\Log;
use core\json\Json;


class JsonEngine extends ResponseEngine
{
    private array $tpl;

    /**
     * 初始化参数，每一个参数名字
     * @param array $template
     */
    public function __construct(array $template = ["code" => 0, "msg" => "OK", "data" => null, "count" => 0])
    {
        $this->tpl = $template;
    }

    function getContentType(): string
    {
        return "application/json";
    }

    function getCode(): int
    {
        return 200;
    }

    /**
     * json数据渲染
     * @param ...$data
     * @return string
     */
    function render(...$data): string
    {
        $array = [];
        $i = 0;
        foreach ($this->tpl as $key => $value) {
            if (isset($data[$i])) {
                $array[$key] = $data[$i];
            } else {
                $array[$key] = $value;
            }
            $i++;
        }
        return Json::encode($array);
    }



    function renderError(string $msg, array $traces, string $dumps,string $tag): string
    {

        $trace_text = [];
        foreach($traces as $i=>$call){
            $trace_text[$i] = sprintf("#%s %s(%s): %s%s%s",$i,$call['file'],$call['line'],$call["class"],$call["type"],$call['function']);
        }

        return JSON::encode([
            "error"=>true,
            "msg"=>$msg,
            "traces"=>$trace_text,
            "dumps"=>$dumps
        ]);
    }

    public function renderMsg(bool $err = false, int $code = 404, string $title = "", $msg = "", int $time = 3, string $url = '', string $desc = "立即跳转"): string
    {
        $array = [
            "code"=>$code,"msg"=>$title,"data"=>$msg,'url'=>$url
        ];
        EventManager::trigger("__json_render_msg__", $array, true);
        return Json::encode($array);
    }
}