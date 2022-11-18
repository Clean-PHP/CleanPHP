<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: testsObjection
 * Class AdminModel
 * Created By ankio.
 * Date : 2022/11/17
 * Time : 12:57
 * Description :
 */

namespace testsObjection;

use library\database\object\Model;
use library\database\object\SqlKey;

class AdminModel extends Model
{

    public int $id = 0;
    public string $login = "";
    public string $token = "";
    /**
     * @inheritDoc
     */
    function getPrimaryKey()
    {
        return [new SqlKey("id",$this->id,true),new SqlKey("login",$this->login,false,32)];
    }


}