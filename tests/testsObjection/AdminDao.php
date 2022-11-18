<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: testsObjection
 * Class AdminDao
 * Created By ankio.
 * Date : 2022/11/17
 * Time : 12:57
 * Description :
 */

namespace testsObjection;

use library\database\object\Dao;

class AdminDao extends Dao
{

    /**
     * @inheritDoc
     */
    function getTable(): string
    {
        return "admin";
    }

}