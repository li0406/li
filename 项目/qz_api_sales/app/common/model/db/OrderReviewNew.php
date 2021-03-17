<?php
// +----------------------------------------------------------------------
// | OrderReviewNew 新客服回访订单
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class OrderReviewNew extends Model
{
    public function getInfoByOrder($order_id, $field = '*')
    {
        $where = [
            ['order_id', '=', $order_id]
        ];
        return $this->field($field)->where($where)->find();
    }

    public function getVisitInfo($id, $order_id){
        $map = new Query();
        $map->where("a.id", $id);
        $map->where("a.order_id", $order_id);
        $map->where("a.is_delete", 1);

        return $this->alias("a")->where($map)
            ->join("order_review_new_remark r", "r.id = a.remark_type", "left")
            ->field([
                "a.next_visit_step visit_step","a.order_id","a.next_visit_time visit_time","a.review_type visit_status",
                "a.visit_push", "a.created_at","a.is_delete","a.visit_content_sales","a.review_feedback","a.review_feedback visit_content",
                "a.updated_at","a.push_company", "a.review_name visit_username", "a.read_mark", "a.remark_type", "r.`name` as remark_type_name"
            ])->find();
    }

    public function editReviewInfo($order_id, $save)
    {
        return $this->where('order_id', $order_id)->update($save);
    }

    public function addReviewInfo($save)
    {
        return $this->insertGetId($save);
    }

    public function hasOrders($ordersId)
    {
        $cond[] = ['order_id', 'in', $ordersId];
        $cond[] = ['is_delete', '=', 1];
        $data = $this->alias('a')
            ->field("count(*) count,order_id")
            ->where($cond)
            ->group('order_id')
            ->select();
        return $data->toArray() ?: [];
    }


    public function getById($id)
    {
        return $this->where("id", $id)->find();
    }

    // 分页列表查询条件
    public function getPageMap($input)
    {
        $map = new Query();

        //查询未删除订单
        $map->where("v.is_delete", 1);
        if(!empty($input['review_type'])){
            $map->where(function ($query) use ($input) {
                // 不显示未回访的主动回访单
                $query->where("v.review_type", "IN", $input['review_type']);
            });

        }

        // 城市筛选
        if (!empty($input["cs"])) {
            $map->where("o.cs", $input["cs"]);
        } else if (!empty($input["citys"])) {
            $map->where("o.cs", "in", $input["citys"]);
        }

        // 订单号
        if (!empty($input["order_id"])) {
            $map->where("o.id", $input["order_id"]);
        }

        // 订单状态
        if (!empty($input["type_fw"])) {
            $map->where("o.type_fw", $input["type_fw"]);
        }

        // 回访阶段
        if (!empty($input["visit_step"])) {
            $map->where("v.next_visit_step", $input["visit_step"]);
        }


        // 是否已推送
        if (!empty($input["visit_push"])) {
            $map->where("v.visit_push", $input["visit_push"]);
        }

        // 订单发布时间筛选-开始时间
        if (!empty($input["date_begin"])) {
            $map->where("o.time_real", "EGT", strtotime($input["date_begin"]));
        }

        // 订单发布时间筛选-结束时间
        if (!empty($input["date_end"])) {
            $map->where("o.time_real", "LT", strtotime($input["date_end"]) + 86400);
        }

        // 回访有效状态（根据是否有回访内容判断）
        if (!empty($input["valid_status"])) {
            if ($input["valid_status"] == 1) {
                $map->where("v.review_feedback", "<>", "");
            } else if ($input["valid_status"] == 2) {
                $map->where("v.review_feedback", "");
            }
        }

        return $map;
    }

    // 查询分页总数量
    public function getPageCount($input)
    {
        $map = $this->getPageMap($input);

        return $this->alias("v")->where($map)
            ->join("orders o", "o.id = v.order_id", "inner")
            ->count("v.id");
    }

    // 查询分页数据
    public function getPageList($input, $offset = 0, $limit = 0)
    {
        $map = $this->getPageMap($input);

        $subSql = $this->alias("v")->where($map)
            ->join("orders o", "o.id = v.order_id", "inner")
            ->field([
                "v.id","v.next_visit_step visit_step","v.order_id","v.updated_at visit_time",
                "v.review_type visit_status", "v.visit_push", "v.created_at", "v.read_mark", "v.remark_type", "v.review_feedback visit_content",
                "o.time_real", "o.xiaoqu", "o.cs", "o.qx", "o.name as yz_name", "o.lx", "o.yusuan", "o.mianji", "o.type_fw", "o.fangshi",
                "if(v.review_feedback <> '' and v.read_mark = 1, 1, 2) as paixu"
            ])
            ->order("paixu asc,v.id desc")
            ->limit($offset, $limit)
            ->buildSql();

        return Db::table($subSql)->alias("t")
            ->join("quyu q", "q.cid = t.cs", "left")
            ->join("area area", "area.qz_areaid = t.qx", "left")
            ->join("jiage jg", "jg.id = t.yusuan", "left")
            ->join("fangshi fs", "fs.id = t.fangshi", "left")
            ->join("order_review_new_remark r", "r.id = t.remark_type", "left")
            ->field("t.*,q.cname as city_name,area.qz_area as area_name,jg.`name` as yusuan_name,fs.`name` as fangshi_name,r.`name` as remark_type_name")
            ->order("t.paixu asc,t.id desc")
            ->select();
    }
}