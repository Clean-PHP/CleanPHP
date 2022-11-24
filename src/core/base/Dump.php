<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class Dump
 * Created By ankio.
 * Date : 2022/11/12
 * Time : 13:39
 * Description :调试输出类
 */

namespace core\base;

use core\objects\StringBuilder;
use ReflectionClass;
use ReflectionException;

class Dump
{

    private string $output = "";


    /**
     * 输出对象
     * @param       $param
     * @param int $i
     */
    private function dumpObject($param, int $i = 0)
    {
        $className = get_class($param);

        if ($className == 'stdClass' && $result = json_encode($param)) {
            $this->dumpArray(json_decode($result, true), $i);
            return;
        }
        static $objId = 1;
        $this->output .= "<b style='color: #333;'>Object</b> <i style='color: #333;'>$className</i>";
        $objId++;
        $this->dumpProp($param, $className, $objId);

    }

    /**
     * 输出数组
     * @param       $param
     * @param int $i
     */
    private function dumpArray($param, int $i = 0)
    {

        $len = count($param);
        $space = str_repeat("    ", $i);
        $i++;
        $this->output .= "<b style='color: #333;'>array</b> <i style='color: #333;'>(size=$len)</i> \r\n";
        if ($len === 0)
            $this->output .= $space . "  <i  style='color: #888a85;'>empty</i> \r\n";
        foreach ($param as $key => $val) {
            $str = htmlspecialchars((new StringBuilder($key))->convert()->toString(), strlen($key));
            $this->output .= $space . sprintf("<i style='color: #333;'> %s </i><i  style='color: #888a85;'>=&gt;", $str);
            $this->dumpType($val, $i);
            $this->output .= "</i> \r\n";
        }
    }

    /**
     * 自动选择类型输出
     * @param       $param
     * @param int $i
     * @return string
     */
    public function dumpType($param, int $i = 0): string
    {

        switch (gettype($param)) {
            case 'NULL' :
                $this->output .= '<span style="color: #3465a4">null</span>';
                break;
            case 'boolean' :
                $this->output .= '<small style="color: #333;font-weight: bold">boolean</small> <span style="color:#75507b">' . ($param ? 'true' : 'false') . "</span>";
                break;
            case 'integer' :
                $this->output .= "<small style='color: #333;font-weight: bold'>int</small> <i style='color:#4e9a06'>$param</i>";
                break;
            case 'double' :
                $this->output .= "<small style='color: #333;font-weight: bold'>float</small> <i style='color:#f57900'>$param</i>";
                break;
            case 'string' :
                $this->dumpString($param);
                break;
            case 'array' :
                $this->dumpArray($param, $i);
                break;
            case 'object' :
                $this->dumpObject($param, $i);
                break;
            case 'resource' :
                $this->output .= '<i style=\'color:#3465a4\'>resource</i>';
                break;
            default :
                $this->output .= '<i style=\'color:#3465a4\'>unknown type</i>';
                break;
        }
        return $this->output;
    }

    /**
     * 输出文本
     * @param $param
     */
    private function dumpString($param)
    {

        $str = sprintf("<small style='color: #333;font-weight: bold'>string</small> <i style='color:#cc0000'>'%s'</i> <i>(length=%d)</i>", htmlspecialchars((new StringBuilder($param))->convert()->toString()), strlen($param));
        $this->output .= $str;
    }

    /**
     * 输出类对象
     * @param $obj
     * @param $className
     * @param $num
     */
    public function dumpProp($obj, $className, $num)
    {
       // if ($className == get_class($obj) && $num > 2) return;
        static $pads = [];
        try {
            $reflect = new ReflectionClass($obj);
        } catch (ReflectionException $e) {
            $this->output .= $e->getMessage();
            return;
        }

        $prop = $reflect->getProperties();
        $len = count($prop);
        $this->output .= "<i style='color: #333;'> (size=$len)</i>";
        array_push($pads, "    ");
        for ($i = 0; $i < $len; $i++) {
            $index = $i;
            $prop[$index]->setAccessible(true);
            $prop_name = $prop[$index]->getName();
            $this->output .= "\n" . implode('', $pads) . sprintf("<i style='color: #333;'> %s </i><i style='color:#888a85'>=&gt;&nbsp;", $prop_name);
            $this->dumpType($prop[$index]->getValue($obj), $num);
        }
        array_pop($pads);
    }

}