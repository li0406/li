<?php
// +----------------------------------------------------------------------
// | OrderSourceAccount
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Model;
use think\db\Query;
use app\common\model\adb\BaseModel;

class OrderSourceAccount extends BaseModel {

    public function getAccountAll(){
        $map = new Query();
        $map->where("a.enabled", 1);

        return $this->alias("a")->where($map)
            ->field(["a.id", "a.account_name"])
            ->order("a.id desc")
            ->select();
    }

    // 市场渠道ROI账户查询条件
    public function getAccountRoiPageMap($input){
        $map = new Query();
        $map->where("a.enabled", 1);

        if (!empty($input["account_ids"])) {
            $map->where("a.id", "in", $input["account_ids"]);
        }

        if (!empty($input["auth_users"])) {
            $map->where("a.creator", "in", $input["auth_users"]);
        }

        return $map;
    }

    // 查询市场渠道ROI账户数量
    public function getAccountRoiPageCount($input){
        $map = $this->getAccountRoiPageMap($input);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 查询市场渠道ROI账户数据
    public function getAccountRoiPageList($input, $offset = 0, $limit = 0){
        $map = $this->getAccountRoiPageMap($input);

        return $this->alias("a")->where($map)
            ->field(["a.id", "a.account_name"])
            ->limit($offset, $limit)
            ->order("a.id desc")
            ->select();
    }

    // 获取账户关联的渠道标识数量
    public function getAccountRelatedStats($accountIds = [], $authUsers = []){
        $map = new Query();
        $map->where("a.enabled", 1);
        $map->where("s.visible", 0);
        $map->where("s.type", 1);

        if (count($accountIds) > 0) {
            $map->where("a.id", "in", $accountIds);
        }

        if (count($authUsers) > 0) {
            $map->where("a.creator", "in", $authUsers);
        }

        return $this->alias("a")->where($map)
            ->join("order_source s", "s.account_id = a.id", "inner")
            ->field(["a.id as account_id", "count(s.id) as src_number"])
            ->group("a.id")
            ->select();
    }

    // 查询订单统计（发单量、分单量）
    public function getAccountOrderStats($date_begin, $date_end, $accountIds = [], $authUsers = []){
        $map = new Query();
        $map->where("s.type", 1);
        $map->where("s.visible", 0);
        $map->where("a.enabled", 1);

        $map->where("o.time_real", "between", [
            strtotime($date_begin),
            strtotime($date_end) + 86399
        ]);

        if (count($accountIds) > 0) {
            $map->where("a.id", "in", $accountIds);
        }

        if (count($authUsers) > 0) {
            $map->where("a.creator", "in", $authUsers);
        }

        $list = $this->alias("a")->where($map)
            ->join("order_source s", "s.account_id = a.id", "inner")
            ->join("orders_source os", "os.source_src = s.src", "inner")
            ->join("orders o", "o.id = os.orderid", "inner")
            ->join("order_info i", "i.`order` = o.id and i.type_fw = 1", "left")
            ->field([
                "a.id as account_id",
                "count(DISTINCT o.id) as fadan",
                "count(DISTINCT IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
                "sum(IF(i.cooperate_mode in(1, 3), i.order_amount, 0)) as order_user_amount",
                "sum(IF(i.cooperate_mode in(2, 4), i.order_amount, 0)) as order_sback_amount",
            ])
            ->group("a.id")
            ->select();

        return$list;
    }

    // 查询订单日统计（发单量、分单量）
    public function getAccountOrderDateStats($date_begin, $date_end, $accountIds = [], $authUsers = []){
        $map = new Query();
        $map->where("s.type", 1);
        $map->where("s.visible", 0);
        $map->where("a.enabled", 1);

        $map->where("o.time_real", "between", [
            strtotime($date_begin),
            strtotime($date_end) + 86399
        ]);

        if (count($accountIds) > 0) {
            $map->where("a.id", "in", $accountIds);
        }

        if (count($authUsers) > 0) {
            $map->where("a.creator", "in", $authUsers);
        }

        $list = $this->alias("a")->where($map)
            ->join("order_source s", "s.account_id = a.id", "inner")
            ->join("orders_source os", "os.source_src = s.src", "inner")
            ->join("orders o", "o.id = os.orderid", "inner")
            ->join("order_info i", "i.`order` = o.id and i.type_fw = 1", "left")
            ->field([
                "FROM_UNIXTIME(o.time_real, '%Y-%m-%d') as date",
                "count(DISTINCT o.id) as fadan",
                "count(DISTINCT IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
                "sum(IF(i.cooperate_mode in(1, 3), i.order_amount, 0)) as order_user_amount",
                "sum(IF(i.cooperate_mode in(2, 4), i.order_amount, 0)) as order_sback_amount",
            ])
            ->group("date")
            ->select();

        return$list;
    }





}