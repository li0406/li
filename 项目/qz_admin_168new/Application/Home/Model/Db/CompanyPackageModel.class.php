<?php
namespace Home\Model\Db;

use Think\Model;

class CompanyPackageModel extends Model {

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

    public function getNewPackageByCompanyIds($companyIds){
        $map = array(
            "company_id" => array("IN", $companyIds),
            "refund_status" => array("EQ", 1),
            "back_status" => array("EQ", 1)
        );

        $subSql = $this->where($map)
            ->field("*,IF(use_status = 3, 0, use_status) as sort")
            ->order("sort desc")
            ->buildSql();

        return M()->table($subSql)->alias("t")
            ->group("t.company_id")
            ->select();
    }

    public function getPackageInfo($id){
        $map = array(
            "p.id" => array("EQ", $id)
        );

        return $this->alias("p")->where($map)
            ->join("left join qz_company_package_info as fen on fen.package_id = p.id and fen.package_type = 1")
            ->join("left join qz_company_package_info as zen on zen.package_id = p.id and zen.package_type = 2")
            ->field([
                "p.*",
                "fen.total_number as fen_number", "fen.send_number as fen_send_number", "fen.use_status as fen_use_status",
                "zen.total_number as zen_number", "zen.send_number as zen_send_number", "zen.use_status as zen_use_status",
            ])->find();
    }

    public function getPackageList($company_id){
        $map = array(
            "p.company_id" => array("EQ", $company_id),
            "p.refund_status" => array("EQ", 1),
            "p.use_status" => array("IN", [1,2])
        );

        return $this->alias("p")->where($map)
            ->join("left join qz_company_package_info as fen on fen.package_id = p.id and fen.package_type = 1")
            ->join("left join qz_company_package_info as zen on zen.package_id = p.id and zen.package_type = 2")
            ->field([
                "p.*",
                "fen.total_number as fen_number", "fen.send_number as fen_send_number",
                "fen.use_status as fen_use_status", "fen.send_number as fen_send_number",
                "zen.total_number as zen_number", "zen.send_number as zen_send_number",
                "zen.use_status as zen_use_status", "zen.send_number as zen_send_number"
            ])
            ->order("p.use_status desc,p.id asc")
            ->select();
    }

    public function getPackageHistoryList($company_id){
        $map = array(
            "p.company_id" => array("EQ", $company_id),
            "p.refund_status" => array("EQ", 1),
            "p.use_status" => array("EQ", 3)
        );

        return $this->alias("p")->where($map)
            ->join("left join qz_company_package_info as fen on fen.package_id = p.id and fen.package_type = 1")
            ->join("left join qz_company_package_info as zen on zen.package_id = p.id and zen.package_type = 2")
            ->field([
                "p.*",
                "fen.total_number as fen_number", "fen.send_number as fen_send_number",
                "fen.use_status as fen_use_status", "fen.send_number as fen_send_number",
                "zen.total_number as zen_number", "zen.send_number as zen_send_number",
                "zen.use_status as zen_use_status", "zen.send_number as zen_send_number"
            ])
            ->order("p.id desc")
            ->select();
    }

    public function addInfo($data){
        return M("company_package_info")->add($data);
    }

    public function saveInfoData($package_id, $package_type, $data){
        $map = array(
            "package_id" => array("EQ", $package_id),
            "package_type" => array("EQ", $package_type)
        );

        return M("company_package_info")->where($map)->save($data);
    }

    public function addTotalNumber($package_id, $package_type, $total_number = 0){
        $map = array(
            "package_id" => array("EQ", $package_id),
            "package_type" => array("EQ", $package_type)
        );

        return M("company_package_info")->where($map)->setInc("total_number", $total_number);
    }

    public function addLog($data){
        return M("company_package_log")->add($data);
    }


    public function addPackageOrderRelation($data)
    {
       return M("company_package_order")->add($data);
    }

    public function editPackageOrderRelation($where,$data)
    {
        return M("company_package_order")->where($where)->save($data);
    }

    public function getPackageLogList($package_id){
        $map = array(
            "package_id" => array("EQ", $package_id),
        );
        return M("company_package_log")->where($map)->order("id desc")->select();
    }

    public function getCompanyNowPackageSignCount($use_status, $company_id){
        $map = array(
            "o.qiandan_companyid" => array("EQ", $company_id),
            "p.company_id" => array("EQ", $company_id),
            "p.use_status" => array("EQ", $use_status),
            "p.refund_status" => array("EQ", 1),
            "o.qiandan_status" => array("EQ", 1),
            "a.deleted" => array("EQ", 1),
        );

        return $this->alias("p")->where($map)
            ->join("inner join qz_company_package_order as a on a.package_id = p.id")
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->field([
                "count(a.id) as count",
                "sum(o.qiandan_jine) as qiandan_jine",
                "sum(a.back_pay_money) as back_pay_money"
            ])
            ->find();
    }

    // 会员公司当前订单包签单记录
    public function getCompanyNowPackageSignList($use_status, $company_id, $offset = 0, $limit = 0){
        $map = array(
            "o.qiandan_companyid" => array("EQ", $company_id),
            "p.company_id" => array("EQ", $company_id),
            "p.use_status" => array("EQ", $use_status),
            "p.refund_status" => array("EQ", 1),
            "o.qiandan_status" => array("EQ", 1),
            "a.deleted" => array("EQ", 1),
        );

        $subSql = $this->alias("p")->where($map)
            ->join("inner join qz_company_package_order as a on a.package_id = p.id")
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->field([
                "a.id", "a.package_id", "a.order_id", "a.back_total_money", "a.back_pay_money", "a.back_status", "p.back_ratio",
                "o.time_real", "o.qiandan_addtime", "o.qiandan_jine", "o.cs", "o.qx", "o.qiandan_mianji", "o.type_fw", "o.mianji"
            ])
            ->order("o.id desc")
            ->limit($offset, $limit)
            ->buildSql();

        return M()->table($subSql)->alias("t")
            ->join("left join qz_quyu as q on q.cid = t.cs")
            ->join("left join qz_area as area on area.qz_areaid = t.qx")
            ->field("t.*,q.cname as city_name,area.qz_area as area_name")
            ->select();
    }


    public function getPackagePageCount($company_id){
        $map = array(
            "p.company_id" => array("EQ", $company_id),
            "p.refund_status" => array("EQ", 1)
        );

        return $this->alias("p")->where($map)->count("p.id");
    }

    public function getPackagePageList($company_id, $offset = 0, $limit = 0){
        $map = array(
            "p.company_id" => array("EQ", $company_id),
            "p.refund_status" => array("EQ", 1)
        );

        return $this->alias("p")->where($map)
            ->join("left join qz_company_package_info as fen on fen.package_id = p.id and fen.package_type = 1")
            ->join("left join qz_company_package_info as zen on zen.package_id = p.id and zen.package_type = 2")
            ->field([
                "p.*",
                "fen.total_number as fen_number", "fen.send_number as fen_send_number",
                "fen.use_status as fen_use_status", "fen.send_number as fen_send_number",
                "zen.total_number as zen_number", "zen.send_number as zen_send_number",
                "zen.use_status as zen_use_status", "zen.send_number as zen_send_number"
            ])
            ->order("p.id desc")
            ->limit($offset, $limit)
            ->select();
    }
}