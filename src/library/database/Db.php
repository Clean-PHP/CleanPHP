<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: library\database
 * Class Db
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 23:19
 * Description :
 */

namespace library\database;

use core\App;
use core\base\Error;
use core\base\Variables;
use core\exception\ExitApp;
use core\exception\ExtendError;
use core\file\File;
use core\file\Log;
use library\database\driver\DriverInterface;
use library\database\object\DbFile;
use library\database\object\Model;
use PDO;
use PDOStatement;

class Db
{
    private ?DriverInterface $db = null;

    private ?DbFile $dbFile = null;

    /**
     * 使用指定数据库配置初始化数据库连接
     * @param DbFile $dbFile
     * @return Db
     */
    public static function init(DbFile $dbFile): Db
    {
        $hash = $dbFile->hash();
        $db = Variables::get($hash);
        if($db === null){
            $db = new self($dbFile);
        }
        Variables::set($hash,$db);
        return $db;
    }

    /**
     * 构造函数
     * @param DbFile $dbFile 数据库配置类
     */
    public function __construct(DbFile $dbFile)
    {
        if (!class_exists("PDO")) {
            throw new ExtendError(lang("缺少PDO拓展支持，请安装PDO拓展并启用。%s", "https://www.php.net/manual/zh/pdo.installation.php"),"pdo");
        }
       $this->dbFile = $dbFile;
       //select driver
        $drive_cls = "library\\database\\driver\\".ucfirst($dbFile->type);
        if(class_exists($drive_cls)){
            $this->db = new $drive_cls($dbFile);
        }elseif (class_exists($dbFile->type)){
            //如果此处指定数据库驱动，则尝试去加载
            $cls = $dbFile->type;
            $this->db = new $cls($dbFile);
        }else{
            Error::err(sprintf("未找到对应的数据库驱动：%s",$dbFile->type),[],"Database Driver");
        }
    }

    /**
     * 数据表初始化
     * @param string $model
     * @param string $table
     * @return void
     * @throws ExitApp
     */
    function initTable(string $model,string $table){
        App::$debug && Log::record("SQL",lang("创建数据表 `%s`",$table));
        if(class_exists($model)){
           /**@var Model $m*/ $m = new $model();
            $this->execute($this->db->renderCreateTable($m,$table));
            $m->onCreateTable($this);
        }
    }
    public function __destruct()
    {
        unset($db);
        Variables::del($this->dbFile->hash());
    }

    /**
     * 数据库执行
     * @param string $sql 需要执行的sql语句
     * @param array $params 绑定的sql参数
     * @param false $readonly 是否为查询
     * @return array|int
     */
    public function execute(string $sql, array $params = [], bool $readonly = false)
    {

        App::$debug && Variables::set("__db_sql_start__",microtime(true));
        /**
         * @var $sth PDOStatement
         */
        $sth = $this->db->getDbConnect()->prepare($sql);

        if ($sth == false){
            Error::err(sprintf("Sql语句【%s】预编译出错：%s",$sql,implode(" , ",$this->db->getDbConnect()->errorInfo())),[],"Database Execute");
        }

        if (is_array($params) && !empty($params)) foreach ($params as $k => $v) {
            if (is_int($v)) {
                $data_type = PDO::PARAM_INT;
            } elseif (is_bool($v)) {
                $data_type = PDO::PARAM_BOOL;
            } elseif (is_null($v)) {
                $data_type = PDO::PARAM_NULL;
            } else {
                $data_type = PDO::PARAM_STR;
            }

            $sth->bindValue($k, $v, $data_type);
        }
        $ret_data = null;
        if ($sth->execute()) {
            $ret_data = $readonly ? $sth->fetchAll(PDO::FETCH_ASSOC) : $sth->rowCount();
        }
        if(App::$debug){
            $end = microtime(true) -  Variables::get("__db_sql_start__");
            Variables::del("__db_sql_start__");
            $sql_default = $sql;
            foreach ($params as $k => $v) {
                $sql_default = str_replace($k, "\"$v\"", $sql_default);
            }
            Log::record("SQL",$sql_default);
            Log::record("SQL",lang("执行时间：%s 毫秒",$end * 1000));
        }
        if($ret_data!==null)return $ret_data;
        Error::err(sprintf("执行SQL语句【%s】出错：%s",$sql,implode(" , ",$sth->errorInfo())),[],"Database Execute");
        return null;
    }

    /**
     * 获取数据库驱动
     * @return DriverInterface
     */
    public function getDriver(): ?DriverInterface
    {
        return $this->db;
    }

    /**
     * 导入数据表
     * @param string $sql_path
     * @return void
     */
    public  function import(string $sql_path){
        $data = $sql_path;
        if(file_exists($data))$data = file_get_contents($data);
        $this->db->execute($data);
    }

    /**
     * 导出数据表
     * @param ?string $output 输出路径
     * @param bool $only_struct 是否只导出结构
     * @return string
     */
    public  function export(string $output = null, bool $only_struct=false): string
    {

        $result = $this->execute("show tables",[],true);
        $tabList = [];
        foreach ($result as $value){
            $tabList[] =  $value["Tables_in_dx"];
        }
        $info = "-- ----------------------------\r\n";
        $info .= "-- Powered by CleanPHP\r\n";
        $info .= "-- ----------------------------\r\n";
        $info .= "-- ----------------------------\r\n";
        $info .= "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";
        $info .= "-- ----------------------------\r\n\r\n";

        foreach($tabList as $val){
            $sql = "show create table ".$val;
            $result = $this->execute($sql,[],true);
            $info .= "-- ----------------------------\r\n";
            $info .= "-- Table structure for `".$val."`\r\n";
            $info .= "-- ----------------------------\r\n";
            $info .= "DROP TABLE IF EXISTS `".$val."`;\r\n";
            $info .=$result[0]["Create Table"].";\r\n\r\n";
        }

        if(!$only_struct){
            foreach($tabList as $val){
                $sql = "select * from ".$val;
                $result = $this->execute($sql,[],true);
                if(count($result)<1)continue;
                $info .= "-- ----------------------------\r\n";
                $info .= "-- Records for `".$val."`\r\n";
                $info .= "-- ----------------------------\r\n";

                foreach ($result as  $value){
                    $sqlStr = "INSERT INTO `".$val."` VALUES (";
                    foreach($value as  $k){
                        $sqlStr .= "'".$k."', ";
                    }
                    $sqlStr = substr($sqlStr,0,strlen($sqlStr)-2);
                    $sqlStr .= ");\r\n";
                    $info .= $sqlStr;
                }


            }
        }
        if($output!==null){
            File::mkDir(dirname($output));
            file_put_contents($output,$info);
        }

        return $info;
    }


}