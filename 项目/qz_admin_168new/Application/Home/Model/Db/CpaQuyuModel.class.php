<?php

namespace Home\Model\Db;
use Think\Model;

class CpaQuyuModel extends Model
{
    public function getCityInfo($city_id)
    {
        $map["cid"] = array("EQ",$city_id);

        return M("cpa_quyu")->where($map)->find();
    }
}