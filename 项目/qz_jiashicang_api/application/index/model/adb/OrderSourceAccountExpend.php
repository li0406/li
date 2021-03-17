<?php
// +----------------------------------------------------------------------
// | OrderSourceAccountExpend
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Model;
use think\db\Query;
use app\common\model\adb\BaseModel;

class OrderSourceAccountExpend extends BaseModel {

    // 查询渠道账户消耗
    public function getAccountExpendStats($date_begin, $date_end, $accountIds = [], $authUsers = []){
        $map = new Query();
        $map->where("a.enabled", 1);
        $map->where("e.is_delete", 2);

        $map->where("e.datetime", "between", [
            strtotime($date_begin),
            strtotime($date_end)
        ]);

        if (count($accountIds) > 0) {
            $map->where("e.account_id", "in", $accountIds);
        }

        if (count($authUsers) > 0) {
            $map->where("a.creator", "in", $authUsers);
        }

        $list = $this->alias("e")->where($map)
            ->join("order_source_account a", "a.id = e.account_id", "inner")
            ->field(["e.account_id", "sum(e.expend_amount) as expend_amount"])
            ->group("e.account_id")
            ->select();

        return $list;
    }

    // 查询渠道账户消耗
    public function getAccountExpendDateStats($date_begin, $date_end, $accountIds = [], $authUsers = []){
        $map = new Query();
        $map->where("a.enabled", 1);
        $map->where("e.is_delete", 2);

        $map->where("e.datetime", "between", [
            strtotime($date_begin),
            strtotime($date_end)
        ]);

        if (count($accountIds) > 0) {
            $map->where("e.account_id", "in", $accountIds);
        }

        if (count($authUsers) > 0) {
            $map->where("a.creator", "in", $authUsers);
        }

        $list = $this->alias("e")->where($map)
            ->join("order_source_account a", "a.id = e.account_id", "inner")
            ->field([
                "FROM_UNIXTIME(e.datetime, '%Y-%m-%d') as date",
                "sum(e.expend_amount) as expend_amount"
            ])
            ->group("date")
            ->select();

        return $list;
    }
}