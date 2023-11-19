<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */

/*
 * Package: cleanphp\objects
 * Class ArgObject
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 21:08
 * Description :
 */

namespace cleanphp\base;

class ArgObject
{
    /**
     * 初始化
     * @param array|null $item
     */
    public function __construct(?array $item = [])
    {
        if (!empty($item)) {
            foreach (get_object_vars($this) as $key => &$val) {
                $data = $val;
                if (array_key_exists($key, $item)) {
                    $data = $item[$key];
                }
                if ($this->onParseType($key, $data, $val)) {
                    $data = parse_type($val, $data);
                    if (gettype($val) === gettype($data)) {
                        $this->$key = $data;
                    }
                }
            }
        }
    }

    /**
     * 当准备进行格式化的时候，该函数会在__construct初始化参数时进行调用
     * @param string $key 当前初始化的key
     * @param mixed $val 当前初始化要赋予的值
     * @param mixed $demo 初始化对象的默认值
     * @return bool 是否允许写入到对象中，返回false是不允许
     */
    public function onParseType(string $key, mixed &$val, mixed $demo): bool
    {
        if (is_bool($demo)) {
            $val = ($val === "1" || $val === 1 || $val === "true" || $val === "on" || $val === true);
        }
        return true;
    }

    /**
     * 获取这个对象的hash值
     * @return string
     */
    public function hash(): string
    {
        return md5(implode(",", get_object_vars($this)));
    }

    /**
     * @param ArgObject|array|null $object
     * @return void
     */
    public function merge(ArgObject|array|null $object): void
    {
        if ($object === null) return;
        if ($object instanceof ArgObject) {
            $object = $object->toArray(false);
        }
        $disable = array_merge($this->getDisableKeys(), ['id']);
        foreach ($this->toArray(false) as $key => $val) {
            if (array_key_exists($key, $object) && !in_array($key, $disable)) {
                $data = $object[$key];
                if ($this->onMerge($key, $val, $data) && $this->onParseType($key, $data, $val)) {
                    $this->$key = $data;
                    continue;
                }
            }
            $this->onMergeFailed($key, $val, $object);
        }
    }

    /**
     * 将object对象转换为数组
     * @param bool $callback 是否对每一项进行回调
     * @return array
     */
    public function toArray(bool $callback = true): array
    {
        $ret = get_object_vars($this);
        if (!$callback) return $ret;
        array_walk($ret, function (&$value, $key, $arr) {
            $this->onToArray($key, $value, $arr['ret']);
        }, ['ret' => &$ret]);
        return $ret;
    }

    /**
     * 在将object对象转换为数组的过程中，对每一项进行回调
     * @param $key string  当前的key值
     * @param $value mixed 当前转为数组的值
     * @param $ret [] 当前初始化后的数组
     * @return void
     */
    public function onToArray(string $key, mixed &$value, &$ret): void
    {
        if (is_bool($value)) {
            $value = $value ? 1 : 0;
        }
    }

    /**
     * 在调用merge的时候，返回哪些字段不允许合并
     * @return array
     */
    public function getDisableKeys(): array
    {
        return [];
    }

    /**
     *准备合并到对象的时候调用
     * @param $key string 当前的Key
     * @param $raw mixed 原始的值
     * @param $val mixed 准备合并的值
     * @return bool 返回true允许合并
     */
    public function onMerge(string $key, mixed $raw, mixed &$val): bool
    {
        return true;
    }

    /**
     * 不合并到对象的时候调用
     * @param $key string 没有合并的key
     * @param $raw mixed 没有合并的原始值
     * @param $object array 欲合并的对象（数组形式）
     * @return void
     */
    public function onMergeFailed(string $key, mixed $raw, array $object): void
    {

    }
}
