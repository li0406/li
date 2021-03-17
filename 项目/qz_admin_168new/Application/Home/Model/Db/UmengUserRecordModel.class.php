<?php

namespace Home\Model\Db;

use Think\Model;

class UmengUserRecordModel extends Model
{
    public function getUserDeviceTokenById($uuid)
    {
        $map = [
            "uuid" => array("EQ",$uuid),
            "device_push" => array("EQ",1),
            "end_time" => array("EGT",time())
        ];
        return $this->where($map)->field("device_token,system")->find();
    }
}