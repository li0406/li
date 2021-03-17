<?php
// +----------------------------------------------------------------------
// | OrderSourceAccount
// +----------------------------------------------------------------------

namespace app\index\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class OrderSourceAccount extends Model {

    // 获取所有渠道账户
    public function getAccountAll($userIds = []){
        $map = new Query();
        $map->where("a.enabled", 1);

        if (count($userIds) > 0) {
            $map->where("a.creator", "in", $userIds);
        }

        return $this->alias("a")->where($map)
            ->field(["a.id", "a.account_name"])
            ->order("a.id desc")
            ->select();
    }

    // 根据账户名称查找
    public function getByAccount($account_name){
        $map = new Query();
        $map->where("a.account_name", $account_name);

        return $this->alias("a")->where($map)
            ->field(["a.id", "a.account_name", "a.enabled"])
            ->find();
    }

}