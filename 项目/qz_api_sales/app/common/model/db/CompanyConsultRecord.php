<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class CompanyConsultRecord extends Model {

    protected $autoWriteTimestamp = false;

    // 查询装企咨询历史记录数量
    public function getRecordPageCount($consult_id){
        $map = $this->getRecordPageMap($consult_id);

        return $this->alias("a")->where($map)
            ->join("company_consult b", "b.id = a.consult_id", "inner")
            ->count("a.id");
    }

    // 查询装企咨询历史记录数据
    public function getRecordPageList($consult_id, $offset = 0, $limit = 0){
        $map = $this->getRecordPageMap($consult_id);

        return $this->alias("a")->where($map)
            ->join("company_consult b", "b.id = a.consult_id", "inner")
            ->join("department d", "d.id = a.dept", "left")
            ->field([
                    "a.id", "a.consult_id", "a.deal_man", "a.address", "a.communication", "a.success_level",
                    "a.deal_type", "a.status", "a.time", "d.`name` as dept_name"
                ])
            ->order("a.time desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 装企咨询历史记录查询条件
    public function getRecordPageMap($consult_id){
        $map = new Query();
        $map->where("a.consult_id", $consult_id);

        return $map;
    }
    
}