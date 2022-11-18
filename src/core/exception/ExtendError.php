<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: core\exception
 * Class ExtendError
 * Created By ankio.
 * Date : 2022/11/18
 * Time : 10:43
 * Description :
 */

namespace core\exception;

use core\file\Log;

class ExtendError extends \Exception
{
    public function __construct($message, $extend_name)
    {
        Log::record("PHP Extensions",sprintf("缺少[%s]拓展",$extend_name),Log::TYPE_ERROR);
        parent::__construct($message);
    }
}