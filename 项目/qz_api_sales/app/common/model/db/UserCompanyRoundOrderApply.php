<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class UserCompanyRoundOrderApply extends Model {

    const CAN_EXAM_RATIO = 0.5; // 允许审核的申请比例

    protected $autoWriteTimestamp = false;

    // 查询补轮申请数量
    public function getRoundApplyPageCount($input){
        $map = $this->getRoundApplyPageMap($input);

        return $this->alias("a")->where($map)
            ->join("user_company_round_order b", "b.apply_id = a.id", "inner")
            ->join("order_info i", "i.`order` = b.order_id and i.`com` = b.user_id", "inner")
            ->join("orders o", "o.id = i.`order`", "inner")
            ->join("user u", "u.id = i.`com`", "inner")
            ->count("a.id");
    }

    // 查询补轮申请数据
    public function getRoundApplyPageList($input, $offset = 0, $limit = 0){
        $map = $this->getRoundApplyPageMap($input);

        $subSql = $this->alias("a")->where($map)
            ->join("user_company_round_order b", "b.apply_id = a.id", "inner")
            ->join("order_info i", "i.`order` = b.order_id and i.`com` = b.user_id", "inner")
            ->join("orders o", "o.id = i.`order`", "inner")
            ->join("user u", "u.id = i.`com`", "inner")
            ->field([
                    "b.id as round_id", "b.apply_id", "a.order_id", "a.company_id", "a.created_at",
                    "a.exam_status", "a.exam_time", "a.exam_remark", "b.round_money",
                    "o.cs as city_id", "o.qx as area_id", "o.xiaoqu", "o.`name` as yz_name",
                    "u.jc as company_name"
                ])
            ->buildSql();

        // 查询订单分配次数和申请补轮次数
        $subSql2 = $this->table($subSql)->alias("t")
            ->join("user_company_round_order r", "r.order_id = t.order_id", "inner")
            ->join("order_info ii", "ii.`order` = t.order_id and ii.`com` = t.company_id and ii.type_fw = 1", "inner")
            ->field(["t.*", "count(r.id) as fen_num", "count(if(r.apply_id = 0, null, 1)) as apply_num"])
            ->group("t.round_id")
            ->buildSql();

        // 排序、其它信息查询
        return $this->table($subSql2)->alias("t2")
            ->join("quyu q", "q.cid = t2.city_id", "left")
            ->join("area area", "area.qz_areaid = t2.area_id", "left")
            ->field([
                    "t2.*", "if(t2.apply_num / t2.fen_num >= " . static::CAN_EXAM_RATIO . ", 1, 2) as can_exam",
                    "q.cname as city_name", "area.qz_area as area_name"
                ])
            ->order("t2.exam_status asc,can_exam asc,t2.created_at desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 补轮申请查询条件
    public function getRoundApplyPageMap($input){
        $map = new Query();

        // 城市
        if (!empty($input["city_id"])) {
            $map->where("o.cs", $input["city_id"]);
        } else if (!empty($input["citys"])) {
            $map->where("o.cs", "in", $input["citys"]);
        }

        // 审核状态
        if (!empty($input["exam_status"])) {
            $map->where("a.exam_status", $input["exam_status"]);
        }

        // 申请时间
        if (!empty($input["apply_begin"]) && !empty($input["apply_end"])) {
            $map->where("a.created_at", "between", [
                strtotime($input["apply_begin"]),
                strtotime($input["apply_end"]) + 86399
            ]);
        }

        // 搜索
        if (!empty($input["search_type"]) && !empty($input["search_keyword"])) {
            $keyword = $input["search_keyword"];
            switch ($input["search_type"]) {
                case 1:
                    $map->where(function($query) use ($keyword) {
                        $query->where("o.id", $keyword);
                        $query->whereOr("o.xiaoqu", "like", "%" . $keyword . "%");
                    });
                    break;
                case 2:
                    $map->where(function($query) use ($keyword) {
                        $query->where("u.id", $keyword);
                        $query->whereOr("u.jc", "like", "%" . $keyword . "%");
                        $query->whereOr("u.qc", "like", "%" . $keyword . "%");
                    });
                    break;
            }
        }

        return $map;
    }

}