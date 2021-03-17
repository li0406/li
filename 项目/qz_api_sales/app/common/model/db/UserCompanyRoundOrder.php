<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class UserCompanyRoundOrder extends Model {

    // 获取轮单信息
    public function findRoundOrderInfo($order_id, $user_id){
        $map = new Query();
        $map->where("a.user_id", $user_id);
        $map->where("a.order_id", $order_id);

        return $this->alias("a")->where($map)
            ->field(["a.id", "a.order_id", "a.user_id", "a.back_ratio"])
            ->find();
    }

    // 保存数据
    public function saveData($id, $data){
        return $this->where("id", $id)->update($data);
    }

    // 获取订单签单信息（订单维度）
    public function getRoundOrderSignInfo($order_id, $checked = true){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.id", $order_id);

        if ($checked == true) {
            $map->where("o.qiandan_status", 1);
            $map->where("o.qiandan_companyid", ">", 0);
        }

        return Db::name("orders")->alias("o")->where($map)
            ->join("user_company_round_order a", "o.id = a.order_id and o.qiandan_companyid = a.user_id", "left")
            ->join("user u", "u.id = o.qiandan_companyid", "left")
            ->join("quyu q1", "q1.cid = o.cs", "left")
            ->join("quyu q2", "q2.cid = u.cs", "left")
            ->field([
                    "o.id as order_id", "o.time_real", "o.cs as city_id", "q1.cname as city_name",
                    "o.qiandan_jine", "o.qiandan_mianji", "o.qiandan_addtime", "o.qiandan_chktime", "o.qiandan_status",
                    "o.qiandan_companyid", "o.qiandan_companyid as company_id", "u.qc as company_name",
                    "u.cs as user_city", "q2.cname as user_city_name", "a.back_ratio",
                ])
            ->find();
    }

    // 查询订单签单数量（订单维度）
    public function getRoundOrderSignPageCount($input){
        $map = $this->getRoundOrderSignPageMap($input);

        return Db::name("orders")->alias("o")->where($map)
            ->join("user u", "u.id = o.qiandan_companyid")
            ->count("o.id");
    }

    // 查询订单签单数据（订单维度）
    public function getRoundOrderSignPageList($input, $offset = 0, $limit = 0){
        $map = $this->getRoundOrderSignPageMap($input);

        $subSql = Db::name("orders")->alias("o")->where($map)
            ->join("user u", "u.id = o.qiandan_companyid")
            ->field([
                "o.id as order_id", "o.xiaoqu", "o.mianji", "o.name", "o.sex", "o.time_real", "o.type_fw",
                "o.qiandan_mianji", "o.qiandan_jine", "o.qiandan_status", "o.qiandan_addtime", "o.qiandan_chktime", "o.qiandan_companyid",
                "o.cs as city_id", "o.qx as area_id", "u.jc as company_jc", "u.qc as company_qc", "u.cs as user_city"
            ])
            ->order("o.qiandan_chktime desc")
            ->limit($offset, $limit)
            ->buildSql();

        return Db::table($subSql)->alias("t")
            ->join("user_company_round_order r", "r.order_id = t.order_id and r.user_id = t.qiandan_companyid", "left")
            ->join("order_info i", "i.`order` = t.order_id and i.`com` = t.qiandan_companyid")
            ->join("quyu q", "q.cid = t.city_id", "left")
            ->join("quyu q2", "q2.cid = t.user_city", "left")
            ->join("area area", "area.qz_areaid = t.area_id", "left")
            ->field([
                    "t.*", "i.type_fw as company_type_fw", "r.back_ratio",
                    "q.cname as city_name", "area.qz_area as area_name", "q2.cname as user_city_name"
                ])
            ->order("t.qiandan_chktime desc")
            ->select();
    }

    // 订单签单查询条件（订单维度）
    public function getRoundOrderSignPageMap($input){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.qiandan_status", 1);
        $map->where("o.qiandan_companyid", ">", 0);

        if (!empty($input["city_id"])) {
            $map->where("o.cs", $input["city_id"]);
        }

        if (!empty($input["keyword"])) {
            $map->where(function($query) use ($input) {
               $query->where("o.id", $input["keyword"]);
               $query->whereOr("u.jc", $input["keyword"]);
               $query->whereOr("o.xiaoqu", "like", "%" . $input["keyword"] . "%");
            });
        }

        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $map->where("o.qiandan_addtime", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);
        }

        return $map;
    }

    // 获取装修公司签单统计
    public function getCompanyOrderSignTotal($company_id){
        $map = $this->getCompanyOrderSignMap($company_id);

        return $this->alias("a")->where($map)
            ->join("orders o", "o.id = a.order_id", "inner")
            ->join("order_info i", "i.`order` = o.id and i.`com` = o.qiandan_companyid", "inner")
            ->join("user u", "u.id = a.user_id", "inner")
            ->field([
                "count(a.id) as sign_number",
                "sum(o.qiandan_jine) as qiandan_jine",
                "a.user_id as company_id",
                "u.jc as company_name"
            ])->find();
    }

    // 查询会员公司签单记录数量
    public function getCompanyOrderSignCount($company_id){
        $map = $this->getCompanyOrderSignMap($company_id);

        return $this->alias("a")->where($map)
            ->join("orders o", "o.id = a.order_id", "inner")
            ->join("order_info i", "i.`order` = o.id and i.`com` = o.qiandan_companyid", "inner")
            ->join("user u", "u.id = a.user_id", "inner")
            ->count("a.id");
    }

    // 查询会员公司签单记录数据
    public function getCompanyOrderSignList($company_id, $offset = 0, $limit = 0){
        $map = $this->getCompanyOrderSignMap($company_id);

        return $this->alias("a")->where($map)
            ->join("orders o", "o.id = a.order_id", "inner")
            ->join("order_info i", "i.`order` = o.id and i.`com` = o.qiandan_companyid", "inner")
            ->join("user u", "u.id = a.user_id", "inner")
            ->join("quyu q", "q.cid = o.cs", "left")
            ->join("area area", "area.qz_areaid = o.qx", "left")
            ->field([
                "a.user_id as company_id", "a.back_ratio", "a.back_total_money",
                "o.id as order_id", "i.type_fw as company_type_fw", "o.cs", "o.qx", "o.mianji",
                "o.qiandan_addtime", "o.qiandan_jine", "o.qiandan_mianji",
                "q.cname as city_name", "area.qz_area as area_name"
            ])
            ->order("o.qiandan_addtime desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 会员公司签单记录查询条件
    public function getCompanyOrderSignMap($company_id){
        $map = new Query();
        $map->where("a.user_id", $company_id);
        $map->where("o.qiandan_companyid", $company_id);
        $map->where("o.qiandan_status", 1);

        return $map;
    }
}