<?php

// +----------------------------------------------------------------------
// | Clickhouse数据库连接
// +----------------------------------------------------------------------
// | Author: 米兰的小铁匠
// +----------------------------------------------------------------------

namespace Util;

use think\facade\Log;
use think\facade\Config;

use think\Exception;
use ClickHouseDB\Client;
use InvalidArgumentException;
use ClickHouseDB\Exception\DatabaseException;
use ClickHouseDB\Exception\QueryException;

class ClickhouseLink {

    private $db;

    private static $_instance;

    private function __construct(){
        try {
            $config = Config::get('clickhouse.');

            $this->db = new Client($config);
            $this->db->database($config['database']);

        } catch (InvalidArgumentException $e) {
            $errmsg = 'clickHouse连接失败：' . $e->getMessage();
            Log::write('error', $errmsg);
            throw new Exception($errmsg, 5000001);
        }
    }

    private function __clone(){
        // 覆盖clone方法禁止clone
    }

    public static function instance(){
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function db(){
        return $this->db;
    }

    public function fetchOne($sql){
        try {
            $statement = $this->db->select($sql);
            $data = $statement->fetchOne();
        } catch (QueryException $e) {
            $errmsg = 'clickHouse执行失败：' . $e->getMessage();
            Log::write('error', $errmsg);
            throw new Exception($errmsg, 5000001);
        }

        return $data;
    }

    public function selectRows($sql){
        try {
            $statement = $this->db->select($sql);
            $data = $statement->rows();

        } catch (QueryException $e) {
            $errmsg = 'clickHouse执行失败：' . $e->getMessage();
            Log::write('error', $errmsg);
            throw new Exception($errmsg, 5000001);
        }

        return $data;
    }

    public function selectList($sql, $hash_key = "id"){
        try {
            $statement = $this->db->select($sql);
            $data = $statement->rowsAsTree($hash_key);

        } catch (QueryException $e) {
            $errmsg = 'clickHouse执行失败：' . $e->getMessage();
            Log::write('error', $errmsg);
            throw new Exception($errmsg, 5000001);
        }

        return $data;
    }

    public function count($sql)
    {
        try {
            $statement = $this->db->select($sql);
            $data = $statement->rowsAsTree('data');

            if (array_key_exists("count",$data["data"])) {
               return $data["data"]["count"];
            }

        } catch (QueryException $e) {
            $errmsg = 'clickHouse执行失败：' . $e->getMessage();
            Log::write('error', $errmsg);
            throw new Exception($errmsg, 5000001);
        }
        return 0;
    }
}