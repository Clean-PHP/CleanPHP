<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\objects
 * Class StringObject
 * Created By ankio.
 * Date : 2022/11/10
 * Time : 12:43
 * Description :
 */

namespace core\objects;

class StringBuilder
{

    private $str = "";

    public function __construct($str = "")
    {
        $this->str = $str;
    }

    /**
     * 添加函数
     * @param string $s
     * @return $this
     */
    public function append(string $s): StringBuilder
    {
        $this->str .= $s;
        return $this;
    }

    /**
     * @param string $sub_string 以$sub_string开头
     * @return bool
     */
    public function startsWith(string $sub_string): bool
    {
        return strpos($this->str, $sub_string) === 0;
    }

    /**
     * @param string $sub_string 以$sub_string结尾
     * @return bool
     */
    public function endsWith(string $sub_string): bool
    {
        return substr($this->str, strrpos($this->str, $sub_string)) === $sub_string;
    }

    /**
     * 转换为文本
     * @return mixed|string
     */
    public function toString()
    {
        return $this->str;
    }

    /**
     * 编码转换
     * @param string $encode_code 编码类型
     * @return StringBuilder
     */
    public function convert(string $encode_code = "UTF-8"): StringBuilder
    {
        $encode = mb_detect_encoding($this->str, mb_detect_order());
        if ($encode !== $encode_code)
            $this->str = mb_convert_encoding($this->str, $encode_code, $encode);
        return $this;
    }
}