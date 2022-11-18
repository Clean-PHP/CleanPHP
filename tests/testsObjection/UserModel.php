<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: testsObjection
 * Class UserModel
 * Created By ankio.
 * Date : 2022/11/16
 * Time : 09:50
 * Description :
 */

namespace testsObjection;

use library\database\object\Model;
use library\database\object\SqlKey;

class UserModel extends Model
{
    public int $id = 0;
    public string $name = "";
    public string $password = "";

    function getPrimaryKey()
    {
        return new SqlKey("id",$this->id,true);
    }


}