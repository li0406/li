<?php

namespace Home\Model\Db;

use Think\Model;

class OrderSourceAccountModel extends Model {

    // 获取账户信息
    public function getInfoById($id){
        $map = array(
            "id" => array("EQ", $id)
        );

        return $this->where($map)->find();
    }

    // 获取账户信息
    public function getInfoByAccount($account_name){
        $map = array(
            "account_name" => array("EQ", $account_name)
        );

        return $this->where($map)->find();
    }

    // 获取所有账户
    public function getAccountAll(){
        $map = array(
            "a.enabled" => array("EQ", 1)
        );

        $result = $this->alias("a")->where($map)
            ->field(["a.id", "a.account_name"])
            ->order("id desc")
            ->select();

        return $result;
    }

    // 渠道账户查询条件
    public function getAccountMap($input){
        $map = array();

        if (!empty($input["account_name"])){
            $map["a.account_name"] = array("LIKE", "%{$input["account_name"]}%");
        }

        if (!empty($input["enabled"])){
            $map["a.enabled"] = array("EQ", $input["enabled"]);
        }

        return $map;
    }

    // 查询渠道账户数量
    public function getAccountCount($input){
        $map = $this->getAccountMap($input);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 查询渠道账户数量
    public function getAccountList($input, $offset = 0, $limit = 0){
        $map = $this->getAccountMap($input);

        $list = $this->alias("a")->where($map)
            ->join("left join qz_adminuser as u on u.id = a.creator")
            ->field(["a.*", "u.`user` as creator_name"])
            ->limit($offset, $limit)
            ->order("a.id desc")
            ->select();

        return $list;
    }

    // 添加数据
    public function addInfo($data){

        return $this->add($data);
    }

    // 编辑数据
    public function updateInfo($id, $data){
        $map = array(
            "id" => array("EQ", $id)
        );

        return $this->where($map)->save($data);
    }

    // 获取关联的渠道标识数量
    public function getRelatedSrcNumbers($account_ids) {
        $map = array(
            "s.account_id" => array("IN", $account_ids),
            "s.`type`" => array("EQ", 1),
            "s.visible" => array("EQ", 0)
        );

        $list = M("order_source")->alias("s")->where($map)
            ->field(["s.account_id", "count(s.id) as src_number"])
            ->group("s.account_id")
            ->select();

        return $list;
    }

}