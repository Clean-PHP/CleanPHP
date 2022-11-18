<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

/**
 * 事件管理器
 * Class EventManager
 */

namespace core\event;


use core\base\Variables;

/**
 * Class EventManager
 * @package core\event
 * Date: 2020/11/20 12:13 上午
 * Author: ankio
 * Description: 事件管理器
 */
class EventManager
{
    protected static array $events = [];

    /**
     * 监听事件
     * @param string $event_name 事件名
     * @param string $listener 监听器名
     * @param int $level 事件等级
     */
    public static function addListener(string $event_name, string $listener, int $level = 1000)
    {
        while (isset(self::$events[$event_name][$level])) {
            $level++;
        }
        //一个事件名绑定多个监听器
        self::$events[$event_name][$level] = $listener;
    }


    /**
     * 删除事件
     * @param $event_name
     */
    public static function removeListener($event_name)
    {
        unset(self::$events[$event_name]);
    }

    /**
     * 触发事件
     * @param string $event_name 事件名
     * @param mixed    &$data 事件携带的数据
     * @param bool $once 只获取一个有效返回值
     */
    public static function trigger(string $event_name, &$data = null, bool $once = false)
    {
        if (!isset(self::$events[$event_name])) return null;
        $list = self::$events[$event_name];
        $results = [];
        foreach ($list as $key => $event) {
            if (!class_exists($event)) {
                unset(self::$events[$event_name][$key]);
                continue;
            }
            $results[$key] = (new $event())->handleEvent($event_name, $data);
            if (false === $results[$key] || (!is_null($results[$key]) && $once)) {
                break;
            }
        }
        return $once ? end($results) : $results;
    }
}

