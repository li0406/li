<?php

// +----------------------------------------------------------------------
// | DepartmentIdentify 渠道部门数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class DepartmentIdentify extends BaseModel {

    public function getDepartmentList($user_id = 0){
        $map = new Query();

        $dbQuery = $this->link()->name("department_identify")->alias("d")
            ->field(["d.id", "d.name", "d.dept_belong"])
            ->order("d.id");

        if (!empty($user_id)){
            $map->where("r.user_id", $user_id);
            $dbQuery->join("order_source_relate r", "r.department_id = d.id", "inner");
        }

        $list = $dbQuery->where($map)->select();
        return $list;
    }


}