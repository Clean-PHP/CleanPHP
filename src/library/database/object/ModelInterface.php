<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class ModelInterface
 * Created By ankio.
 * Date : 2022/11/15
 * Time : 11:34
 * Description :
 */

namespace library\database\object;

interface ModelInterface
{
    /**
     * 获取主键
     * @return array|SqlKey
     */
    function getPrimaryKey();

}