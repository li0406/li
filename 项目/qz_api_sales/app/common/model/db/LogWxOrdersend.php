<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class LogWxOrdersend extends Model {

    protected $autoWriteTimestamp = false;
    
    /**
     * 查询列表总数
     * @param  [type]  $type    [description]
     * @param  [type]  $options [description]
     * @param  integer $offset  [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getList($type, $options, $offset = 0, $limit = 0){
        $dbQuery = static::alias("a")->order("a.time_add desc");

        // 订单号
        if (!empty($options["orderid"])) {
            $dbQuery->where("a.orderid", $options["orderid"]);
        }

        // 装修公司简称
        if (!empty($options["company_jc"])) {
            $dbQuery->join("user u", "FIND_IN_SET(u.id, a.userid)", "left")->group("a.id");
            $dbQuery->whereRaw(sprintf("position('%s' IN u.jc)", $options["company_jc"]));
        }

        // 装修公司ID
        if (!empty($options["company_id"])) {
            if (!empty($options["company_jc"])) {
                $dbQuery->where("u.id", $options["company_id"]);
            } else {
                $dbQuery->whereRaw(sprintf("FIND_IN_SET('%s', a.userid)", $options["company_id"]));
            }
        }

        // 时间
        if (!empty($options["time_begin"]) && !empty($options["time_end"])) {
            $dbQuery->where("a.time_add", "between", array($options["time_begin"], $options["time_end"]));
        }

        if ($type == "count") {
            $result = $dbQuery->count("a.id");
        } else {
            $result = $dbQuery->field("a.*")->limit($offset, $limit)->select();
        }

        return $result;
    }

}