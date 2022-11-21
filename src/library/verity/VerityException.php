<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\verity
 * Class VerityException
 * Created By ankio.
 * Date : 2022/11/21
 * Time : 00:12
 * Description :
 */

namespace library\verity;

use Throwable;

class VerityException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}