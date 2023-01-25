<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\verity
 * Class VerityObject
 * Created By ankio.
 * Date : 2022/11/21
 * Time : 00:01
 * Description :
 */

namespace library\verity;

use core\App;
use core\file\Log;
use core\objects\ArgObject;

abstract class VerityObject extends ArgObject
{

    /**
     * 获取匹配规则
     * @return array
     */
    abstract function getRules():array;

    use VerityTrait;
}