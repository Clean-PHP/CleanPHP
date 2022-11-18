<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: testsObjection
 * Class UserDao
 * Created By ankio.
 * Date : 2022/11/16
 * Time : 09:50
 * Description :
 */

namespace testsObjection;

use library\database\object\Dao;
use library\database\object\Field;
use library\database\object\Model;

class UserDao extends Dao
{
    /**
     * @inheritDoc
     */
    function getTable(): string
    {
        return "user";
    }

    function getOne(){
        return $this->find(new Field("id","name","password"),["id"=>1]);
    }
    function add(Model $model): int
    {
        return $this->insertModel($model);
    }
    function change($old,$new){
         $this->updateModel($old,$new);
    }
    function del($model){
        $this->deleteModel($model);
    }


}