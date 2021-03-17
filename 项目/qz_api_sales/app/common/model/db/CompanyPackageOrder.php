<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class CompanyPackageOrder extends Model {

    public function findPackageOrder($order_id, $company_id){
        $map = new Query();
        $map->where("a.order_id", $order_id);
        $map->where("a.company_id", $company_id);

        return $this->alias("a")->where($map)
            ->join("company_package b", "b.id = a.package_id", "inner")
            ->field(["a.id", "a.order_id", "a.company_id", "a.package_id", "b.back_ratio"])
            ->find();
    }

    public function saveData($id, $data){
        return $this->where("id", $id)->update($data);
    }

}