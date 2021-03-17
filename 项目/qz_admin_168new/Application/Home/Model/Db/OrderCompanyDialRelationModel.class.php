<?php

namespace Home\Model\Db;
use Think\Model;

class OrderCompanyDialRelationModel extends Model
{
    public function getZixunCompanyInfo($order_id)
    {
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return $this->where($map)->alias("a")
                    ->join("join qz_user u on u.id = a.company_id")
                    ->field("u.jc")->select();
    }
}