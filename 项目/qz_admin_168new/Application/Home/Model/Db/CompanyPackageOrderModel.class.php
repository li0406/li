<?php
namespace Home\Model\Db;

use Think\Model;

class CompanyPackageOrderModel extends Model {

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

    public function addLog($data){
        return M("company_package_order_log")->add($data);
    }

    public function getOrderLogList($package_order_id){
        $map = array(
            "package_order_id" => array("EQ", $package_order_id)
        );

        return M("company_package_order_log")->where($map)->order("id desc")->select();
    }

    public function getPackageOrderTotal($package_ids){
        $map = array(
            "a.package_id" => array("IN", $package_ids),
            "o.qiandan_status" => array("EQ", 1),
            "a.deleted" => array("EQ", 1),
        );

        return M("company_package_order")->alias("a")->where($map)
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->field([
                "a.package_id",
                "count(a.id) as count",
                "sum(o.qiandan_jine) as qiandan_jine",
                "sum(a.back_total_money) as back_total_money",
                "sum(a.back_pay_money) as back_pay_money",
            ])
            ->group("a.package_id")
            ->select();
    }

}