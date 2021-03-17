<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyRoundOrderApplyModel extends Model{
    
    const LOG_ACT_TYPE_EXAM_GJ = 1; // 继续跟进
    const LOG_ACT_TYPE_EXAM_PASS = 2; // 审核通过
    const LOG_ACT_TYPE_EXAM_FAIL = 3; // 审核不通过

    const CAN_EXAM_RATIO = 0.6666; // 允许审核的申请比例

    // 签返补轮审核列表查询条件
    public function getRoundOrderApplyMap($input){
        $having = "";

        $map = array(
            "i.type_fw" => array("EQ", 1),
            "o.`on`" => array("EQ", 4)
        );

        // 审核状态
        if (!empty($input["exam_status"])) {
            $map["a.exam_status"] = array("EQ", $input["exam_status"]);

            // 如果是预受理则限制只显示可受理的申请
            if ($input["exam_status"] == 1) {
                $having = "can_exam = 1";
            }
        }

        // 订单号、小区名
        if (!empty($input["order"])) {
            $map["_complex"][] = array(
                "o.id" => array("EQ", $input["order"]),
                "o.xiaoqu" => array("LIKE", "%" . $input["order"] . "%"),
                "_logic" => "or"
            );
        }
        
        // 装修公司
        if (!empty($input["company"])) {
            $map["_complex"][] = array(
                "u.id" => array("EQ", $input["company"]),
                "u.qc" => array("LIKE", "%" . $input["company"] . "%"),
                "_logic" => "or"
            );
        }

        // 所属城市
        if (!empty($input["city"])) {
            $map["o.cs"] = array("EQ", $input["city"]);
        } else if (!empty($input["citys"])) {
            $map["o.cs"] = array("IN", $input["citys"]);
        }

        // 日期查询
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $map["a.created_at"] = array("BETWEEN", [
                strtotime($input["begin"]),
                strtotime($input["end"]) + 86399
            ]);
        } else if (!empty($input["begin"])) {
            $map["a.created_at"] = array("EGT", strtotime($input["begin"]));
        } else if (!empty($input["end"])) {
            $map["a.created_at"] = array("ELT", strtotime($input["end"]) + 86399);
        }

        //审核日期查询
        if (isset($input['check_begin']) && !empty($input['check_begin']) && isset($input['check_end']) && !empty($input['check_end'])) {
            $map["a.exam_time"] = array("BETWEEN", [
                strtotime($input["check_begin"]),
                strtotime($input["check_end"]) + 86399
            ]);
        } else if (isset($input['check_begin']) && !empty($input['check_begin'])) {
            $map["a.exam_time"] = array("EGT", strtotime($input["check_begin"]));
        } else if (isset($input['check_end']) && !empty($input['check_end'])) {
            $map["a.exam_time"] = array("ELT", strtotime($input["check_end"]) + 86399);
        }

        // 所属城市
        if (!empty($input["people"])) {
            $map["ot.review_uid"] = array("EQ", $input["people"]);
        }

        return [
            "map" => $map,
            "having" => $having
        ];
    }

    // 获取基础查询SQL
    public function getRoundOrderApplySql($input){
        $map = $this->getRoundOrderApplyMap($input);

        $buildSql1 = M("order_review_new_history")->alias("h")
            ->join("left join qz_order_review_new_remark as nr on nr.id = h.remark_type")
            ->field(["h.id", "h.order_id", "h.review_uid", "h.next_visit_time", "h.review_type", "nr.`name` as remark_type_name"])
            ->order("h.id desc")
            ->buildSql();

        $buildSql2 = M()->table($buildSql1)->alias("tt")
            ->join("left join qz_adminuser as uu on tt.review_uid=uu.id")
            ->field([
                    "tt.*", "uu.`user` username",
                    "CASE
                        WHEN tt.next_visit_time = DATE_FORMAT(now(), '%Y-%m-%d')
                        THEN 3
                        WHEN tt.next_visit_time IS NULL OR tt.next_visit_time = '' 
                        THEN 1
                        ELSE 2
                        END AS nxt"
                ])
            ->group("tt.order_id")
            ->buildSql();

        // 基础查询
        $subSql = $this->alias("a")->where($map["map"])
            ->join("inner join qz_user_company_round_order as b on b.apply_id = a.id")
            ->join("inner join qz_order_info as i on i.`order` = b.order_id and i.`com` = b.user_id")
            ->join("inner join qz_orders as o on o.id = i.`order`")
            ->join("inner join qz_user as u on u.id = a.company_id")
            ->join("left join $buildSql2 as ot on a.order_id = ot.order_id")
            ->field([
                    "b.id as round_id", "b.apply_id", "a.order_id", "a.company_id", "a.created_at",
                    "a.exam_status", "a.exam_time", "a.exam_remark", "a.exam_operator", "b.round_money",
                    "o.cs as city_id", "o.qx as area_id", "o.xiaoqu", "o.`name` as yz_name",
                    "i.addtime as allot_time", "u.jc as company_jc", "u.qc as company_qc",
                    "ot.remark_type_name", "ot.review_type", "ot.username", "ot.nxt",
                    "IF(ot.next_visit_time != 0 and ot.next_visit_time != '' and ot.next_visit_time IS NOT NULL, FROM_UNIXTIME(ot.next_visit_time, '%Y-%m-%d'), '') next_visit_time"
                ])
            ->buildSql();


        // 查询订单分配次数和申请补轮次数
        $subSql2 = M()->table($subSql)->alias("t")
            ->join("inner join qz_user_company_round_order as r on r.order_id = t.order_id")
            ->join("inner join qz_order_info as ii on ii.`order` = t.order_id and ii.`com` = t.company_id and ii.type_fw = 1")
            ->field(["t.*", "count(r.id) as fen_num", "count(if(r.apply_id = 0, null, 1)) as apply_num"])
            ->group("t.round_id")
            ->buildSql();


        // 排序、其它信息查询
        $order = 't2.nxt desc,exam_status2 asc,can_exam asc,t2.created_at asc';

        if ($input['nxt_order'] == 2) {
            $order = 't2.next_visit_time desc';
        } else if ($input['nxt_order'] == 1) {
            $order = 't2.next_visit_time asc';
        }

        if ($input['blt_order'] == 2) {
            $order = 't2.created_at desc';
        } else if ($input['blt_order'] == 1) {
            $order = 't2.created_at asc';
        }

        if ($input['exam_order'] == 2) {
            $order = 't2.exam_time desc';
        } else if ($input['exam_order'] == 1) {
            $order = 't2.exam_time asc';
        }


        if ($input['distribute_order'] == 2) {
            $order = 't2.allot_time desc';
        } else if ($input['distribute_order'] == 1) {
            $order = 't2.allot_time asc';
        }

        // 最终查询SQL
        $exam_ratio = static::CAN_EXAM_RATIO;
        $basqSql = M()->table($subSql2)->alias("t2")
            ->join("left join qz_quyu as q on q.cid = t2.city_id")
            ->join("left join qz_area as area on area.qz_areaid = t2.area_id")
            ->join("left join qz_adminuser as au on au.id = t2.exam_operator")
            ->field([
                    "t2.*", "if(t2.apply_num / t2.fen_num >= {$exam_ratio}, 1, 2) as can_exam",
                    "q.cname as city_name", "area.qz_area as area_name",
                    "au.`user` as exam_operator_user","CASE t2.exam_status WHEN 1 THEN 0 WHEN 11 THEN 1 ELSE t2.exam_status END AS exam_status2"
                ])
            ->order($order)
            ->having($map["having"])
            ->buildSql();

        return $basqSql;
    }

    // 签返补轮审核列表数量
    public function getRoundOrderApplyCount($input){
        $basqSql = $this->getRoundOrderApplySql($input);

        return M()->table($basqSql)->alias("t3")
            ->count("t3.round_id");
    }

    // 签返补轮审核列表数据
    public function getRoundOrderApplyList($input, $offset = 0, $limit = 0){
        $basqSql = $this->getRoundOrderApplySql($input);

        return M()->table($basqSql)->alias("t3")
            ->limit($offset, $limit)
            ->select();
    }

    // 签返补轮审核列表数量统计
    public function getRoundOrderApplyTotal(){
        $map = array(
            "a.exam_status" => array("IN", [1, 11])
        );

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_user_company_round_order as b on b.apply_id = a.id")
            ->join("inner join qz_order_info as i on i.`order` = b.order_id and i.`com` = b.user_id")
            ->join("inner join qz_orders as o on o.id = i.`order`")
            ->field(["a.id", "a.order_id",  "a.company_id", "a.exam_status", "b.id as round_id"])
            ->buildSql();

        $subSql2 = M()->table($subSql)->alias("t")
            ->join("inner join qz_user_company_round_order as r on r.order_id = t.order_id")
            ->join("inner join qz_order_info as ii on ii.`order` = t.order_id and ii.`com` = t.company_id and ii.type_fw = 1")
            ->field(["t.*", "count(r.id) as fen_num", "count(if(r.apply_id = 0, null, 1)) as apply_num"])
            ->group("t.round_id")
            ->buildSql();

        $exam_ratio = static::CAN_EXAM_RATIO;
        $subSql3 = M()->table($subSql2)->alias("t2")
            ->field([
                "t2.*",
                "if(t2.apply_num / t2.fen_num >= {$exam_ratio}, 1, 2) as can_exam"
            ])
            ->buildSql();

        return M()->table($subSql3)->alias("t3")
            ->field([
                "count(DISTINCT IF(t3.exam_status = 1 and t3.can_exam = 1, t3.order_id, null)) as apply_prepare_num",
                "count(DISTINCT IF(t3.exam_status = 11, t3.order_id, null)) as apply_check_num",
            ])
            ->find();
    }

    // 签返补轮审核列表数量统计
    public function getRoundOrderApplyExamTotal($begin, $end){
        $map = array(
            "a.exam_status" => array("IN", [2, 3]),
            "a.exam_time" => array("BETWEEN", [
                strtotime($begin),
                strtotime($end) + 86399
            ])
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_user_company_round_order as b on b.apply_id = a.id")
            ->join("inner join qz_order_info as i on i.`order` = b.order_id and i.`com` = b.user_id")
            ->join("inner join qz_orders as o on o.id = i.`order`")
            ->field([
                "count(DISTINCT IF(a.exam_status = 2, a.order_id, null)) as apply_pass_num",
                "count(DISTINCT IF(a.exam_status = 3, a.order_id, null)) as apply_unpass_num",
            ])
            ->find();
    }

    // 签返补轮审核详情
    public function getRoundOrderApplyDetail($round_id){
        $map = array(
            "b.id" => array("EQ", $round_id),
            "i.type_fw" => array("EQ", 1),
            "o.on" => array("EQ", 4),
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_user_company_round_order as b on b.apply_id = a.id")
            ->join("inner join qz_order_info as i on i.`order` = a.order_id and i.`com` = a.company_id")
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->join("inner join qz_user as u on u.id = a.company_id")
            ->join("left join qz_quyu as q on q.cid = o.cs")
            ->join("left join qz_area as area on area.qz_areaid = o.qx")
            ->join("left join qz_huxing as hx on hx.id = o.huxing")
            ->join("left join qz_fengge as fg on fg.id = o.fengge")
            ->join("left join qz_jiage as jg on jg.id = o.yusuan")
            ->field([
                    "b.id as round_id","b.apply_id", "b.round_money",
                    "a.order_id", "a.company_id", "a.created_at", "a.apply_reason", "a.apply_remark",
                    "a.exam_status", "a.exam_time", "a.exam_remark", "a.exam_operator", "a.track_info",
                    "o.cs as city_id", "o.qx as area_id", "o.xiaoqu", "o.`name`", "o.sex", "o.time_real", "o.other_contact",
                    "o.lng", "o.lat", "o.huxing", "o.mianji", "o.lx", "o.lxs",
                    "i.addtime as allot_time", "i.opname","u.jc as company_jc", "u.qc as company_qc", "o.tel",
                    "q.cname as city_name", "area.qz_area as area_name", "hx.`name` as huxing_name", "fg.`name` as fengge_name",
                    "jg.`name` as yusuan_name"
                ])
            ->find();
    }

    public function getRoundOrderApplyInfo($id){
        $map = array(
            "a.id" => array("EQ", $id)
        );

        return $this->alias("a")->where($map)
            ->find();
    }

    // 订单装修公司申请补轮次数
    public function getOrderCompanyApplyNumber($order_id){
        $map = array(
            "o.`on`" => array("EQ", 4),
        );

        if (is_array($order_id)) {
            $map["o.id"] = array("IN", $order_id);
        } else {
            $map["o.id"] = array("EQ", $order_id);
        }

        $subSql = M("orders")->alias("o")->where($map)
            ->join("inner join qz_order_info as i on i.`order` = o.id")
            ->join("left join qz_order_company_review as r on r.orderid = i.`order` and r.comid = i.`com`")
            ->join("left join qz_user_company_round_order_apply as a on a.order_id = i.`order` and a.company_id = i.`com`")
            ->field(["i.id", "i.`order`", "i.`com`", "count(a.id) as apply_count"])
            ->group("i.`order`,i.`com`")
            ->buildSql();

        return M()->table($subSql)->alias("t")->field([
                "t.`order` as order_id",
                "count(t.id) as fen_count",
                "count(IF(t.apply_count = 0, null, 1)) as apply_count",
                "sum(t.apply_count) as apply_sum"
            ])
            ->group("t.`order`")
            ->select();
    }

    // 保存数据
    public function updateData($id, $data) {
        $map = array(
            "id" => array("EQ", $id),
        );

        return $this->where($map)->save($data);
    }

    // 保存数据
    public function updateAll($ids, $data) {
        $map = array(
            "id" => array("IN", $ids),
        );

        return $this->where($map)->save($data);
    }

    public function addLog($data){
        return M("user_company_round_order_apply_log")->add($data);
    }

    public function addLogAll($data){
        return M("user_company_round_order_apply_log")->addAll($data);
    }

    // 获取订单分配装修公司补轮状态
    public function getOrderCompanyRoundApplyLog($order_id){
        $map = array(
            "i.`order`" => array("EQ", $order_id)
        );

        return M("order_info")->alias("i")->where($map)
            ->join("left join qz_order_company_review as r on r.orderid = i.`order` and r.comid = i.`com`")
            ->join("left join qz_user_company_round_order as b on b.order_id = i.`order` and b.user_id = i.`com`")
            ->join("left join qz_user_company_round_order_apply as a on a.id = b.apply_id")
            ->join("left join qz_user as u on u.id = i.`com`")
            ->field([
                    "i.`order`", "i.`com`", "i.type_fw", "r.status", "u.jc as company_jc", "u.qc as company_qc",
                    "b.id as round_id", "b.apply_id", "a.exam_status", "a.company_id", "a.apply_reason", "a.apply_remark", "a.created_at"
                ])
            ->select();
    }

    public function roundApplyCheckList($orderid, $company_id)
    {
        $map = [
            'a.order_id' => ['EQ', $orderid],
            'a.company_id' => ['EQ', $company_id],
            'a.exam_status' => ['IN', '2,3']
        ];
        $list = $this->alias('a')->join('LEFT JOIN qz_adminuser b ON a.exam_operator=b.id')->where($map)->field('a.id,a.company_id,a.order_id,a.exam_status,a.exam_time,a.exam_remark,b.user username')->select();

        return $list;
    }

    public function roundApplyTrackList($orderid)
    {
        $map = [
            'a.order_id' => ['EQ', $orderid]
        ];

        $list = M('user_company_round_order_track')->alias('a')->join('LEFT JOIN qz_adminuser u ON a.track_operator=u.id')->where($map)->field('a.*,u.user username')->order('track_time desc')->select();

        return $list;
    }

    public function addTrackData($data)
    {
        return M('user_company_round_order_track')->add($data);
    }
}