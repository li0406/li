<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class CompanyVisit extends Model {

    public function getById($id){
        return $this->where("id", $id)->find();
    }

    public function getByOrderId($order_id)
    {
        $where = [
            ["v.order_id", '=', $order_id],
            ["r.is_delete", '=', 1],
        ];
        return $this->alias('v')
            ->field('v.*')
            ->join('qz_order_review r', 'r.order_id = v.order_id')
            ->where($where)->find();
    }

    // 分页列表查询条件
    public function getPageMap($input){
        $map = new Query();
        $map->where(function($query){
            // 不显示未回访的主动回访单
            $query->where("v.visit_status", "IN", [2,3])
                ->whereOr("v.visit_type", 1);
        });

        // 回访阶段
        if (!empty($input["visit_step"])) {
            $map->where("v.visit_step", $input["visit_step"]);
        }

        // 回访状态
        if (!empty($input["visit_status"])) {
            $map->where("v.visit_status", $input["visit_status"]);
        }

        // 回访有效状态（根据是否有回访内容判断）
        if (!empty($input["valid_status"])) {
            if ($input["valid_status"] == 1) {
                $map->where("v.visit_content", "<>", "");
            } else if ($input["valid_status"] == 2) {
                $map->where("v.visit_content", "");
            }
        }

        // 是否已推送
        if (!empty($input["visit_push"])) {
            $map->where("v.visit_push", $input["visit_push"]);
        }


        $orderMap = new Query();

        // 城市筛选
        if (!empty($input["cs"])) {
            $orderMap->where("o.cs", $input["cs"]);
        } else if (!empty($input["citys"])) {
            $orderMap->where("o.cs", "in", $input["citys"]);
        }

        // 订单号
        if (!empty($input["order_id"])) {
            $orderMap->where("o.id", $input["order_id"]);
        }

        // 订单状态
        if (!empty($input["type_fw"])) {
            $orderMap->where("o.type_fw", $input["type_fw"]);
        }

        // 订单发布时间筛选-开始时间
        if (!empty($input["date_begin"])) {
            $orderMap->where("o.time_real", "EGT", strtotime($input["date_begin"]));
        }

        // 订单发布时间筛选-结束时间
        if (!empty($input["date_end"])) {
            $orderMap->where("o.time_real", "LT", strtotime($input["date_end"]) + 86400);
        }

        return [
            "map" => $map,
            "orderMap" => $orderMap
        ];
    }

    // 查询分页总数量
    public function getPageCount($input){
        $map = $this->getPageMap($input);

        $subSql = $this->alias("a")->where($map["orderMap"])
            ->join("orders o", "o.id = a.order_id", "inner")
            ->field(["a.*"])
            ->order("a.updated_at desc")
            ->buildSql();

        $groupSql = Db::table($subSql)->alias("t")
            ->field("t.*")
            ->group("t.order_id")
            ->buildSql();

        return Db::table($groupSql)->alias("v")
            ->where($map["map"])
            ->count("v.id");
    }

    // 查询分页数据
    public function getPageList($input, $offset = 0, $limit = 0){
        $map = $this->getPageMap($input);

        $subSql = $this->alias("a")->where($map["orderMap"])
            ->join("orders o", "o.id = a.order_id", "inner")
            ->field([
                "a.id", "a.visit_type", "a.visit_step", "visit_time", "visit_status", "a.visit_push", "a.created_at",
                "a.order_id", "a.order_lf_status", "a.read_mark", "a.review_remark as remark_type", "a.visit_content",
                "o.time_real", "o.xiaoqu", "o.cs", "o.qx", "o.name as yz_name", "o.lx", "o.yusuan", "o.mianji", "o.type_fw", "o.fangshi"
            ])
            ->order("a.updated_at desc")
            ->buildSql();

        $groupSql = Db::table($subSql)->alias("t")
            ->field("t.*")
            ->group("t.order_id")
            ->buildSql();

        $subSql = Db::table($groupSql)->alias("v")->where($map["map"])
            ->field([
                "v.*",
                "if(v.visit_content <> '' and v.read_mark = 1, 1, 2) as paixu"
            ])
            ->order("paixu asc,v.id desc")
            ->limit($offset, $limit)
            ->buildSql();

        return Db::table($subSql)->alias("t")
            ->join("quyu q", "q.cid = t.cs", "left")
            ->join("area area", "area.qz_areaid = t.qx", "left")
            ->join("jiage jg", "jg.id = t.yusuan", "left")
            ->join("fangshi fs", "fs.id = t.fangshi", "left")
            ->field("t.*,q.cname as city_name,area.qz_area as area_name,jg.`name` as yusuan_name,fs.`name` as fangshi_name")
            ->order("t.paixu asc,t.id desc")
            ->select();
    }

    // 获取订单有效回访单
    public function getOrderVisitList($order_id){
        $map = new Query();
        $map->where("v.order_id", $order_id);
        $map->where("v.visit_status", "IN", [2, 3, 4, 5]);

        return $this->alias("v")->where($map)
            ->join("company_visit_related r", "r.visit_id = v.id and r.related_visit = 1", "left")
            ->join("user u", "u.id = r.company_id", "left")
            ->field("v.id,v.order_id,v.visit_time,v.visit_step,v.visit_content,group_concat(u.jc) as company_jc")
            ->group("v.id")
            ->order("visit_time desc")
            ->select();
    }

    // 获取订单回访单数量
    public function getOrderVisitCount($order_id, $visit_status = 0){
        $map = new Query();
        $map->where("order_id", $order_id);

        if ($visit_status) {
            $map->where("visit_status", $visit_status);
        }

        return $this->where($map)->count("id");
    }

    // 获取订单未回访单
    public function getOrderNoVisit($order_id){
        $map = new Query();
        $map->where("order_id", $order_id);
        $map->where("visit_status", 1);

        return $this->where($map)->find();
    }

    public function saveData($id, $data){
        return $this->where("id",$id)->update($data);
    }

    // 获取回访的装修公司
    public function getVisitCompanyRelated($visit_id, $related_visit = false, $related_push = false){
        $map = new Query();
        $map->where("r.visit_id", $visit_id);

        if ($related_visit) {
            $map->where("r.related_visit", 1);
        }

        if ($related_push) {
            $map->where("r.related_push", 1);
        }

        return Db::name("company_visit_related")
            ->alias("r")->where($map)
            ->join("user u", "u.id = r.company_id")
            ->field("r.*,u.jc as company_jc,u.qc as company_qc")
            ->select();
    }

    // 获取回访单关联数据
    public function getVisitRelated($visit_id){
        return Db::name("company_visit_related")->where("visit_id", $visit_id)->select();
    }

    // 生成回访单装修公司数据
    public function addVisitRelated($visit_id, $company_id){
        $data = [
            "visit_id" => $visit_id,
            "company_id" => $company_id,
            "related_visit" => 1,
            "created_at" => time(),
            "updated_at" => time(),
        ];

        return Db::name("company_visit_related")->data($data)->insert();
    }

    // 删除回访单装修公司数据
    public function deleteVisitRelated($visit_id){
        return Db::name("company_visit_related")->where("visit_id", $visit_id)->delete();
    }

    // 清除回访单推送装修公司数据
    public function cleanVisitPushRelated($visit_id){
        $data = [
            "related_push" => 0,
            "related_push_isread" => 0,
            "updated_at" => time()
        ];
        return Db::name("company_visit_related")->where("visit_id", $visit_id)->update($data);
    }

    // 清除回访单没有任何关联的装修公司数据
    public function cleanVisitEmptyRelated($visit_id){
        $map = new Query();
        $map->where("visit_id", $visit_id);
        $map->where("related_push", 0);
        $map->where("related_visit", 0);

        return Db::name("company_visit_related")->where($map)->delete();
    }

    // 保存回访单装修公司数据
    public function saveVisitRelated($data) {
        if (!empty($data["id"])) {
            return Db::name("company_visit_related")->where("id", $data["id"])->update($data);
        } else {
            return Db::name("company_visit_related")->insert($data);
        }
    }

    /**
     * 获取装修公司未读回访订单数量
     * @param $companyIds
     */
    public function getCompanyUnReadOrderCount($companyIds){
        $map = new Query();
        $map->where("r.company_id", "in", $companyIds);
        $map->where("r.related_push_isread", 0);
        $map->where("v.visit_push", 2);

        $subSql = Db::name("company_visit")->alias("v")->where($map)
            ->join("company_visit_related r", "r.visit_id = v.id and r.related_push = 1", "inner")
            ->join("order_info i", "i.`order` = v.order_id", "inner")
            ->field(["r.company_id", "v.order_id"])
            ->group("r.company_id,v.order_id")
            ->buildSql();

        return Db::table($subSql)->alias("t")
            ->field(["t.company_id", "count(t.order_id) as order_count"])
            ->group("t.company_id")
            ->select();
    }
}