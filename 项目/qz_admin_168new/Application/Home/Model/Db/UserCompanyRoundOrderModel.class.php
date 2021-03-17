<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyRoundOrderModel extends Model {

    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }

    public function delInfoByUserId($order_id, $user_id)
    {
        $map = array(
            "order_id" => array("EQ", $order_id),
            "user_id" => array("IN", $user_id),
        );
        return $this->where($map)->delete();
    }

    public function getById($id){
        $map = array(
            "id" => array("EQ", $id)
        );
        return $this->where($map)->find();
    }

    public function saveData($id, $data){
        $map = array(
            "id" => array("EQ", $id)
        );

        return $this->where($map)->save($data);
    }

    public function editNewQianOrderMap($company,$order_id,$data)
    {
        $map = array(
            "user_id" => array("IN", $company),
            "order_id" => array("EQ",$order_id)
        );
        return $this->where($map)->save($data);
    }

    public function getCompanySignCount($user_id){
        $map = array(
            "a.user_id" => array("EQ", $user_id),
            "o.qiandan_companyid" => array("EQ", $user_id),
            "o.qiandan_status" => array("EQ", 1)
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->field([
                "count(a.id) as count",
                "sum(o.qiandan_jine) as qiandan_jine"
            ])->find();
    }

    public function getCompanySignList($user_id, $offset = 0, $limit = 0){
        $map = array(
            "a.user_id" => array("EQ", $user_id),
            "o.qiandan_companyid" => array("EQ", $user_id),
            "o.qiandan_status" => array("EQ", 1),
        );

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->join("inner join qz_order_info as i on i.`order` = o.id and i.`com` = o.qiandan_companyid")
            ->field([
                "a.*", "i.type_fw as company_type_fw",
                "o.time_real", "o.qiandan_addtime", "o.qiandan_jine", "o.cs", "o.qx", "o.qiandan_mianji", "o.type_fw", "o.mianji"
            ])
            ->order("o.qiandan_addtime desc")
            ->limit($offset, $limit)
            ->buildSql();

        return M()->table($subSql)->alias("t")
            ->join("left join qz_quyu as q on q.cid = t.cs")
            ->join("left join qz_area as area on area.qz_areaid = t.qx")
            ->field(["t.*", "q.cname as city_name", "area.qz_area as area_name"])
            ->order("t.qiandan_addtime desc")
            ->select();
    }

    public function addLog($data){
        return M("user_company_round_order_log")->add($data);
    }

    public function getOrderLogList($round_order_id){
        $map = array(
            "round_order_id" => array("EQ", $round_order_id)
        );

        return M("user_company_round_order_log")->where($map)->order("id desc")->select();
    }

    public function getPaymentMoneyTotal($user_id){
        $map = array(
            "a.user_id" => array("EQ", $user_id),
            "o.qiandan_companyid" => array("EQ", $user_id),
            "o.qiandan_status" => array("EQ", 1),
            "p.cooperation_type" => array("EQ", 8),
            "p.exam_status" => array("EQ", 3),
            "p.is_delete" => array("EQ", 1),
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->join("inner join qz_sale_report_payment as p on p.order_id = a.order_id")
            ->field([
                "count(p.id) as count",
                "sum(p.payment_total_money) as total_money"
            ])->find();
    }

	 public function getCompanyRoundOrderList($user_id, $order_id){
        $map = array(
            "order_id" => array("EQ", $order_id),
            "user_id" => array("IN", $user_id),
        );
        return $this->where($map)->select();
    }
}