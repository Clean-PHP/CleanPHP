<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

namespace library\database\object;

use core\base\Variables;
use core\config\Config;
use library\database\Db;
use PHPUnit\Framework\TestCase;
use testsObjection\UserDao;
use testsObjection\UserModel;

class DaoTest extends TestCase
{

    public function __construct()
    {
        UserDao::getInstance(UserModel::class)->dropTable();
        parent::__construct();
    }
    function testInsertUpdateDeleteSelectModel(){
        $user = new UserModel();
        $user->name = "休息休息";
        $user->password = "1234";
        //增
        $id = UserDao::getInstance()->add($user);
        //查
        $result = UserDao::getInstance()->getOne();
        $user->id = $id;
        $this->assertEquals($user,$result);
        //更新
        $old = $user;
        $user->name = "233333333";
        UserDao::getInstance()->change($old,$user);
        $this->assertEquals($user,UserDao::getInstance()->getOne());
        //删除
        UserDao::getInstance()->del($user);
        $this->assertEquals(null,UserDao::getInstance()->getOne());
        UserDao::getInstance()->add($user);
        UserDao::getInstance()->add($user);
        UserDao::getInstance()->add($user);
        UserDao::getInstance()->add($user);
    }


    function testExport(){
        $this->testInsertUpdateDeleteSelectModel();
        $db = Db::init(new DbFile(Config::getConfig("database")["main"]));//手动初始化数据库
        $db->export(Variables::getCachePath("export.sql"));

    }

}
