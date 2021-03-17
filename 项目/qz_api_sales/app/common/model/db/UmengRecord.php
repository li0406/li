<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\Db\Query;

class UmengRecord extends Model {

    public function getCompanyDeviceToken($companyIds){
        $map = new Query();
        $map->where("comid", "in", $companyIds);
        $map->where("employee_id", 0);
        $map->where("start_time", "<=", time());
        $map->where("end_time", ">=", time());

        return Db::name("umeng_record")->where($map)
            ->field("comid,GROUP_CONCAT(device_token) as device_tokens")
            ->group("comid")
            ->select();
    }

}