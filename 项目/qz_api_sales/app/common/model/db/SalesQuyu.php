<?php

/**
 * 销售分配城市模型
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-23 16:51:22
 */

namespace app\common\model\db;

use think\db\Where;
use think\Model;

class SalesQuyu extends Model {

    protected $autoWriteTimestamp = false;

    // 获取关联城市
    public static function getNoRelationAll($to){
        // $subSql = static::name("user")->alias("u")
        //     ->join("user_company c", "u.id = c.userid")
        //     ->where("u.on", 2)->where("u.classid", 3)->where("c.fake", 0)
        //     ->field("u.cs,count(u.id) as user_count")
        //     ->group("u.cs")
        //     ->buildSql();

        return static::name("quyu")->alias("q")
            ->where("s.cs is null")
            ->join("sales_quyu s", "s.cs = q.cid and s.`to` = '$to'", "left")
            // ->join("$subSql t", "t.cs = q.cid", "left")
            // ->field("q.cid,q.cname,q.px_abc,IF(t.user_count is null, 0, t.user_count) user_count")
            ->field("q.cid,q.cname,q.px_abc")
            ->order("q.px_abc,q.cid desc")
            ->select();
    }

    // 获取关联城市
    public static function getRelationAll($to){
        return static::alias("s")->where("s.to", $to)
            ->join("quyu q", "s.cs = q.cid")
            ->field("s.`to`,q.cid,q.cname,q.px_abc")
            ->order("q.px_abc,q.cid desc")
            ->select();
    }

    // 获取所有城市
    public static function getCityAll($to){
        return static::name("quyu")->alias("q")
            ->join("sales_quyu s", "s.cs = q.cid and s.`to` = '$to'", "left")
            ->field("q.cid,q.cname,q.px_abc,IF(s.cs is null, 0, 1) is_relation")
            ->order("q.px_abc,q.cid desc")
            ->select();
    }

    // 新增记录
    public static function addRecord($to, $cs){
        $data = array(
            "to" => $to,
            "cs" => $cs,
            "created_at" => time()
        );

        return static::insertGetId($data);
    }

    // 新增多条记录
    public static function addRecords($rows){
        return static::insertAll($rows);
    }

    // 删除记录
    public static function delRecords($to, $citys){
        return static::where("to", $to)->where("cs", "in", $citys)->delete();
    }
}