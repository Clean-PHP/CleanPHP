<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\exception
 * Class RouteError
 * Created By ankio.
 * Date : 2022/11/12
 * Time : 16:28
 * Description :
 */

namespace core\exception;


use core\file\Log;
use Exception;

class ControllerError extends Exception
{
    public string $__module = "";
    public string $__controller = "";
    public string $__action = "";
    public bool $_controller_exist = false;

    /**
     * @param string $message
     * @param string $__module
     * @param string $__controller
     * @param string $__action
     * @param boolean $_controller_exist 是否存在
     */
    public function __construct($message = "", $__module = "", $__controller = "", string $__action = "", bool $_controller_exist = false)
    {
        $this->__action = $__action;
        $this->__controller = $__controller;
        $this->__module = $__module;
        $this->_controller_exist = $_controller_exist;
        parent::__construct($message);
    }
}