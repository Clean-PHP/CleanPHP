<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: core\base
 * Class Model
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 23:35
 * Description :
 */

namespace library\database\object;

use core\objects\ArgObject;

abstract class Model extends ArgObject
{
    private bool $fromDb = false;

    public function __construct(array $item = [], $fromDb = false)
    {
        $this->fromDb = $fromDb;
        parent::__construct($item);
    }

    /**
     * @return bool
     */
    public function isFromDb(): bool
    {
        return $this->fromDb;
    }

    /**
     * 获取主键
     * @return array|SqlKey
     */
    abstract function getPrimaryKey();

    /*  function copy($new): Model
      {
          $ret = get_object_vars($new);
          $old_ = get_object_vars($this);
          $cls = new (get_class($this));
          foreach ($ret as $key => $value) {
              if(in_array($key,$old_) && $cls->$key !== $value){
                  $this->$key = $value;
              }
          }
          return $this;
      }*/

}