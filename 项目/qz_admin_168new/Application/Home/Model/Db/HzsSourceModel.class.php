<?php

namespace Home\Model\Db;

use Think\Model;

class HzsSourceModel extends Model {

    // 获取渠道商source
    public function getSourceListByCompanyIds($companyIds){
        $map = array(
            "companyid" => array("IN", $companyIds),
            "status" => array("EQ", 1)
        );

        return $this->where($map)->field(["id", "companyid", "sourceid"])->select();
    }

    // 获取合作商source
    public function getSourceListByCompanyId($companyId){
        $map = array(
            "companyid" => array("EQ", $companyId),
            "status" => array("EQ", 1)
        );

        return $this->where($map)->field(["id", "companyid", "sourceid"])->select();
    }

    // 合作商渠道查询条件
    private function getSourceListMap($companyid, $input){
        $map = array(
            "s.`status`" => array("EQ", 1),
            "s.companyid" => array("EQ", $companyid),
        );

        if (!empty($input["src"])) {
            $map["os.src"] = array("EQ", $input["src"]);
        }

        if (!empty($input["sname"])) {
            $map["os.name"] = array("EQ", $input["sname"]);
        }

        return $map;
    }

    // 合作商渠道查询数量
    public function getSourceListCount($companyid, $input){
        $map = $this->getSourceListMap($companyid, $input);

        return $this->alias("s")->where($map)
            ->join("inner join qz_order_source as os on os.id = s.sourceid")
            ->count("s.id");
    }

    // 合作商渠道查询列表
    public function getSourceList($companyid, $input, $offset = 0, $limit = 0){
        $map = $this->getSourceListMap($companyid, $input);

        return $this->alias("s")->where($map)
            ->join("inner join qz_order_source as os on os.id = s.sourceid")
            ->join("left join qz_order_source_group as g on g.id = os.groupid")
            ->field([
                "s.id", "s.sourceid", "s.companyid",
                "os.`name` as sname", "os.src", "os.charge", "g.`name` as group_name"
            ])
            ->order("s.id desc")
            ->select();
    }
}