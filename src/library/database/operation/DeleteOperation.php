<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\database\operation
 * Class DeleteOperation
 * Created By ankio.
 * Date : 2022/11/16
 * Time : 18:19
 * Description :
 */

namespace library\database\operation;

use core\exception\ExitApp;

class DeleteOperation extends BaseOperation
{

    public function __construct(&$db,&$dao,$model)
    {
        parent::__construct($db,$dao,$model);
        $this->opt = [];
        $this->opt['type'] = 'delete';
        $this->bind_param = [];

    }

    /**
     * 修改Where语句
     * @param array $conditions
     * @return $this
     */
    public function where(array $conditions): DeleteOperation
    {
        return parent::where($conditions);
    }

    protected function translateSql()
    {
        $sql = $this->getOpt('DELETE FROM', 'table_name');
        $sql .= $this->getOpt('WHERE', 'where');
        $this->tra_sql = $sql . ";";
    }

    /**
     * 提交查询语句
     */
    public function commit(){
        return parent::__commit();
    }
}