<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\database\driver
 * Class Driver
 * Created By ankio.
 * Date : 2022/11/15
 * Time : 21:48
 * Description :
 */

namespace library\database\driver;

use PDO;

abstract class Driver implements DriverInterface
{
    protected ?PDO $pdo  = null;

}