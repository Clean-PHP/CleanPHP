<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: core\web
 * Class Arguments
 * Created By ankio.
 * Date : 2022/11/11
 * Time : 07:47
 * Description :
 */

namespace core\base;

class Argument
{
    /**
     * 从get参数中获取
     * @param ?string $key
     * @param mixed $default
     * @return bool|float|int|mixed|string|null
     */
    static function get(string $key = null, $default = null)
    {
        if ($key === null) return $_GET;
        if (isset($_GET[$key])) {
            return parse_type($default, $_GET[$key]);
        }
        return $default;
    }

    /**
     * 从post参数中获取
     * @param ?string $key
     * @param mixed $default
     * @return bool|float|int|mixed|string|null
     */
    static function post(string $key = null, $default = null)
    {
        if ($key === null) return $_POST;
        if (isset($_POST[$key])) {
            return parse_type($default, $_POST[$key]);
        }
        return $default;
    }

    /**
     * 所有参数
     * @param ?string $key
     * @param mixed $default
     * @return bool|float|int|mixed|string|null
     */
    static function arg(string $key = null, $default = null)
    {
        if ($key === null) return array_merge(self::get(), self::post());
        return self::get($key) ?? self::post($key) ?? $default;
    }

    /**
     * 获取json数组
     * @return array
     */
    static function json(): array
    {
        $raw = self::raw();
        if ($raw === null) return [];
        return Json::decode($raw, true);
    }

    /**
     * 获取原始数据
     * @return ?string
     */
    static function raw(): ?string
    {
        return file_get_contents("php://input") ?? null;
    }
}