<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class MainApp
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 15:59
 * Description :
 */

namespace core\base;

interface  MainApp
{
    /**
     * 请求到达时
     * @return mixed
     */
    function onRequestArrive();

    /**
     * 请求结束时
     * @return mixed
     */
    function onRequestEnd();

}