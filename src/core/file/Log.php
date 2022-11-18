<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class Log
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 22:12
 * Description :
 */

namespace core\file;

use core\config\Config;
use core\base\Variables;


class Log
{
    private static ?Log $instance = null;
    private static int $validate = 30;
    private string $temp = "";
    private string $file = "";
    private string $tag = "";


    const TYPE_ERROR = 0;
    const TYPE_INFO = 1;
    const TYPE_WARNING = 2;
    private int $type = 1;//写入的数据类型

    public static function save()
    {
        self::$instance = null;
    }

    /**
     * 输出信息
     * @param $tag
     * @param $msg
     * @param int $type
     */
    public static function record($tag, $msg, int $type = self::TYPE_INFO)
    {
        self::getInstance($tag)->setType($type)->write($msg);
    }

    /**
     * 写入日志文件
     * @param $msg
     */
    protected function write($msg)
    {
        $m_timestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳
        $timestamp = floor($m_timestamp); // validate
        $milliseconds = round(($m_timestamp - $timestamp) * 1000); // 毫秒
        $type = $this->type === Log::TYPE_INFO?"INFO":($this->type === Log::TYPE_ERROR?"ERROR":"WARNING");
        $this->temp .= '[ ' . date('Y-m-d H:i:s',$timestamp).'.'.$milliseconds . ' ] [ ' .$type . ' ] [ ' . $this->tag . ' ] ' . $msg . "\n";
    }

    /**
     * 获取实例
     * @param $tag
     * @param string $filename
     * @return Log
     */
    public static function getInstance($tag, string $filename = "cleanphp"): Log
    {
        if (self::$instance == null) {
            self::$instance = new Log();
        }
        self::$instance->tag = $tag;
        self::$instance->file = Variables::getLogPath(date('Y-m-d'), Variables::get("__frame_log_tag__", "") . $filename . '.log');
        $dir_name = dirname(self::$instance->file);
        if (!file_exists($dir_name)) {
            File::mkDir($dir_name);
        }
        self::$validate = Config::getConfig("frame")["log"] ?? 30;
        return self::$instance;
    }

    /**
     * 当日志变量被销毁后，统一写入文件
     */
    public function __destruct()
    {
        $this->temp = "-----------[session start]-----------\n{$this->temp}-----------[session end]-----------\n\n";
        $handler = fopen(self::$instance->file, 'a');
        if (flock($handler, LOCK_EX)) {
            fwrite($handler, $this->temp, strlen($this->temp));
            flock($handler, LOCK_UN);
        }
        fclose($handler);
        //删除指定日期之前的日志
        $this->rm(date('Y-m-d', strtotime("- " . self::$validate . " day")));
    }

    /**
     * 删除日志
     * @param $date
     */
    private function rm($date = null)
    {
        if (is_dir(Variables::getLogPath($date))) {
            File::del(Variables::getLogPath($date));
        }
    }

    /**
     * 设置写入的数据类型
     * @param int $type
     * @return Log
     */
    private function setType(int $type): Log
    {
        $this->type = $type;
        return $this;
    }

}