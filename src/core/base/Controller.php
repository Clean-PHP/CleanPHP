<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

namespace core\base;


use core\App;
use core\engine\JsonEngine;
use core\engine\ResponseEngine;
use core\engine\ViewEngine;
use core\event\EventManager;

class Controller
{


    protected string $module = "";
    protected string $controller = "";
    protected string $action = "";

    private $engine;

    private ?string $init_data;

    private string $content_type = "";
    private int $code = 200;

    public function __construct(string $m, string $c, string $a)
    {
        $this->module = $m;
        $this->controller = $c;
        $this->action = $a;
        $this->setCode($this->eng()->getCode());
        $this->setContentType($this->eng()->getContentType());
        EventManager::trigger("__on_controller_create__", $this);
        $this->init_data = $this->init();

    }

    /**
     * 获取渲染引擎
     * @return ViewEngine|JsonEngine|ResponseEngine
     */
    public function eng()
    {
        if (!$this->engine) return App::getEngine();
        return $this->engine;
    }

    /**
     * 初始化函数
     */
    public function init()
    {
        EventManager::trigger("__on_controller_init__", $this);
        return null;
    }

    /**
     * 获取初始化结果
     */
    public function getInit(): ?string
    {
        return $this->init_data;
    }

    /**
     * 设置引擎
     * @param $engine ViewEngine|JsonEngine|ResponseEngine 引擎
     * @return void
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * 获取返回的ContentType
     * @return string
     */
    public function getContentType(): string
    {
        return $this->content_type;
    }

    /**
     * 设置ContentType
     * @param $content_type
     * @return void
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
    }

    /**
     * 获取响应代码
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * 设置响应代码
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * 获取模块
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * 获取控制器
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * 获取执行方法
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }


    /**
     * 数据渲染
     * @param ...$data
     */
    public function render(...$data): string
    {
        return $this->eng()->render(...$data);
    }


}
