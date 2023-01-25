<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

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
use library\database\Db;

abstract class Model extends ArgObject
{
    /**
     * 获取主键
     * @return array|SqlKey
     */
    abstract function getPrimaryKey();

}