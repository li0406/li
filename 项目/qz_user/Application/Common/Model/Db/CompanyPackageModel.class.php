<?php
namespace Common\Model\Db;

use Think\Model;
/**
 *
 */
class CompanyPackageModel extends Model
{
    public function getPackageInfo($company_id)
    {
        $map = array(
            "a.company_id" => array("EQ",$company_id),
            "a.use_status" => array("IN",array(1,2))
        );

        return $this->where($map)->alias("a")
                    ->join("join qz_company_package_info b on a.id = b.package_id")
                    ->field("a.id,a.deposit_money,a.back_ratio,b.package_type,max(if(b.package_type =1,total_number,null)) as fen_total,max(if(b.package_type = 2,total_number,null)) as zen_total,max(if(b.package_type = 1,send_number,null)) as fen_send,max(if(b.package_type = 2,send_number,null)) as zen_send")
                    ->group('a.id')
                    ->order("a.use_status desc")
                    ->select();
    }

    public function getPackageQianDanInfo($company_id)
    {
        $map = array(
            "a.company_id" => array("EQ",$company_id),
            "a.use_status" => array("EQ",2)
        );
        return $this->where($map)->alias("a")
                    ->join("left join qz_company_package_order po on po.package_id = a.id")
                    ->join("left join qz_orders o on o.id = po.order_id and o.on = 4 and o.qiandan_status = 1")
                    ->field("count(o.id) as all_count,sum(o.qiandan_jine) as all_money")
                    ->group("a.company_id")->find();

    }

    public function loadpackageorderinfo($company_id)
    {
        $map = array(
            "a.company_id" => array("EQ",$company_id),
            "a.use_status" => array("EQ",2)
        );
        return $this->where($map)->alias("a")
            ->join("left join qz_company_package_order po on po.package_id = a.id and po.back_status = 1")
            ->join("left join qz_orders o on o.id = po.order_id and o.on = 4 and o.qiandan_status = 1")
            ->field("count(o.id) as count, sum(if(po.back_total_money is not null,po.back_total_money,0)) as all_money,sum(if(po.back_pay_money is not null,po.back_pay_money,0)) as pay_money")
            ->group("a.company_id")->find();
    }
}