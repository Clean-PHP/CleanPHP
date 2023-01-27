<?php
/*
 *  Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace core\event;


/**
 * Interface EventListener
 * @package core\event
 */
interface EventListener
{
    /**
     * 事件接收器
     * @param $event string 事件名
     * @param $data mixed 事件数据
     * @return mixed 返回true表示允许其他事件接收器继续执行，返回false不允许
     */
    public function handleEvent(string $event, &$data);
}
