<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use app\common\model\logic\TeamLogic;

class SaleReportUnpassLog extends Model {

    protected $autoWriteTimestamp = false;

    public function addLog($data){
        return $this->insert($data);
    }

    // 报备不通过查询数量
    public function getUnpassLogPageCount($input){
        $map = $this->getUnpassLogPageMap($input);

        return $this->alias("a")->where($map)
            ->join("adminuser u1", "u1.id = a.report_saler", "left")
            ->count("a.id");
    }

    // 报备不通过查询列表
    public function getUnpassLogPageList($input, $offset = 0, $limit = 0){
        $map = $this->getUnpassLogPageMap($input);

        return $this->alias("a")->where($map)
            ->join("adminuser u1", "u1.id = a.report_saler", "left")
            ->join("adminuser u2", "u2.id = a.exam_operator", "left")
            ->field([
                    "a.id", "a.report_id", "a.cooperation_type", "a.report_type", "a.company_name",
                    "a.report_time", "a.exam_time", "a.exam_remark",
                    "a.report_saler", "u1.`user` as report_saler_name",
                    "a.exam_operator", "u2.`user` as exam_operator_name"
                ])
            ->order("a.exam_time desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 报备不通过查询条件
    public function getUnpassLogPageMap($input){
        $map = new Query();

        // 报备类型
        if (!empty($input["report_type"])) {
            $map->where("a.report_type", $input["report_type"]);
        }

        // 公司名称
        if (!empty($input["company_name"])) {
            $map->where("a.company_name", "like", "%". $input["company_name"] ."%");
        }

        // 报备人
        if (!empty($input["saler_name"])) {
            $map->where("u1.user", "like", "%". $input["saler_name"] ."%");
        }

        if(!empty($input["user_ids"])){
            $map->where('a.report_saler', 'IN', implode(',', $input["user_ids"]));
        }

        return $map;
    }
}