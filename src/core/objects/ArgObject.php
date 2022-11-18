<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\objects
 * Class ArgObject
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 21:08
 * Description :
 */

namespace core\objects;

class ArgObject
{
    /**
     * 将数组转换为对象，使其更具表现力
     * @param $item array 数组
     */
    public function __construct(array $item = [])
    {
        foreach (get_object_vars($this) as $key => $val) {
            if (isset($item[$key])) {
                $data = $item[$key];
                if(!$this->onParseType($key,$data,$val)){
                    $this->$key = parse_type($val, $data);
                }else{
                    $this->$key = $data;
                }

            }
        }
    }


    /**
     * 转化为数组
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * 当进行数组转换为object的时候，可重写该方法进行数据校验
     * @param string $key 对象属性名
     * @param mixed &$val 对象属性值，传入的是地址，直接修改即可
     * @param mixed $demo 默认属性值
     * @return bool 返回true表示修改，返回false表示不修改
     */
    public function onParseType(string $key, &$val, $demo): bool
    {
        return false;
    }

    /**
     * 获取对象hash值
     * @return string
     */
    public function hash():string{
        return md5(implode(",",get_object_vars($this)));
    }
}