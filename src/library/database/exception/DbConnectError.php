<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\database\exception
 * Class DbConnectError
 * Created By ankio.
 * Date : 2022/11/18
 * Time : 11:13
 * Description :
 */

namespace library\database\exception;

use core\file\Log;

class DbConnectError extends \exception
{
    public function __construct($message ,array $error,$tag)
    {
        Log::record($tag, lang("数据库连接异常：%s，异常信息：%s", $message,implode(" , ",$error)),Log::TYPE_ERROR);
        parent::__construct($message);
    }
}