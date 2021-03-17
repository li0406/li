<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class UserCompanyAccount extends Model {

    protected $autoWriteTimestamp = false;

    // 添加账户信息
    public function addAccountInfo($data){
        $data["created_at"] = time();
        $data["updated_at"] = time();
        
        return $this->insert($data);
    }

    public function editInfo($company_id,$data)
    {
        $map[] = ["user_id","EQ",$company_id];
        return $this->where($map)->update($data);
    }

    // 获取账户信息
    public function getAccountInfo($user_id){
        $map = new Query();
        $map->where("a.user_id", $user_id);

        return $this->alias("a")->where($map)->find();
    }


    // 查询会员资金列表数量
    public function getAccountPageCount($input){
        $map = $this->getAccountPageMap($input);

        return $this->alias("a")->where($map)
            ->join("user u", "u.id = a.user_id", "inner")
            ->join("user_company c", "c.userid = u.id", "inner")
            ->count("u.id");
    }

    // 查询会员资金列表数据
    public function getAccountPageList($input, $offset = 0, $limit = 0){
        $map = $this->getAccountPageMap($input);

        return $this->alias("a")->where($map)
            ->join("user u", "u.id = a.user_id", "inner")
            ->join("user_company c", "c.userid = u.id", "inner")
            ->join("user_company_signback s", "s.user_id = u.id", "left")
            ->join("quyu q", "q.cid = u.cs", "left")
            ->field([
                "u.id as company_id", "u.jc as company_name", "u.`on`", "u.cs", "u.`start` as contract_start",
                "a.account_amount", "a.deposit_money", "a.round_order_number", "s.back_ratio", "q.cname as city_name"
            ])
            ->order("u.start desc,u.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 会员资金列表查询条件
    public function getAccountPageMap($input){
        $map = new Query();
        $map->where("u.classid", 3);
        $map->where("c.fake", 0);
        $map->where("c.cooperate_mode", 2);

        // 城市
        if (!empty($input["city_id"])) {
            $map->where("u.cs", $input["city_id"]);
        } else if (!empty($input["citys"])) {
            $map->where("u.cs", "in", $input["citys"]);
        }

        // 会员状态
        if (!empty($input["on_status"])) {
            $map->where("u.on", $input["on_status"]);
        } else {
            $map->where("u.on", "in", [0, 2, -1, -4]);
        }

        // 搜索
        if (!empty($input["company"])) {
            $map->where(function($query) use ($input) {
                $query->where("u.id", $input["company"]);
                $query->whereOr("u.jc", "like", "%". $input["company"] ."%");
            });
        }

        // 余额区间查询
        if (!empty($input["amount_min"])) {
            $map->where("a.account_amount", ">=", floatval($input["amount_min"]));
        }

        // 余额区间查询
        if (!empty($input["amount_max"])) {
            $map->where("a.account_amount", "<=", floatval($input["amount_max"]));
        }

        return $map;
    }

    public function setGuaranteedAmountInc($company_id,$amount)
    {
        $map[] = ["user_id","EQ",$company_id];
        return $this->where($map)->setInc("contracted_amount",$amount);
    }

    public function setGuaranteedAmountDec($company_id,$amount)
    {
        $map[] = ["user_id","EQ",$company_id];
        return $this->where($map)->setDec("contracted_amount",$amount);
    }
}