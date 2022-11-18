<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

namespace core\cache;

use core\base\Variables;
use library\redis\RedisCache;
use PHPUnit\Framework\TestCase;

/**
 * Package: core\cache
 * Class CacheTest
 * Created By ankio.
 * Date : 2022/11/15
 * Time : 16:52
 * Description : 缓存测试
 */
class CacheTest extends TestCase
{
    private $data = [];
    public function __construct(?string $name = null, array $data = [], $dataName = '')
  {
      parent::__construct($name, $data, $dataName);

      $this->data[uniqid()] = 1;
      $this->data[uniqid()] = "";
      $this->data[uniqid()] = [uniqid()=>uniqid(),uniqid()];
      $this->data[uniqid()] = json_decode('{"code":1}');
      $this->data[uniqid()] = new Cache();

  }

    /**
     * 测试设置缓存
     * @return void
     */
    public function testSetCacheAndGetCache()
    {
        foreach ($this->data as $key => $val){
            $id = uniqid();
            Cache::init(3000,Variables::getCachePath($id))->set($key,$val);
            $this->assertEquals($val,Cache::init(3000,Variables::getCachePath($id))->get($key));
            Cache::init(3000,Variables::getCachePath($id))->del($key);
        }

    }

    /**
     * 测试使用Redis设置缓存
     * @return void
     */
    public function testUseRedis(){
        Cache::setDriver(new RedisCache());
        $this->testSetCacheAndGetCache();
    }

}
