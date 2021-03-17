<?php

namespace Home\Model\Db;

use Think\Model;

class HzsCompanyModel extends Model {

    public function getById($id){
        $map = array(
            "a.id" => array("EQ", $id),
        );

        return $this->where($map)->alias("a")
                    ->join("left join qz_adminuser b on b.id = a.yy_id")
                    ->field("a.*,b.name as yy_name")
                    ->find();
    }

    // 合作商列表查询条件
    private function getCompanyListMap($input){
        $map = array(
            "a.`status`" => array("EQ", 1),
        );

        if (!empty($input["name"])) {
            $map["a.name"] = array("LIKE", "%" .$input["name"]. "%");
        }

        return $map;
    }

    // 合作商列表查询总数
    public function getCompanyListCount($input){
        $map = $this->getCompanyListMap($input);
        return $this->alias("a")->where($map)->count("a.id");
    }

    // 合作商列表查询总数
    public function getCompanyList($input, $offset= 0, $limit = 0){
        $map = $this->getCompanyListMap($input);

        return $this->alias("a")->where($map)
            ->join("left join qz_adminuser as u on u.id = a.yy_id")
            ->field(["a.id", "a.`name`", "a.cooperate_mode", "a.pay_mode", "a.create_time", "u.`name` as yy_user_name"])
            ->order("a.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    public function findHzsList($query,$limit =  10)
    {
        $map = array(
            "name" => array("LIKE","%$query%")
        );
        return $this->where($map)->limit($limit)->field("id,name")->select();
    }

    public function getHzsOrder($company_id,$begin,$end)
    {
        $map = array(
            "a.id" => array("EQ",$company_id)
        );

        $buildSql = $this->where($map)->alias("a")
             ->join("left join qz_hzs_source b on a.id = b.companyid and b.status = 1")
             ->field("a.id,b.sourceid")->buildSql();

        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );
        return $this->table($buildSql)->where($map)->alias("t")
            ->join("left join qz_orders_source c on c.source_src_id = t.sourceid")
            ->join("left join qz_orders o on o.id = c.orderid")
            ->field("t.id,count(o.id) as count,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen")
            ->group("t.id")
            ->find();
    }

    public function getHzsRealOrder($company_id, $begin, $end)
    {
        $map = array(
            "a.id" => array("EQ", $company_id),
            "b.status" => array("EQ", 1),
            "s.visible" => array("EQ", 0),
            "o.is_settlement" => array("EQ", 1),
            "o.on" => array("EQ", 4),
            "csos.lasttime" => array("between", [$begin, $end])
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source b on a.id = b.companyid")
            ->join("inner join qz_order_source as s on s.id = b.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->join("inner join qz_order_csos_new as csos on csos.order_id = o.id")
            ->field(["a.id", "count(o.id) as fen"])
            ->find();
    }

    public function getHzsLiangfangOrder($company_id,$begin,$end)
    {
        $map = array(
            "a.lf_time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.status" => array("IN",array(1,2,4))
        );
        $buildSql = M("order_company_review")->alias("a")->field("a.orderid")->where($map)
                                             ->group("a.orderid")->buildSql();
        $map = array(
           "c.companyid" => array("EQ",$company_id)
        );
        return M()->table($buildSql)->alias("a")->where($map)
           ->join("join qz_orders_source b on a.orderid = b.orderid")
           ->join("join qz_hzs_source c on c.sourceid = b.source_src_id")
           ->field("count(a.orderid) as count")->find();
    }

    public function setArrearsOrderDec($id)
    {
        $map  = [
            "id" => $id
        ];
        return $this->where($map)->setDec("arrears_order");
    }

    public function setArrearsOrderInc($id)
    {
        $map  = [
            "id" => $id
        ];
        return $this->where($map)->setInc("arrears_order");
    }

    /**
     * @des:合作商管理列表
     * @param array $param
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function getHzsList($param = [], $offset = 0, $limit = 0)
    {
        $map['a.classify'] = 1;
        if (isset($param['name']) && trim($param['name'])) {
            $map["a.name"] = array("LIKE", "%" . trim($param['name'] . "%"));
        }
        if (isset($param['status']) && strlen(trim($param['status'])) > 0) {
            $map['a.status'] = $param['status'];
        }
        if (isset($param['account']) && !empty($param['account'])) {
            $map['a.account'] = $param['account'];
        }
        if (isset($param['id_arr']) && is_array($param['id_arr']) && count($param['id_arr']) > 0) {
            $map['a.id'] = array("in", $param['id_arr']);
        }
        if (isset($param['yy_id']) && trim($param['yy_id'])) {
            $map['a.yy_id'] = array("in", $param['yy_id']);
        }
        if (isset($param['id']) && trim($param['id'])) {
            $map['a.id'] = array("EQ", $param['id']);
        }
        $subSql =  $this->alias('a')->where($map)
            ->join("left join qz_adminuser as u on u.id = a.yy_id")
            ->field(["a.id","a.name","a.psw","a.short","a.account","u.name as yy_user_name","a.create_time","a.status"])
            ->buildSql();
        return $this->table($subSql)->alias('a')
            ->order("a.id")
            ->limit($offset, $limit)
            ->select();
    }

    /**
     * @des:合作商列表统计
     * @param array $param
     * @return mixed
     */
    public function getHzsListCount($param = [])
    {
        $map['a.classify'] = 1;
        if (isset($param['name']) && trim($param['name'])) {
            $map["a.name"] = array("LIKE", "%" . trim($param['name'] . "%"));
        }
        if (isset($param['status']) && strlen(trim($param['status'])) > 0) {
            $map['a.status'] = $param['status'];
        }
        if (isset($param['account']) && !empty($param['account'])) {
            $map['a.account'] = $param['account'];
        }
        if (isset($param['id_arr']) && is_array($param['id_arr']) && count($param['id_arr']) > 0) {
            $map['a.id'] = array("in", $param['id_arr']);
        }
        if (isset($param['yy_id']) && trim($param['yy_id'])) {
            $map['a.yy_id'] = array("in", $param['yy_id']);
        }
        $subSql =  $this->alias('a')->where($map)
            ->join("left join qz_adminuser as u on u.id = a.yy_id")
            ->field(["a.id","a.name","a.psw","a.short","a.account","u.name as yy_user_name","a.create_time","a.status"])
            ->buildSql();
        return $this->table($subSql)->alias('a')
            ->count();
    }

    /**
     * @des:获取合作商所有子账户数
     * @param $parent_id
     * @param $account
     * @param null $offset
     * @param null $limit
     * @param bool $type
     * @return mixed
     */
    public function getChildHzsInfo($parent_id, $account, $offset = null, $limit = null, $type = false)
    {
        $map['a.classify'] = 2;
        $map['a.status'] = 1;
        if ($type == false) {
            $map['a.parent_id'] = $parent_id;
        } else if ($type == true && is_array($parent_id) && count($parent_id) > 0) {
            $map['a.parent_id'] = array('in', $parent_id);
        }
        if(!empty($account)){
            $map['a.id'] = $account;
        }
        return $this->alias('a')->where($map)
            ->field(["a.id", "a.status", "a.name", "a.account"])
            ->order("id desc")
            ->limit($offset, $limit)
            ->select();
    }

    //获取合作商子账户个数
    public function getChildHzsInfoCount($parent_id, $account, $type = false)
    {
        $map['a.classify'] = 2;
        $map['a.status'] = 1;
        if ($type == false && !empty($parent_id)) {
            $map['a.parent_id'] = $parent_id;
        } else if (is_array($parent_id) && count($parent_id) > 0) {
            $map['a.parent_id'] = array('in', $parent_id);
        }
        if(!empty($account)){
            $map['a.id'] = $account;
        }
        $subSql = $this->alias('a')->where($map);
        return $subSql->field(["id", "status", "name", "status"])
            ->order("id")
            ->count();
    }

    /**
     * @des:获取合作商所有子账户数
     * @param array $id @合作商 id
     * @param null $account @合作商账户id
     * @return mixed
     */
    public function getChildAccountInfoByParent($id = [], $account = null)
    {
        if (!empty($account)) {
            $map['c.id'] = $account;
        }

        if (is_array($id) && count($id) > 0) {
            $map['a.id'] = array('in', $id);
        }

        $map['c.status'] = 1;
        $map['c.classify'] = 2;
        
        $list = $this->alias('c')->where($map)
            ->join("left join qz_hzs_company a on c.parent_id = a.id")
            ->join("left join qz_adminuser u on u.id = c.yy_id")
            ->field(["c.id","c.name","u.name as yy_name","c.cooperate_mode","c.pay_mode","c.create_time"])
            ->order("c.id")
            ->select();

        return $list;
    }

    public function getChildAccountInfo($id)
    {
        #$where['a.cooperate_starttime'] = array("between", [$begin, $end]);
        #$where['a.cooperate_endtime'] = array("between", [$begin, $end]);
        #$where['_logic'] = 'or';
        #$map['_complex'] = $where;
        $map['a.classify'] = 2;
        $map['a.id'] = $id;

        return $this->alias('a')->where($map)
            ->join("left join qz_adminuser u on u.id = a.yy_id")
            ->field(["a.id","a.name","u.name as yy_name","a.cooperate_mode","pay_mode"])
            ->order("a.id")
            ->select();
    }

    //根据id或账户名查询所属账户，条件必须有一个
    public function findHzsParentList($name = null, $account = null)
    {
        $map['classify'] = 1;
        $map['status'] = 1;
        if ($account) {
            $map['account'] = $account;
        } else {
            $map["name"] = array("LIKE", "%" . trim($name . "%"));
        }
        return $this->alias('a')->where($map)
            ->field(["id","name","account"])
            ->order("a.id")
            ->select();
    }

    //获取总合作商对应的渠道
    // public function getHzsSourceList(array $id_arr,$account, $source, $offset, $limit)
    // {
    //     $map['a.classify'] = 1;
    //     $map['s.status'] = 1;
    //     if (!empty($id_arr)) {
    //         $map['a.id'] = array("in", $id_arr);
    //     }
    //     if (!empty($account)) {
    //         $map['hc.id'] = array("EQ", $account);
    //     }
    //     if (!empty($source)) {
    //         $map['o.id'] = array("EQ", $source);
    //     }
    //     return $this->alias("a")->where($map)
    //         ->join("left join qz_hzs_company hc on hc.parent_id=a.id")
    //         ->join("left join qz_hzs_source s on s.companyid=hc.id")
    //         ->join('left join qz_order_source o on o.id = s.sourceid')
    //         ->field(["o.id", "o.name"])
    //         ->group("o.id")
    //         ->limit($offset, $limit)
    //         ->select();
    // }

    //获取总合作商对应渠道总数
    public function getHzsSourceListCount(array $id_arr, $account, $source)
    {
        $map['a.classify'] = array("EQ",1);
        $map['s.status'] = array("EQ",1);
        if (count($id_arr) > 0 && is_array($id_arr)) {
            $map['a.id'] = array("in", $id_arr);
        }
        if (!empty($account)) {
            $map['hc.id'] = array("EQ", $account);
        }
        if (!empty($source)) {
            $map['o.id'] = array("EQ", $source);
        }
        return $this->alias("a")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=a.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->join('left join qz_order_source o on o.id = s.sourceid')
            ->count("distinct o.id");
    }

    public function hzsUpdate($id,$input)
    {
        $map['id'] = $id;
        $input['update_time'] = time();
        return $this->where($map)->save($input);
    }


    // 获取合作商列表
    public function getHzsCompanyList($classify = 1, $yy_id = 0){
        $map = array(
            "a.classify" => array("EQ", $classify),
            "a.status" => array("EQ", 1),
        );

        if (!empty($yy_id)) {
            $map["a.yy_id"] = array("EQ", $yy_id);
        }

        return $this->alias("a")->where($map)
            ->field(["a.id", "a.account", "a.name"])
            ->order("a.id desc")
            ->select();
    }

    // 获取合作商列表
    public function getHzsSourceList($yy_id = 0){
        $map = array(
            "a.classify" => array("EQ", 2),
            "a.status" => array("EQ", 1),
            "hs.status" => array("EQ", 1),
            "s.type" => array("EQ", 1),
            "s.visible" => array("EQ", 0),
        );

        if (!empty($yy_id)) {
            $map["a.yy_id"] = array("EQ", $yy_id);
        }

        return $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->field(["s.id", "s.src", "s.name", "s.alias"])
            ->order("s.id desc")
            ->select();
    }

    // 获取合作商统计列表
    public function getHzsCompanyPageMap($input){
        $map = array(
            "a.classify" => array("EQ", 1),
            "a.status" => array("EQ", 1),
        );

        if (!empty($input["yy_id"])) {
            $map["a.yy_id"] = array("EQ", $input["yy_id"]);
        }

        if (!empty($input["company"])) {
            $map["a.id"] = array("EQ", $input["company"]);
        }

        return $map;
    }

    // 获取合作商统计列表
    public function getHzsCompanyPageCount($input){
        $map = $this->getHzsCompanyPageMap($input);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 获取合作商统计列表
    public function getHzsCompanyPageList($input, $offset = 0, $limit = 0){
        $map = $this->getHzsCompanyPageMap($input);

        return $this->alias("a")->where($map)
            ->field([
                "a.id", "a.account", "a.name"
            ])
            ->limit($offset, $limit)
            ->order("a.id desc")
            ->select();
    }


    // 获取合作商账户统计列表
    public function getHzsAccountPageMap($input){
        $map = array(
            "a.classify" => array("EQ", 2),
            "a.status" => array("EQ", 1),
        );

        if (!empty($input["yy_id"])) {
            $map["a.yy_id"] = array("EQ", $input["yy_id"]);
        }

        if (!empty($input["account"])) {
            $map["a.id"] = array("EQ", $input["account"]);
        }

        if (!empty($input["company"])) {
            $map["b.id"] = array("EQ", $input["company"]);
        }

        return $map;
    }

    // 获取合作商账户统计列表
    public function getHzsAccountPageCount($input){
        $map = $this->getHzsAccountPageMap($input);

        return $this->alias("a")->where($map)
            ->join("left join qz_hzs_company as b on b.id = a.parent_id")
            ->count("a.id");
    }

    // 获取合作商账户统计列表
    public function getHzsAccountPageList($input, $offset = 0, $limit = 0){
        $map = $this->getHzsAccountPageMap($input);

        return $this->alias("a")->where($map)
            ->join("left join qz_hzs_company as b on b.id = a.parent_id")
            ->field([
                "a.id", "a.account", "a.name"
            ])
            ->limit($offset, $limit)
            ->order("a.id desc")
            ->select();
    }

    // 获取渠道来源统计列表
    public function getHzsSourcePageMap($input){
        $map = array(
            "a.classify" => array("EQ", 2),
            "a.`status`" => array("EQ", 1),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),
        );

        if (!empty($input["yy_id"])) {
            $map["a.yy_id"] = array("EQ", $input["yy_id"]);
        }

        if (!empty($input["account"])) {
            $map["a.id"] = array("EQ", $input["account"]);
        }

        if (!empty($input["company"])) {
            $map["b.id"] = array("EQ", $input["company"]);
        }

        if (!empty($input["source"])) {
            $map["s.id"] = array("EQ", $input["source"]);
        }

        return $map;
    }

    // 获取渠道来源统计列表
    public function getHzsSourcePageCount($input){
        $map = $this->getHzsSourcePageMap($input);

        return $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("left join qz_hzs_company as b on b.id = a.parent_id")
            ->count("s.id");
    }

    // 获取渠道来源统计列表
    public function getHzsSourcePageList($input, $offset = 0, $limit = 0){
        $map = $this->getHzsSourcePageMap($input);

        return $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("left join qz_hzs_company as b on b.id = a.parent_id")
            ->field([
                "s.id", "s.src", "s.name", "s.alias", "a.`name` as account_name"
            ])
            ->limit($offset, $limit)
            ->order("s.id desc")
            ->select();
    }

    // 获取合作商uv统计
    public function getHzsCompanyUvStat($hzs_ids, $begin, $end, $yy_id = 0){
        $map = array(
            "a.id" => array("IN", $hzs_ids),
            "b.classify" => array("EQ", 2),
            "b.`status`" => array("EQ", 1),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),
            "c.dates" => array("between", [
                date("Ymd", strtotime($begin)),
                date("Ymd", strtotime($end)),
            ])
        );

        if (!empty($yy_id)) {
            $map["b.yy_id"] = array("EQ", $yy_id);
        }

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_company as b on b.parent_id = a.id")
            ->join("inner join qz_hzs_source as hs on hs.companyid = b.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_url_stats_count as c on c.src = s.src")
            ->field(["a.id as company_id", "sum(c.num) as uv"])
            ->group("a.id")
            ->select();

        return $list;
    }

    // 获取合作商账户uv统计
    public function getHzsAccountUvStat($hzs_ids, $begin, $end, $yy_id = 0){
        $map = array(
            "a.id" => array("IN", $hzs_ids),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),
            "c.dates" => array("between", [
                date("Ymd", strtotime($begin)),
                date("Ymd", strtotime($end)),
            ])
        );

        if (!empty($yy_id)) {
            $map["a.yy_id"] = array("EQ", $yy_id);
        }

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_url_stats_count as c on c.src = s.src")
            ->field(["a.id as account_id", "sum(c.num) as uv"])
            ->group("a.id")
            ->select();

        return $list;
    }

    // 获取渠道数据uv统计
    public function getHzsSourceUvStat($source_ids, $begin, $end){
        $map = array(
            "s.id" => array("IN", $source_ids),
            "s.`visible`" => array("EQ", 0),
            "c.dates" => array("between", [
                date("Ymd", strtotime($begin)),
                date("Ymd", strtotime($end)),
            ])
        );

        $list = $this->table("qz_order_source")->alias("s")->where($map)
            ->join("inner join qz_url_stats_count as c on c.src = s.src")
            ->field(["s.id as source_id", "sum(c.num) as uv"])
            ->group("s.id")
            ->select();

        return $list;
    }

    


    // 获取合作商发单统计
    public function getHzsCompanyOrderFadanStat($hzs_ids, $begin, $end, $yy_id = 0){
        $map = array(
            "a.id" => array("IN", $hzs_ids),
            "b.classify" => array("EQ", 2),
            "b.`status`" => array("EQ", 1),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),
            "o.time_real" => array("between", [
                strtotime($begin),
                strtotime($end) + 86399,
            ])
        );

        if (!empty($yy_id)) {
            $map["b.yy_id"] = array("EQ", $yy_id);
        }

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_company as b on b.parent_id = a.id")
            ->join("inner join qz_hzs_source as hs on hs.companyid = b.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->field([
                "a.id as company_id",
                "count(DISTINCT o.id) as fadan",
                "count(DISTINCT IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
                "count(DISTINCT IF(o.`on` = 4 and o.is_settlement = 1, o.id, null)) as valid"
            ])
            ->group("a.id")
            ->select();

        return $list;
    }

    // 获取合作商账户发单统计
    public function getHzsAccountOrderFadanStat($hzs_ids, $begin, $end, $yy_id = 0){
        $map = array(
            "a.id" => array("IN", $hzs_ids),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),
            "o.time_real" => array("between", [
                strtotime($begin),
                strtotime($end) + 86399,
            ])
        );

        if (!empty($yy_id)) {
            $map["a.yy_id"] = array("EQ", $yy_id);
        }

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->field([
                "a.id as account_id",
                "count(DISTINCT o.id) as fadan",
                "count(DISTINCT IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
                "count(DISTINCT IF(o.`on` = 4 and o.is_settlement = 1, o.id, null)) as valid"
            ])
            ->group("a.id")
            ->select();

        return $list;
    }

    // 获取渠道数据发单统计
    public function getHzsSourceOrderFadanStat($source_ids, $begin, $end){
        $map = array(
            "s.id" => array("IN", $source_ids),
            "s.`visible`" => array("EQ", 0),
            "o.time_real" => array("between", [
                strtotime($begin),
                strtotime($end) + 86399,
            ])
        );

        $list = $this->table("qz_order_source")->alias("s")->where($map)
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->field([
                "s.id as source_id",
                "count(DISTINCT o.id) as fadan",
                "count(DISTINCT IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
                "count(DISTINCT IF(o.`on` = 4 and o.is_settlement = 1, o.id, null)) as valid"
            ])
            ->group("s.id")
            ->select();

        return $list;
    }


    // 获取合作商实际分单统计
    public function getHzsCompanyOrderCsosStat($hzs_ids, $begin, $end, $yy_id = 0){
        $map = array(
            "a.id" => array("IN", $hzs_ids),
            "b.classify" => array("EQ", 2),
            "b.`status`" => array("EQ", 1),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),

            "o.`on`" => array("EQ", 4),
            "o.`type_fw`" => array("EQ", 1),
            "csos.lasttime" => array("between", [
                strtotime($begin),
                strtotime($end) + 86399,
            ])
        );

        if (!empty($yy_id)) {
            $map["a.yy_id"] = array("EQ", $yy_id);
        }

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_company as b on b.parent_id = a.id")
            ->join("inner join qz_hzs_source as hs on hs.companyid = b.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->join("inner join qz_order_csos_new as csos on csos.order_id = o.id")
            ->field([
                "a.id as company_id",
                "count(DISTINCT o.id) as csos_fendan",
                "count(DISTINCT IF(o.is_settlement = 1, o.id, null)) as csos_valid"
            ])
            ->group("a.id")
            ->select();

        return $list;
    }

    // 获取合作商账户实际分单统计
    public function getHzsAccountOrderCsosStat($hzs_ids, $begin, $end, $yy_id = 0){
        $map = array(
            "a.id" => array("IN", $hzs_ids),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),

            "o.`on`" => array("EQ", 4),
            "o.`type_fw`" => array("EQ", 1),
            "csos.lasttime" => array("between", [
                strtotime($begin),
                strtotime($end) + 86399,
            ])
        );

        if (!empty($yy_id)) {
            $map["a.yy_id"] = array("EQ", $yy_id);
        }

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->join("inner join qz_order_csos_new as csos on csos.order_id = o.id")
            ->field([
                "a.id as account_id",
                "count(DISTINCT o.id) as csos_fendan",
                "count(DISTINCT IF(o.is_settlement = 1, o.id, null)) as csos_valid"
            ])
            ->group("a.id")
            ->select();

        return $list;
    }

    // 获取渠道数据实际分单统计
    public function getHzsSourceOrderCsosStat($source_ids, $begin, $end){
        $map = array(
            "s.id" => array("IN", $source_ids),
            "s.`visible`" => array("EQ", 0),

            "o.`on`" => array("EQ", 4),
            "o.`type_fw`" => array("EQ", 1),
            "csos.lasttime" => array("between", [
                strtotime($begin),
                strtotime($end) + 86399,
            ])
        );

        $list = $this->table("qz_order_source")->alias("s")->where($map)
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->join("inner join qz_order_csos_new as csos on csos.order_id = o.id")
            ->field([
                "s.id as source_id",
                "count(DISTINCT o.id) as csos_fendan",
                "count(DISTINCT IF(o.is_settlement = 1, o.id, null)) as csos_valid"
            ])
            ->group("s.id")
            ->select();

        return $list;
    }


    // 获取合作商订单查询条件
    public function getHzsOrderPageMap($input){
        $map = array(
            "a.classify" => array("EQ", 2),
            "a.`status`" => array("EQ", 1),
            "hs.`status`" => array("EQ", 1),
            "s.`visible`" => array("EQ", 0),
        );

        if (!empty($input["yy_id"])) {
            $map["a.yy_id"] = array("EQ", $input["yy_id"]);
        }

        if (!empty($input["account"])) {
            $map["a.id"] = array("EQ", $input["account"]);
        }

        if (!empty($input["company"])) {
            $map["b.id"] = array("EQ", $input["company"]);
        }

        if (!empty($input["source"])) {
            $map["s.id"] = array("EQ", $input["source"]);
        }

        if (!empty($input["begin"]) && !empty($input["end"])) {
            $map["o.time_real"] = array("between", [
                strtotime($input["begin"]),
                strtotime($input["end"]) + 86399
            ]);
        }

        return $map;
    }

    // 获取合作商订单数量
    public function getHzsOrderPageCount($input){
        $map = $this->getHzsOrderPageMap($input);

        return $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->join("left join qz_hzs_company as b on b.id = a.parent_id")
            ->count("o.id");
    }

    // 获取合作商订单数据
    public function getHzsOrderPageList($input, $offset = 0, $limit = 0){
        $map = $this->getHzsOrderPageMap($input);

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_hzs_source as hs on hs.companyid = a.id")
            ->join("inner join qz_order_source as s on s.id = hs.sourceid")
            ->join("inner join qz_orders_source as os on os.source_src_id = s.id")
            ->join("inner join qz_orders as o on o.id = os.orderid")
            ->join("left join qz_hzs_company as b on b.id = a.parent_id")
            ->join("left join qz_quyu as q on q.cid = o.cs")
            ->join("left join qz_area as area on area.qz_areaid = o.qx")
            ->field([
                "o.id", "o.`on`", "o.xiaoqu", "o.type_fw", "o.time_real", "o.tel", "o.remarks",
                "os.source_src_id", "os.source_src", "s.name as source_name",
                "q.cname as city_name", "area.qz_area as area_name"
            ])
            ->limit($offset, $limit)
            ->order("o.time_real desc")
            ->select();

        return $list;
    }

}