<?php

namespace Home\Model\Db;
use Think\Model;

class CompanyTrackModel extends Model
{
    public function getComapnyTrackList($order_id)
    {
        $map = array(
            "a.order_id" => array("EQ",$order_id)
        );
        return $this->where($map)->alias("a")
                    ->join("join qz_user u on u.id = a.company_id")
                    ->field("company_id,track_step,track_time,track_content,u.jc,1 record_type")
                    ->order("track_time desc, a.id desc")
                    ->select();
    }
}