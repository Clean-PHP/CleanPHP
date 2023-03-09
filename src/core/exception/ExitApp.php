<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: core\exception
 * Class ExitAppException
 * Created By ankio.
 * Date : 2022/11/10
 * Time : 20:25
 * Description :
 */

namespace core\exception;

use Exception;

class ExitApp extends Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}