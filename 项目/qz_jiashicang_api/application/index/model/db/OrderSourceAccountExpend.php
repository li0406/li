<?php
// +----------------------------------------------------------------------
// | OrderSourceAccountExpend
// +----------------------------------------------------------------------

namespace app\index\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class OrderSourceAccountExpend extends Model {

    public function getAccountExpendMap($input){
        $map = new Query();
        $map->where("is_delete", 2);

        // 账户查询
        if (!empty($input["account_id"])) {
            $map->where("e.account_id", $input["account_id"]);
        }

        // 日期查询
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $map->where("e.datetime", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"])
            ]);
        }

        // 权限用户
        if (!empty($input["auth_users"])) {
            $map->where("a.creator", "in", $input["auth_users"]);
        }

        return $map;
    }

    public function getAccountExpendCount($input){
        $map = $this->getAccountExpendMap($input);

        return $this->alias("e")->where($map)
            ->join("order_source_account a", "a.id = e.account_id", "inner")
            ->count("e.id");
    }

    public function getAccountExpendList($input, $offset = 0, $limit = 0){
        $map = $this->getAccountExpendMap($input);

        return $this->alias("e")->where($map)
            ->join("order_source_account a", "a.id = e.account_id", "inner")
            ->join("adminuser u", "u.id = e.operator", "left")
            ->field([
                "e.id", "e.datetime", "e.account_id", "e.expend_amount", "e.updated_at",
                "a.account_name", "e.operator", "u.`user` as operator_name"
            ])
            ->limit($offset, $limit)
            ->order("e.id desc")
            ->select();
    }

    // 通过ID查询
    public function getById($id){
        $map = new Query();
        $map->where("e.id", $id);

        return $this->alias("e")->where($map)
            ->join("order_source_account a", "a.id = e.account_id", "inner")
            ->field(["e.*", "a.creator as account_creator"])
            ->find();
    }

    // 通过组合索引查询
    public function getByUnique($account_id, $datetime){
        $map = new Query();
        $map->where("account_id", $account_id);
        $map->where("datetime", $datetime);

        return $this->where($map)->find();
    }


    public function addInfo($data){
        return $this->insertGetId($data);
    }

    public function addAllInfo($data){
        return $this->insertAll($data);
    }

    public function updateInfo($id, $data){
        $map = new Query();
        $map->where("id", $id);

        return $this->where($map)->update($data);
    }

}