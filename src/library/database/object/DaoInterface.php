<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Interface DaoInterface
 * Created By ankio.
 * Date : 2022/11/15
 * Time : 21:15
 * Description :
 */

namespace library\database\object;

interface DaoInterface
{
    /**
     * 当前操作的表
     * @return string
     */
    function getTable():string;
}