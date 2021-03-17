<?php

// +----------------------------------------------------------------------
// | 订单数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class OrderSource extends BaseModel {

    // 渠道来源组/发单位置分组
    public function getSourceGroupList($type = 1){
        $map = new Query();
        $map->where("a.category", 1);

        if ($type == 2) {
            $map->where("a.type", 2);
            $map->where("b.parentid", 0);
        } else {
            $map->where("a.type", 1);
        }

        $list = $this->link()->name("order_source_group")->alias("a")->where($map)
            ->join("order_source_group b", "b.id = a.parentid", "left")
            ->field([
                "a.id", "a.name", "a.parentid", "b.`name` as parent_name",
                "IF(a.parentid > 0, a.parentid, a.id) as px"
            ])
            ->order("px asc,a.id asc")
            ->select();

        return $list;
    }

    // 渠道来源检索
    public function getSrcSearchList($keyword, $user_id = 0){
        $map = new Query();
        $map->where("s.type", 1);
        $map->where("s.visible", 0);

        if (!empty($keyword)) {
            $map->where("s.name", "like", "%{$keyword}%");
        }

        $dbQuery = $this->link()->name("order_source")->alias("s")
            ->field(["s.src", "s.name", "s.alias"])
            ->order("s.id desc");

        if (!empty($user_id)) {
            $map->where("r.user_id", $user_id);
            $dbQuery->join("department_identify d", "d.id = s.dept", "inner")
                ->join("qz_order_source_relate r", "r.department_id = d.id", "inner");
        }

        $list = $dbQuery->where($map)->select();

        return $list;
    }

    // 发单位置检索
    public function getSourceSearchList($keyword){
        $map = new Query();
        $map->where("s.type", 2);
        $map->where("s.visible", 0);

        if (!empty($keyword)) {
            $map->where("s.name", "like", "%{$keyword}%");
        }

        $list = $this->link()->name("order_source")->alias("s")->where($map)
            ->field(["s.src", "s.name"])
            ->order("s.id desc")
            ->select();

        return $list;
    }

    // 获取下级ID
    public function getGroupChildrenList($id, $type = 1){
        $map = new Query();
        $map->where("a.type", $type);
        $map->where("a.parentid", $id);
        $map->where("a.category", 1);

        $list = $this->link()->name("order_source_group")->alias("a")->where($map)
            ->join("order_source_group b", "b.id = a.parentid", "inner")
            ->field(["a.id", "a.name", "a.parentid", "b.name as parent_name"])
            ->order("a.id asc")
            ->select();

        return $list;
    }

    // 数据位置统计-按发单位置-查询条件
    public function getSourceOrderDetailedMap($input){
        $map = new Query();
        $map->where("s.type", 2);
        $map->where("s.visible", 0);

        // 发单量查询条件
        $fadanMap = new Query();

        // 实际分单查询条件
        $csosMap = new Query();
        $csosMap->where("o.on", 4);

        // 量房查询条件
        $lfMap = new Query();
        $lfMap->where("o.on", 4);

        // 签单查询条件
        $qiandanMap = new Query();
        $qiandanMap->where("o.on", 4);
        $qiandanMap->where("o.qiandan_status", ">=", 0);
        $qiandanMap->where("o.qiandan_companyid", ">", 0);

        // 补轮查询条件
        $roundMap = new Query();
        $roundMap->where("o.on", 4);

        $roundChildMap = new Query();

        // 订单时间查询
        if (!empty($input["date_begin"]) && $input["date_end"]) {
            // 发单时间
            $fadanMap->where("o.time_real", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);

            // 实际分单时间
            $csosMap->where("csos.lasttime", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);

            // 用户点击量房时间
            $lfMap->where("r.lf_time", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);

            // 签单申请时间
            $qiandanMap->where("o.qiandan_addtime", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);

            // 补轮申请时间
            $roundChildMap->where("a.exam_time", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);
        }

        // 来源组
        if (!empty($input["group_id"])) {
            if (is_array($input["group_id"])) {
                $map->where("s.groupid", "in", $input["group_id"]);
            } else {
                $map->where("s.groupid", $input["group_id"]);
            }
        }

        // 发单位置标识
        if (!empty($input["source"])) {
            $map->where("s.src", $input["source"]);
        }

        return [
            "map" => $map,
            "fadanMap" => $fadanMap,
            "csosMap" => $csosMap,
            "lfMap" => $lfMap,
            "qiandanMap" => $qiandanMap,
            "roundMap" => $roundMap,
            "roundChildMap" => $roundChildMap,
        ];
    }

    // 数据位置统计-按发单位置-基础查询sql
    public function getSourceOrderDetailedSql($input){
        $map = $this->getSourceOrderDetailedMap($input);

        // 发单量统计
        $fadanSql = $this->link()->name("orders")->alias("o")->where($map["fadanMap"])
            ->field([
                "o.source",
                "count(o.id) as fadan",
                "count(IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
            ])
            ->group("o.source")
            ->buildSql();

        // 实际分单量统计
        $csosSql = $this->link()->name("orders")->alias("o")->where($map["csosMap"])
            ->join("order_csos_new csos", "csos.order_id = o.id", "inner")
            ->join("order_info i", "i.`order` = o.id", "left")
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "left")
            ->field([
                "o.source",
                "count(DISTINCT IF(o.type_fw = 1, o.id, null)) as csos_fendan",
                "count(DISTINCT IF(o.type_fw = 2, o.id, null)) as csos_zendan",
                "count(DISTINCT IF(o.type_fw = 1 and r.`status` in(1, 2, 4), o.id, null)) as csos_fen_liangfang",
                "count(DISTINCT IF(o.type_fw = 2 and r.`status` in(1, 2, 4), o.id, null)) as csos_zen_liangfang",
                "count(DISTINCT IF(o.type_fw = 1 and o.qiandan_status >= 0 and o.qiandan_companyid > 0, o.id, null)) as csos_fen_qiandan",
                "count(DISTINCT IF(o.type_fw = 2 and o.qiandan_status >= 0 and o.qiandan_companyid > 0, o.id, null)) as csos_zen_qiandan",
            ])
            ->group("o.source")
            ->buildSql();

        // 量房统计
        $lfSql = $this->link()->name("orders")->alias("o")->where($map["lfMap"])
            ->join("order_info i", "i.`order` = o.id", "inner")
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "inner")
            ->field([
                "o.source",
                "count(DISTINCT IF(r.`status` in(1, 2, 4), o.id, null)) as liangfang"
            ])
            ->group("o.source")
            ->buildSql();

        // 签单统计
        $qiandandSql = $this->link()->name("orders")->alias("o")->where($map["qiandanMap"])
            ->field([
                "o.source",
                "count(DISTINCT o.id) as qiandan",
                "sum(o.qiandan_jine) as qiandan_amount"
            ])
            ->group("o.source")
            ->buildSql();

        // 补轮统计
        $roundSql = $this->link()->name("orders")->alias("o")->where($map["roundMap"])
            ->join("order_info i", "i.order = o.id", "inner")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id and a.exam_status = 2", "inner")
            ->field([
                "a.order_id", "o.source",
                "sum(r.round_money) as round_amount",
                "min(a.exam_time) as exam_time"
            ])
            ->group("a.order_id")
            ->buildSql();

        $roundSql = $this->link()->table($roundSql)->alias("a")->where($map["roundChildMap"])
            ->field([
                "a.source",
                "count(a.order_id) as round_num",
                "sum(a.round_amount) as round_amount",
            ])
            ->group("a.source")
            ->buildSql();


        // 查询扩展
        $subSql = $this->link()->name("order_source")->alias("s")->where($map["map"])
            ->join("order_source_group g", "g.id = s.groupid", "left")
            ->join("order_source_group gp", "gp.id = g.parentid", "left")
            ->join([$fadanSql => "fd"], "fd.source = s.src", "left")
            ->join([$csosSql => "csos"], "csos.source = s.src", "left")
            ->join([$lfSql => "lf"], "lf.source = s.src", "left")
            ->join([$qiandandSql => "qd"], "qd.source = s.src", "left")
            ->join([$roundSql => "round"], "round.source = s.src", "left")
            ->field([
                "s.src as source", "s.name",
                "g.name as group_name", "gp.name as group_parent_name",
                "IFNULL(fd.fadan, 0) as fadan",
                "IFNULL(fd.fendan, 0) as fendan",
                "IFNULL(csos.csos_fendan, 0) as csos_fendan",
                "IFNULL(csos.csos_zendan, 0) as csos_zendan",
                "IFNULL(csos.csos_fen_liangfang, 0) as csos_fen_liangfang",
                "IFNULL(csos.csos_zen_liangfang, 0) as csos_zen_liangfang",
                "IFNULL(csos.csos_fen_qiandan, 0) as csos_fen_qiandan",
                "IFNULL(csos.csos_zen_qiandan, 0) as csos_zen_qiandan",
                "IFNULL(lf.liangfang, 0) as liangfang",
                "IFNULL(qd.qiandan, 0) as qiandan",
                "IFNULL(qd.qiandan_amount, 0) as qiandan_amount",
                "IFNULL(round.round_num, 0) as round_num",
                "IFNULL(round.round_amount, 0) as round_amount",
            ])
            ->having("fadan + csos_fendan + liangfang + qiandan > 0")
            ->buildSql();

        return $subSql;
    }

    // 数据位置统计-按发单位置-查询数量
    public function getSourceOrderDetailedCount($input){
        $sql = $this->getSourceOrderDetailedSql($input);

        $count = $this->link()->table($sql)->alias("t")->count("t.source");
        return $count;
    }

    // 数据位置统计-按发单位置-查询数据
    public function getSourceOrderDetailedList($input, $offset = 0, $limit = 0){
        $sql = $this->getSourceOrderDetailedSql($input);

        $order = "t.csos_fendan desc,t.source desc";
        if (!empty($input["sort_field"])) {
            $sort_rule = sys_check_variable($input["sort_rule"]) == "asc" ? "asc" : "desc";
            switch ($input["sort_field"]) {
                case "fadan":
                    $order = "t.fadan {$sort_rule},t.source desc";
                    break;
                case "csos_fendan":
                    $order = "t.csos_fendan {$sort_rule},t.source desc";
                    break;
                case "liangfang":
                    $order = "t.liangfang {$sort_rule},t.source desc";
                    break;
                case "qiandan":
                    $order = "t.qiandan {$sort_rule},t.source desc";
                    break;
            }
        }

        $list = $this->link()->table($sql)->alias("t")
            ->limit($offset, $limit)
            ->order($order)
            ->select();

        return $list;
    }


    // 按日期统计渠道订单数据SQL
    public function getDateSourceOrderStatsQuery($input){
        $map = new Query();
        $map->where("s.type", 2);
        $map->where("s.visible", 0);

        // 渠道来源组
        if (!empty($input["group_id"])) {
            $map->where("s.groupid", $input["group_id"]);
        }

        // 渠道来源标识
        if (!empty($input["source"])) {
            $map->where("s.src", $input["source"]);
        }

        $dbQuery = $this->link()->name("order_source")->alias("s")->where($map);
        return $dbQuery;
    }

    // 按日期统计渠道订单发单数据
    public function getDateSourceOrderFadanStats($input){
        $dbQuery = $this->getDateSourceOrderStatsQuery($input);

        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $dbQuery->where("o.time_real", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399,
            ]);
        }

        $list = $dbQuery->group("date")
            ->join("orders o", "o.source = s.src", "inner")
            ->field([
                "FROM_UNIXTIME(o.time_real, '%Y-%m-%d') as date",
                "count(DISTINCT o.id) as fadan",
                "count(DISTINCT IF(o.`on` = 4 and o.type_fw = 1, o.id, null)) as fendan",
            ])
            ->select();

        return $list;
    }

    // 按日期统计渠道订单实际分单数据
    public function getDateSourceOrderCsosStats($input){
        $dbQuery = $this->getDateSourceOrderStatsQuery($input);

        $dbQuery->where("o.on", 4);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $dbQuery->where("csos.lasttime", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399,
            ]);
        }

        $list = $dbQuery->group("date")
            ->join("orders o", "o.source = s.src", "inner")
            ->join("order_csos_new csos", "csos.order_id = o.id", "inner")
            ->join("order_info i", "i.`order` = o.id", "left")
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "left")
            ->field([
                "FROM_UNIXTIME(csos.lasttime, '%Y-%m-%d') as date",
                "count(DISTINCT IF(o.type_fw = 1, o.id, null)) as csos_fendan",
                "count(DISTINCT IF(o.type_fw = 2, o.id, null)) as csos_zendan",
                "count(DISTINCT IF(o.type_fw = 1 and r.`status` in(1, 2, 4), o.id, null)) as csos_fen_liangfang",
                "count(DISTINCT IF(o.type_fw = 2 and r.`status` in(1, 2, 4), o.id, null)) as csos_zen_liangfang",
                "count(DISTINCT IF(o.type_fw = 1 and o.qiandan_status >= 0 and o.qiandan_companyid > 0, o.id, null)) as csos_fen_qiandan",
                "count(DISTINCT IF(o.type_fw = 2 and o.qiandan_status >= 0 and o.qiandan_companyid > 0, o.id, null)) as csos_zen_qiandan",
            ])
            ->select();

        return $list;
    }

    // 按日期统计渠道订单量房数据
    public function getDateSourceOrderLfStats($input){
        $dbQuery = $this->getDateSourceOrderStatsQuery($input);

        $dbQuery->where("o.on", 4);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $dbQuery->where("r.lf_time", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399,
            ]);
        }

        $list = $dbQuery->group("date")
            ->join("orders o", "o.source = s.src", "inner")
            ->join("order_info i", "i.`order` = o.id", "inner")
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "inner")
            ->field([
                "FROM_UNIXTIME(r.lf_time, '%Y-%m-%d') as date",
                "count(DISTINCT IF(r.`status` in(1, 2, 4), o.id, null)) as liangfang"
            ])
            ->select();

        return $list;
    }

    // 按日期统计渠道订单签单数据
    public function getDateSourceOrderQiandanStats($input){
        $dbQuery = $this->getDateSourceOrderStatsQuery($input);

        $dbQuery->where("o.on", 4);
        $dbQuery->where("o.qiandan_status", ">=", 0);
        $dbQuery->where("o.qiandan_companyid", ">", 0);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $dbQuery->where("o.qiandan_addtime", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399,
            ]);
        }

        $list = $dbQuery->group("date")
            ->join("orders o", "o.source = s.src", "inner")
            ->field([
                "FROM_UNIXTIME(o.qiandan_addtime, '%Y-%m-%d') as date",
                "count(DISTINCT o.id) as qiandan",
                "sum(o.qiandan_jine) as qiandan_amount"
            ])
            ->select();

        return $list;
    }

    // 按日期统计渠道订单补轮数据
    public function getDateSourceOrderRoundStats($input){
        $dbQuery = $this->getDateSourceOrderStatsQuery($input);

        $dbQuery->where("o.on", 4);
        $roundSql = $dbQuery->group("a.order_id")
            ->join("orders o", "o.source = s.src", "inner")
            ->join("order_info i", "i.order = o.id", "inner")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id and a.exam_status = 2", "inner")
            ->field([
                "a.order_id",
                "sum(r.round_money) as round_amount",
                "min(a.exam_time) as exam_time"
            ])
            ->buildSql();

        $map = new Query();
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $map->where("a.exam_time", "between", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399,
            ]);
        }

        $list = $this->link()->table($roundSql)->alias("a")->where($map)
            ->field([
                "FROM_UNIXTIME(a.exam_time, '%Y-%m-%d') as date",
                "count(a.order_id) as round_num",
                "sum(a.round_amount) as round_amount",
            ])
            ->group("date")
            ->select();

        return $list;
    }
}