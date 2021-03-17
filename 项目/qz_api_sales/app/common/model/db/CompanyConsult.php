<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class CompanyConsult extends Model {

    protected $autoWriteTimestamp = false;

    public function getInfo($id){
        $map = new Query();
        $map->where("a.id", $id);

        return $this->alias("a")->where($map)->find();
    }

    // 装企咨询详情
    public function getDetail($id){
        $map = new Query();
        $map->where("a.id", $id);

        return $this->alias("a")->where($map)
            ->join("company_ip_info b", "b.id = a.ip_id", "left")
            ->join("quyu q", "q.cid = a.cs", "left")
            ->join("area area", "area.qz_areaid = a.qx", "left")
            ->join("adminuser u", "u.id = a.operator", "left")
            ->field([
                    "a.id", "a.name as company_name", "a.add_time", "a.tel", "a.remark", "a.linkman",
                    "a.cooperation_type", "a.record_status", "a.operate_status", "a.operator", "a.operate_time",
                    "b.ip as ip_address", "q.cname as city_name", "area.qz_area as area_name", "u.`user` as operator_name"
                ])
            ->find();
    }

    // 装企咨询查询数量
    public function getConsultPageCount($input){
        $map = $this->getConsultPageMap($input);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 装企咨询查询列表数据
    public function getConsultPageList($input, $offset = 0, $limit = 0){
        $map = $this->getConsultPageMap($input);

        return $this->alias("a")->where($map)
            ->join("company_ip_info b", "b.id = a.ip_id", "left")
            ->join("quyu q", "q.cid = a.cs", "left")
            ->join("area area", "area.qz_areaid = a.qx", "left")
            ->join("adminuser u", "u.id = a.operator", "left")
            ->field([
                    "a.id", "a.name as company_name", "a.add_time", "a.tel", "a.remark", "a.linkman",
                    "a.cooperation_type", "a.record_status", "a.operate_status", "a.operator", "a.operate_time",
                    "b.ip as ip_address", "q.cname as city_name", "area.qz_area as area_name", "u.`user` as operator_name"
                ])
            ->limit($offset, $limit)
            ->order("a.add_time desc")
            ->select();
    }

    // 装企咨询查询条件
    public function getConsultPageMap($input){
        $map = new Query();

        // 城市
        if (!empty($input["city_id"])) {
            $map->where("a.cs", $input["city_id"]);
        } else if (!empty($input["citys"])) {
            $map->where("a.cs", "in", $input["citys"]);
        }

        // 合作状态
        if (!empty($input["record_status"])) {
            $map->where("a.record_status", $input["record_status"]);
        }

        // 合作类型
        if (!empty($input["cooperation_type"])) {
            $map->where("a.cooperation_type", $input["cooperation_type"]);
        }

        // 处理状态
        if (!empty($input["operate_status"])) {
            $map->where("a.operate_status", $input["operate_status"]);
        }

        // 搜索关键词（公司名称、联系方式）
        if (!empty($input["keyword"])) {
            $map->where(function($query) use ($input) {
                $query->where("a.name", "like", "%". $input["keyword"] ."%");
                $query->whereOr("a.tel", "like", "%". $input["keyword"] ."%");
            });
        }

        // 咨询日期
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $map->where("a.add_time", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);
        }

        return $map;
    }

    public function updateInfo($id, $data){
        return $this->where("id", $id)->update($data);
    }

}