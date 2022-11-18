<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Interface baseDriver
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 23:20
 * Description :
 */

namespace library\database\driver;

use library\database\object\DbFile;
use library\database\object\Model;
use library\database\object\SqlKey;
use library\database\operation\InsertOperation;
use PDO;

interface DriverInterface
{
    /**
     * @param DbFile $dbFile 数据库配置类型
     */
    public function __construct(DbFile $dbFile);

    /**
     * 主键渲染
     * @param Model $model
     * @param string $table
     * @return string
     */
    function renderCreateTable(Model $model,string $table): string;

    /**
     * 渲染键值
     * @param SqlKey $sqlKey
     * @return mixed
     */
    function renderKey(SqlKey $sqlKey);
    /**
     * 获取数据库链接
     * @return PDO
     */
    function getDbConnect(): PDO;

    /**
     * 清空数据表
     * @param $table string 表格
     * @return mixed
     */
    function renderEmpty(string $table);

    /**
     * 处理插入模式
     * @param $model int 从以下{@link InsertOperation::INSERT_NORMAL}、{@link InsertOperation::INSERT_DUPLICATE}、{@link InsertOperation::INSERT_IGNORE}数据中获取
     * @return string
     */
    function onInsertModel(int $model):string;


}