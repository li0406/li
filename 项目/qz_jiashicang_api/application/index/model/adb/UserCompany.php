<?php

// +----------------------------------------------------------------------
// | UserCompany 会员公司数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class UserCompany extends BaseModel {

    // 查询城市1.0会员数量
    public function getCityUserCount($city_ids = []) {
        $map = new Query();
        $map->where("u.on", 2);
        $map->where("c.fake", 0);
        $map->where("c.cooperate_mode", 1);
        $map->where("u.classid", "in", [3, 6]);

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        $list = $this->link()->name("user")->alias("u")->where($map)
            ->join("qz_user_company c", "c.userid = u.id", "inner")
            ->field([
                "u.cs as city_id",
                "count(u.id) as vip_count",
                "sum(c.viptype) as vip_num"
            ])
            ->group("u.cs")
            ->select();

        return$list;
    }

    /**
     * @des:获取1.0实时会员
     * @param $param
     * @return mixed
     */
    public function getUserAmountV1($param)
    {
        $map = new Query();
        $map->where('c.fake', '=', 0);
        $map->where('c.cooperate_mode', '=', 1);
        $map->where('u.classid', '=', 3);
        $map->where('u.on', '=', 2);
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            if (!in_array('000001', $param['cs'])) {
                $map->where('u.cs', 'in', $param['cs']);
            }
        }
        return $this->link()->table('qz_user')->alias('u')->where($map)
            ->join('qz_user_company c', 'c.userid=u.id', 'inner')
            ->sum("c.viptype");
    }

    /**
     * @des:获取2.0实时会员
     * @param array $param
     * @return mixed
     */
    public function getUserAmountV2($param = [])
    {
        $map = new Query();
        $map->where('c.fake', '=', 0);
        $map->where('c.cooperate_mode', '=', 2);
        $map->where('u.classid', '=', 3);
        $map->where('u.on', '=', 2);
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('u.cs', 'in', $param['cs']);
        }
        return $this->link()->table('qz_user')->alias('u')->where($map)
            ->join('qz_user_company c', 'c.userid=u.id', 'inner')
            ->count("distinct c.userid");
    }


    // 检索装修公司
    public function getCompanySearchList($input, $limit = 0){
        $map = new Query();
        $map->where("u.classid", "in", [3, 6]);
        $map->where("u.on", "in", [-1, 2, -4]);

        // 城市
        if (!empty($input["city_ids"])) {
            $map->where("u.cs", "in", $input["city_ids"]);
        }

        // 装修公司ID查询
        if (!empty($input["company_id"])) {
            $map->where("u.id", $input["company_id"]);
        }

        // 装修公司ID/简称查询
        if (!empty($input["keyword"])) {
            $map->where(function($query) use ($input) {
                $query->where("u.id", $input["keyword"]);
                $query->whereOr("u.jc", "like", "%" . $input["keyword"] . "%");
            });
        }

        return $this->link()->name("user")->alias("u")->where($map)
            ->join("user_company uc","uc.userid = u.id", "inner")
            ->join("quyu q", "q.cid = u.cs", "left")
            ->field([
                "u.id as company_id", "u.jc as company_jc", "u.qc as company_qc",
                "u.on as user_on", "uc.cooperate_mode",
                "u.cs as city_id", "q.cname as city_name"
            ])
            ->order("u.on desc,u.id desc")
            ->limit($limit)
            ->select();
    }

    // 会员详情查询条件
    public function getCompanyDetailedMap($datetime, $input){
        $map = new Query();
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);

        // 如果查询结束时间是今天则查询实时会员状态
        if ($datetime == strtotime(date("Y-m-d"))) {
            $map->where("u.on", 2);
        } else {
            $map->where(function($query){
                $query->where(function($qv1){
                    $qv1->where("uc.cooperate_mode", 1)->where("v1.vip_status", 2);
                });

                $query->whereOr(function($qv2){
                    $qv2->where("uc.cooperate_mode", 2)->where("v2.vip_status", 2);
                });
            });
        }

        // 装修公司ID查询
        if (!empty($input["company_id"])) {
            $map->where("u.id", $input["company_id"]);
        }

        // 城市查询
        if (!empty($input["city_ids"])) {
            $map->where("u.cs", "in", $input["city_ids"]);
        }

        return $map;
    }

    // 查询会员详情数量
    public function getCompanyDetailedCount($datetime, $input){
        $map = $this->getCompanyDetailedMap($datetime, $input);

        return $this->link()->name("user")->alias("u")->where($map)
            ->join("user_company uc","uc.userid = u.id", "inner")
            ->join("user_company_expend v1", "v1.company_id = u.id and v1.datetime = {$datetime}", "left")
            ->join("user_company_vip_status v2", "v2.company_id = u.id and v2.datetime = {$datetime}", "left")
            ->count("u.id");
    }

    // 查询会员详情数据
    public function getCompanyDetailedList($datetime, $input, $offset = 0, $limit = 0){
        $map = $this->getCompanyDetailedMap($datetime, $input);

        $orderMap = new Query();
        $orderMap->where("o.on", 4);
        $orderMap->where("i.type_fw", "in", [1, 2]);
        $orderMap->where("i.addtime", ">=", strtotime($input["date_begin"]));
        $orderMap->where("i.addtime", "<=", strtotime($input["date_end"]));

        $orderSql = $this->link()->name("orders")->alias("o")->where($orderMap)
            ->join("order_info i", "i.order = o.id", "inner")
            ->join("order_company_review r", "r.orderid = i.order and r.comid = i.com", "left")
            ->field([
                "i.com as company_id",
                "count(i.id) as all_num",
                "count(if(i.type_fw = 1, 1, null)) as fen_num",
                "count(if(i.type_fw = 2, 1, null)) as zen_num",
                "count(if(i.type_fw = 1 and r.status in(1, 2, 4), 1, null)) as fen_lf_num",
                "count(if(i.type_fw = 1 and o.qiandan_status >= 0 and o.qiandan_companyid = i.com, 1, null)) as fen_qiandan_num",
                "count(if(i.type_fw = 2 and o.qiandan_status >= 0 and o.qiandan_companyid = i.com, 1, null)) as zen_qiandan_num",
                "sum(if(i.type_fw = 1 and o.qiandan_status >= 0 and o.qiandan_companyid = i.com, o.qiandan_jine, 0)) as fen_qiandan_amount",
                "count(if(i.type_fw = 1 and o.lx = 1 and o.lxs = 3, 1, null)) as fen_jugai_num",
            ])
            ->group("i.com")
            ->buildSql();

        return $this->link()->name("user")->alias("u")->where($map)
            ->join("user_company uc","uc.userid = u.id", "inner")
            ->join("user_company_expend v1", "v1.company_id = u.id and v1.datetime = {$datetime}", "left")
            ->join("user_company_vip_status v2", "v2.company_id = u.id and v2.datetime = {$datetime}", "left")
            ->join("quyu q", "q.cid = u.cs", "left")
            ->join("{$orderSql} t1", "t1.company_id = u.id", "left")
            ->field([
                "u.id as company_id", "u.jc as company_jc", "u.qc as company_qc", "uc.cooperate_mode",
                "v2.account_amount", "v2.deposit_amount",
                "u.cs as city_id", "q.cname as city_name",
                "t1.all_num", "t1.fen_num", "t1.zen_num", "t1.fen_lf_num", "t1.fen_jugai_num",
                "t1.fen_qiandan_num", "t1.zen_qiandan_num", "t1.fen_qiandan_amount",
            ])
            ->limit($offset, $limit)
            ->order("t1.all_num desc,u.id desc")
            ->select();
    }
}