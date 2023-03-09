<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: core\extend\Json
 * Class Json
 * Created By ankio.
 * Date : 2022/5/5
 * Time : 12:22
 * Description :
 */

namespace core\base;

use stdClass;

class Json
{
    /**
     * @param $string string 需要解码的字符串
     * @param false $isArray 是否解码为数组
     * @return array|bool|float|int|mixed|stdClass|string|null
     */
    static function decode(string $string, bool $isArray = false)
    {
        return json_decode(self::removeUtf8Bom($string), $isArray);
    }

    static function removeUtf8Bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace("/^$bom/", '', $text);
    }

    /**
     * @param array $array string 需要编码的字符串
     * @return string
     */
    static function encode(array $array)
    {
        $result = json_encode($array);
        if ($result === false) {
            throw new \JsonException(json_last_error_msg());
        }
        return $result;
    }

}