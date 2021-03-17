<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class SaleReportPayment extends Model {

    protected $autoWriteTimestamp = false;

    public function getById($id, $field = "*"){
        return $this->where("id", $id)->field($field)->find();
    }

    public function getInfo($id){
        $map = new Query();
        $map->where("a.id", $id);

        return $this->alias("a")->where($map)
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->field([
                    "a.id", "a.cooperation_type", "a.creator", "a.exam_status", "a.is_delete", "a.is_related",
                    "a.company_id", "a.company_name", "a.created_at", "a.creator", "a.need_kf_exam",
                    "a.deposit_money", "a.round_order_money", "a.deposit_to_platform_money",
                    "a.city_id", "a.deposit_to_round_money", "q.cname as city_name"
                ])
            ->find();
    }

    public function getPaymentDetail($id){
        $map = new Query();
        $map->where("a.id", $id);
        $map->where("a.is_delete", 1);

        return $this->alias("a")->where($map)
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->join("adminuser u", "u.id = a.creator", "left")
            ->join("sale_report_related r", "r.report_payment_id = a.id", "left")
            ->join("sale_report_zx_company b", "b.id = r.report_id and b.cooperation_type = r.report_cooperation_type", "left")
            ->join("sale_report_swj_company c", "c.id = r.report_id and c.cooperation_type = r.report_cooperation_type", "left")
            ->field([
                    "a.*", "q.cname as city_name", "u.`user` as creator_name",
                    "if(r.report_id is null, 0, r.report_id) as report_id",
                    "if(r.report_cooperation_type is null, 0, r.report_cooperation_type) as report_cooperation_type",
                    "if(b.id is null, c.`status`, b.`status`) as report_status",
                ])
            ->find();
    }

    // 分页查询条件
    public function getPageMap($input){
        $map = new Query();
        $map->where("a.is_delete", 1);

        // 城市选择
        if (!empty($input["city_id"])) {
            $map->where("a.city_id", $input["city_id"]);
        } else if (!empty($input["citys"])) {
            $map->where("a.city_id", "in", $input["citys"]);
        }

        // 销售权限
        if (!empty($input["saler_ids"])) {
            $map->where("a.creator", "in", $input["saler_ids"]);
        }

        if (!empty($input["limit_refund"])){
            $map->where("a.cooperation_type", '<>', $input['limit_refund']);
        }

        // 装修公司名称查询
        if (!empty($input["company_name"])) {
            $map->where("a.company_name", "like", "%" .$input["company_name"]. "%");
        }

        // 汇款方查询
        if (!empty($input["payment_uname"])) {
            $map->where("a.payment_uname", "like", "%" .$input["payment_uname"]. "%");
        }

        // 合作类型
        if (!empty($input["cooperation_type"])) {
            $map->where("a.cooperation_type", $input["cooperation_type"]);
        }

        // 审核状态
        if(!empty($input['fix_status'])){
            $map->where('a.exam_status', 'IN', $input['fix_status']);
        }else{
            if (!empty($input["exam_status"])) {
                if ($input["exam_status"] == 3) {
                    $map->where("a.exam_status", $input["exam_status"]);
                    $map->where("a.need_kf_exam", 2);
                } else if ($input["exam_status"] == 30) {
                    $map->where("a.exam_status", 3);
                    $map->where("a.need_kf_exam", 1);
                } else {
                    $map->where("a.exam_status", $input["exam_status"]);
                }
            }
        }

        if(!empty($input['creator_name'])){
            $map->where('u.user', '=', $input['creator_name']);
        }

        // 是否报备会员
        if (!empty($input["is_related"])) {
            $map->where("a.is_related", $input["is_related"]);
        }

        // 关键词 (919修改) -- 943 重新改回
        if (!empty($input["keyword"])) {
            // $map->where("a.company_name", "like", "%" .$input["keyword"]. "%");
            $map->where(function ($query) use ($input) {
               $query->where("a.company_name", 'like', '%' . $input["keyword"] . '%');
               $query->whereOr("a.payment_uname", 'like', '%' .$input["keyword"] . '%');
            });
        }

        return $map;
    }

    // 查询分页总条数
    public function getPageCount($input){
        $map = $this->getPageMap($input);

        return $this->alias("a")->join("adminuser u", "u.id = a.creator", "left")->where($map)->count("a.id");
    }

    // 查询分页数据
    public function getPageList($input, $offset = 0, $limit = 0){
        $map = $this->getPageMap($input);

        return $this->alias("a")->where($map)
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->join("adminuser u", "u.id = a.creator", "left")
            ->join("user_company_account aa", "aa.user_id = a.company_id", "left")
            ->field(["a.*", "q.cname as city_name", "u.`user` as creator_name", "aa.deposit_money as time_deposit_money"])
            ->order("a.updated_at desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 删除（逻辑删除）
    public function deleteInfo($id){
        $data = [
            "is_delete" => 2,
            "updated_at" => time()
        ];

        return $this->where("id", $id)->update($data);
    }

    // 数据更新
    public function updateInfo($id, $data){
        return $this->where("id", $id)->update($data);
    }

    // 数据更新
    public function updateAllInfo($ids, $data){
        return $this->where("id", "in", $ids)->update($data);
    }

    // 新增
    public function addInfo($data){
        return $this->insertGetId($data);
    }

    // 小报备审核分页查询条件
    public function getExamPageMap($input){
        $map = new Query();
        $map->where("a.is_delete", 1);

        // 城市选择
        if (!empty($input["city_id"])) {
            $map->where("a.city_id", $input["city_id"]);
        } else if (!empty($input["citys"])) {
            $map->where("a.city_id", "in", $input["citys"]);
        }

        // 审核状态
        if (!empty($input["exam_status"])) {
            if ($input["exam_status"] == 3) {
                $map->where("a.exam_status", $input["exam_status"]);
                $map->where("a.need_kf_exam", 2);
            } else if ($input["exam_status"] == 30) {
                $map->where("a.exam_status", 3);
                $map->where("a.need_kf_exam", 1);
            } else {
                $map->where("a.exam_status", $input["exam_status"]);
            }
        } else {
            $map->where("a.exam_status", "neq", 1);
        }

        // 合作类型
        if (!empty($input["cooperation_type"])) {
            $map->where("a.cooperation_type", $input["cooperation_type"]);
        }

        // 关键词（919修改）
        if (!empty($input["keyword"])) {
            $map->where("a.company_name", 'like', '%' . $input["keyword"] . '%');
            // $map->where(function($query) use ($input) {
            //    $query->where("a.company_name", $input["keyword"]);
            //    $query->whereOr("a.payment_uname", $input["keyword"]);
            // });
        }

        // 公司名称
        if (!empty($input["company_name"])) {
            $map->where("a.company_name", "like", "%" .$input["company_name"]. "%");
        }

        // 汇款方
        if (!empty($input["payment_uname"])) {
            $map->where("a.payment_uname", "like", "%" .$input["payment_uname"]. "%");
        }

        return $map;
    }

    // 小报备审核查询分页总条数
    public function getExamPageCount($input){
        $map = $this->getExamPageMap($input);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 小报备审核查询分页数据
    public function getExamPageList($input, $offset = 0, $limit = 0){
        $map = $this->getExamPageMap($input);

        return $this->alias("a")->where($map)
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->join("adminuser u", "u.id = a.creator", "left")
            ->join("sale_report_related r", "r.report_payment_id = a.id", "left")
            ->join("sale_report_zx_company b", "b.id = r.report_id and b.cooperation_type = r.report_cooperation_type", "left")
            ->join("sale_report_swj_company c", "c.id = r.report_id and c.cooperation_type = r.report_cooperation_type", "left")
            ->field([
                    "a.*", "q.cname as city_name", "u.`user` as creator_name",
                    "r.report_id", "r.report_cooperation_type",
                    "if(b.id is null, c.`status`, b.`status`) as report_status",
                    "IF(a.exam_status = 2, 1, 2) as paixu",
                ])
            ->order("paixu asc, a.submit_time desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 小报备客服审核分页查询条件
    public function getKfExamPageMap($input){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.need_kf_exam", 1);
        $map->where("a.exam_status", "in", [3, 5, 6]);

        // 合作类型
        if (!empty($input["cooperation_type"])) {
            $map->where("a.cooperation_type", $input["cooperation_type"]);
        }

        // 审核状态
        if (!empty($input["exam_status"])) {
            $map->where("a.exam_status", $input["exam_status"]);
        }

        // 城市选择
        if (!empty($input["city_id"])) {
            $map->where("a.city_id", $input["city_id"]);
        }

        // 公司名称/ID
        if (!empty($input["company"])) {
            $map->where(function($query) use ($input) {
               $query->where("a.company_name", "like", "%". $input["company"] . "%");
               $query->whereOr("a.company_id", $input["company"]);
            });
        }

        // 报备人
        if (!empty($input["saler"])) {
            $map->where("u.user", $input["saler"]);
        }

        return $map;
    }

    // 小报备客服审核查询分页总条数
    public function getKfExamPageCount($input){
        $map = $this->getKfExamPageMap($input);

        return $this->alias("a")->where($map)
            ->join("adminuser u", "u.id = a.creator", "left")
            ->count("a.id");
    }

    // 小报备客服审核查询分页数据
    public function getKfExamPageList($input, $offset = 0, $limit = 0){
        $map = $this->getKfExamPageMap($input);

        return $this->alias("a")->where($map)
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->join("adminuser u", "u.id = a.creator", "left")
            ->field([
                    "a.*", "q.cname as city_name", "u.`user` as creator_name",
                    "IF(a.exam_status = 5, 1, 2) as paixu",
                ])
            ->order("paixu asc, a.exam_time desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 大报备关联小报备列表
    public function getRelatedReportList($report_id, $report_cooperation_type){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("r.report_id", $report_id);
        $map->where("r.report_cooperation_type", $report_cooperation_type);

        return $this->alias("a")->where($map)
            ->join("sale_report_related r", "r.report_payment_id = a.id", "inner")
            ->join("adminuser u", "u.id = a.creator", "left")
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->field(["a.*", "q.cname as city_name", "u.`user` as creator_name"])
            ->order("a.updated_at desc")
            ->select();
    }

    // 订单返点小报备列表
    public function getOrderBackReportList($order_id){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("a.cooperation_type", 8);
        $map->where("a.order_id", $order_id);

        return $this->alias("a")->where($map)
            ->join("adminuser u", "u.id = a.creator", "left")
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->field([
                    "a.id", "company_id", "a.company_name", "a.cooperation_type", "a.payment_time", "a.order_id",
                    "a.payment_total_money", "a.deposit_money", "a.round_order_money",
                    "q.cname as city_name", "u.`user` as creator_name"
                ])
            ->order("a.updated_at desc")
            ->select();
    }

    // 订单返点小报备数量
    public function getOrderBackReportCount($order_id){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("a.cooperation_type", 8);

        if (is_array($order_id)) {
            $map->where("a.order_id", "in", $order_id);
        } else {
            $map->where("a.order_id", $order_id);
        }

        return $this->alias("a")->where($map)
            ->field(["a.order_id", "count(a.id) as count"])
            ->group("a.order_id")
            ->select();
    }


    // 获取小报备业绩明细查询条件
    private function getStatisticDetailedMap($input){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);

        // 城市查询
        if (!empty($input["city_id"])) {
            $map->where("a.city_id", $input["city_id"]);
        }

        // 汇款时间查询
        if (!empty($input["payment_date_begin"]) && !empty($input["payment_date_end"])) {
            $map->where("a.payment_time", "between", [
                    strtotime($input["payment_date_begin"]),
                    strtotime($input["payment_date_end"]) + 86399
                ]);
        }

        // 收款类型查询
        if (!empty($input["payment_type"])) {
            $map->where("a.payment_type", $input["payment_type"]);
        }

        // 汇款金额最小值
        if (!empty($input["payment_money_min"])) {
            $map->where("a.payment_money", ">=", floatval($input["payment_money_min"]) * 10000);
        }

        // 汇款金额最大值
        if (!empty($input["payment_money_max"])) {
            $map->where("a.payment_money", "<=", floatval($input["payment_money_max"]) * 10000);
        }

        // 业绩人
        if (!empty($input["saler_id"]) || !empty($input["user_ids"])) {
            $map->where("a.id", "in", function($query) use ($input) {
                $m = new Query();
                $m->where("share_ratio", ">", 0);

                if (!empty($input["saler_id"])) {
                    $m->where("saler_id", intval($input["saler_id"]));
                }

                if (!empty($input["user_ids"])) {
                    $m->where("saler_id", "in", $input["user_ids"]);
                }

                $query->name("sale_report_payment_person")->where($m)
                    ->field("report_payment_id")
                    ->distinct(true)
                    ->select();
            });
        }

        // 收款方
        if (!empty($input["payee_type"])) {
            $map->where("a.id", "in", function($query) use ($input) {
                $query->name("sale_report_payment_payee")
                    ->where("payee_type", intval($input["payee_type"]))
                    ->field("report_payment_id")
                    ->distinct(true)
                    ->select();
            });
        }

        return $map;
    }

    // 获取小报备业绩明细总数量
    public function getStatisticDetailedCount($input){
        $map = $this->getStatisticDetailedMap($input);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 获取小报备业绩明细分页数据
    public function getStatisticDetailedList($input, $offset = 0, $limit = 0){
        $map = $this->getStatisticDetailedMap($input);

        return $this->alias("a")->where($map)
            ->join("quyu q", "q.cid = a.city_id", "left")
            ->join("adminuser u", "u.id = a.creator", "left")
            ->field([
                    "a.id", "a.city_id", "a.company_name", "a.cooperation_type", "a.creator", "a.round_order_money", "a.deposit_money",
                    "a.payment_total_money", "a.payment_money", "a.payment_uname", "a.payment_time", "a.payment_type", "a.other_money",
                    "a.deposit_to_round_money","a.refund_money", "a.platform_money", "a.deposit_to_platform_money",
                    "q.cname as city_name", "u.`user` as creator_name",
                ])
            ->order("a.payment_time desc,a.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    public function getSalesPaymentMoney($user_ids,$begin,$end)
    {
        if (count($user_ids) > 0) {
            $map[] = ["b.saler_id", "IN", $user_ids];
        }

        $map[] = ["a.exam_status", "EQ", 3];
        $map[] = ["a.payment_time", "EGT", $begin];
        $map[] = ["a.payment_time", "ELT", $end];

        return $this->where($map)->alias("a")
            ->join("sale_report_payment_person b", "a.id = b.report_payment_id")
            ->field(["
                    b.saler_id", "b.share_ratio", "a.id as payment_id", "a.payment_type", "a.payment_account_type",
                    "a.payment_money,FROM_UNIXTIME(payment_time,'%Y%m') as payment_time",
                    "FROM_UNIXTIME(payment_time,'%m-%d') as payment_day",
                    "FROM_UNIXTIME(payment_time,'%Y-%m-%d') as payment_full_day",
                ])
            ->select();
    }

    public function getStatisticPaymentSalerCount($input){
        $paymentMap = new Query();
        $paymentMap->where("a.exam_status", 3);
        $paymentMap->where("p.share_ratio", ">", 0);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $time_begin = strtotime($input["date_begin"]);
            $time_end = strtotime($input["date_end"]) + 86399;
            $paymentMap->where("a.payment_time", "between", [$time_begin, $time_end]);
        }

        $paymentSql = Db::name("sale_report_payment")->alias("a")->where($paymentMap)
            ->join("sale_report_payment_person p", "p.report_payment_id = a.id", "inner")
            ->field(["p.saler_id", "sum(a.payment_money * p.share_ratio / 100) as saler_achievement"])
            ->group("p.saler_id")
            ->buildSql();

        $map = $this->getStatisticPaymentSalerMap($input);

        return Db::name("adminuser")->alias("u")->where($map)
            ->join("role_department d", "d.role_id = u.uid", "inner")
            ->join("sale_team_map m", "m.current_id = u.id and m.module = 2", "left")
            ->join("sale_team t", "t.id = m.top_id", "left")
            ->join([$paymentSql => "t1"], "t1.saler_id = u.id", "left")
            ->count('u.id');
    }

    public function getStatisticPaymentSalerList($input, $offset = 0, $limit = 0){
        // 个人业绩查询
        $paymentMap = new Query();
        $paymentMap->where("a.exam_status", 3);
        $paymentMap->where("p.share_ratio", ">", 0);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $time_begin = strtotime($input["date_begin"]);
            $time_end = strtotime($input["date_end"]) + 86399;
            $paymentMap->where("a.payment_time", "between", [$time_begin, $time_end]);
        }

        $paymentSql = Db::name("sale_report_payment")->alias("a")->where($paymentMap)
            ->join("sale_report_payment_person p", "p.report_payment_id = a.id", "inner")
            ->field([
                "p.saler_id",
                "sum(a.payment_money * p.share_ratio / 100) as saler_achievement",
                "sum((a.platform_money + a.deposit_to_platform_money) * p.share_ratio / 100) as platmoney_achievement",
                "sum((a.payment_money - a.platform_money - a.deposit_to_platform_money) * p.share_ratio / 100) as saler_noplat_achievement",
            ])
            ->group("p.saler_id")
            ->buildSql();

        // 个人目标查询
        $indicatorsMap = new Query();
        $indicatorsMap->where("module", 2);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $month_begin = date("Ym", strtotime($input["date_begin"]));
            $month_end = date("Ym", strtotime($input["date_end"]));
            $indicatorsMap->where("date", "between", [$month_begin, $month_end]);
        }

        $indicatorsSql = Db::name("sale_indicators")->where($indicatorsMap)
            ->field(["current_id as saler_id", "sum(value) as saler_indicators"])
            ->group("current_id")
            ->buildSql();

        // 团队业绩查询
        $teamMap = new Query();
        $teamMap->where("module", 2);
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $time_begin = strtotime($input["date_begin"]);
            $time_end = strtotime($input["date_end"]) + 86399;
            $teamMap->where("datetime", "between", [$time_begin, $time_end]);
        }
        $teamSql = Db::name("sale_team_achievement")->where($teamMap)
            ->field(["saler_id", "sum(achievement) as team_achievement"])
            ->group("saler_id")
            ->buildSql();

        // 排序
        $orderby = "saler_achievement desc,u.id desc";
        if (!empty($input["sort_field"])) {
            $sort_rule = "ASC";
            if (!empty($input["sort_rule"]) && strtoupper($input["sort_rule"]) == "DESC") {
                $sort_rule = "DESC";
            }

            $sortFields = ["saler_achievement", "saler_finish_ratio", "team_achievement", "saler_indicators"];
            if (in_array($input["sort_field"], $sortFields)) {
                $orderby = sprintf("%s %s,u.id desc", $input["sort_field"], $sort_rule);
            }
        }

        // 主查询
        $map = $this->getStatisticPaymentSalerMap($input);
        $list = Db::name("adminuser")->alias("u")->where($map)
            ->join("role_department d", "d.role_id = u.uid", "inner")
            ->join("sale_team_map m", "m.current_id = u.id and m.module = 2", "left")
            ->join("sale_team t", "t.id = m.top_id", "left")
            ->join("adminuser l", "l.id = t.team_leader", "left")
            ->join([$paymentSql => "t1"], "t1.saler_id = u.id", "left")
            ->join([$indicatorsSql => "t2"], "t2.saler_id = u.id", "left")
            ->join([$teamSql => "t3"], "t3.saler_id = u.id", "left")
            ->field([
                    "u.id as saler_id", "u.`user` as saler_name", "u.`name` as saler_nickname", "t.id as team_id", "t.team_name",
                    "t.team_leader", "l.`user` as team_leader_name",
                    "t1.saler_achievement", "t1.platmoney_achievement", "t1.saler_noplat_achievement",
                    "t2.saler_indicators", "t3.team_achievement",
                    "(saler_achievement / saler_indicators) as saler_finish_ratio"
                ])
            ->order($orderby)
            ->limit($offset, $limit)
            ->select();

        return $list;
    }

    private function getStatisticPaymentSalerMap($input){
        $map = new Query();
        // $map->where("u.stat", 1);
        // $map->where("u.state", 1);
        if (is_array($input["department_id"])) {
            $map->where("d.department_id", "in", $input["department_id"]);
        } else {
            $map->where("d.department_id", $input["department_id"]);
        }

        if (!empty($input["user_ids"])) {
            $map->where("u.id", "in", $input["user_ids"]);
        }

        if (!empty($input["saler_name"])) {
            $map->where("u.user", "like", "%".$input["saler_name"]."%");
        }

        if (!empty($input["team_id"])) {
            $map->where("t.id", $input["team_id"]);
        }
        if (!empty($input["has_achievement"])) {
            if($input["has_achievement"] == 1){
                $map->where('t1.saler_achievement is not null');
            }else{
                $map->where('t1.saler_achievement is null');
            }
        }

        return $map;
    }

    public function getTeamReportPaymentCountByDay($begin,$end)
    {
        $map[] = ["exam_time","EGT",$begin];
        $map[] = ["exam_time","ELT",$end];
        $map[] = ["exam_status","EQ",3];
        return $this->where($map)->count();
    }

    public function getMemberChart($begin,$end)
    {
        $map[] = ["a.payment_time","EGT",$begin];
        $map[] = ["a.payment_time","ELT",$end];
        $map[] = ["a.exam_status","EQ",3];
        $map[] = ["a.is_delete","EQ",1];

        return $this->where($map)->alias("a")
            ->field("cooperation_type,sum(payment_money) as money ")
            ->group("cooperation_type")
            ->select();
    }

    public function getSalerAchievement($date_begin, $date_end){
        $map = new Query();
        $map->where("a.exam_status", 3);
        $map->where("b.share_ratio", ">", 0);

        if (!empty($date_begin)) {
            $time_begin = strtotime($date_begin);
            $map->where("a.payment_time", ">=", $time_begin);
        }

        if (!empty($date_end)) {
            $time_end = strtotime($date_end) + 86399;
            $map->where("a.payment_time", "<=", $time_end);
        }

        return $this->alias("a")->where($map)
            ->join("sale_report_payment_person b", "b.report_payment_id = a.id", "inner")
            ->join("adminuser u", "u.id = b.saler_id", "left")
            ->field([
                    "b.saler_id", "u.`user` as saler_user", "u.`name` as saler_name",
                    "sum(a.payment_money * b.share_ratio / 100) as achievement",
                    "sum(if(a.payment_money < 0, a.payment_money * b.share_ratio / 100, 0)) as refund_achievement"
                ])
            ->group("b.saler_id")
            ->order("achievement desc")
            ->select();
    }

    // 获取小报备业绩明细总数量
    public function getStatisticSumCount($input)
    {
        $map = $this->getStatisticDetailedMap($input);

        return $this->alias("a")->where($map)
            ->field([
                "sum(a.payment_total_money)" => 'payment_total_money',
                "sum(a.payment_money)" => 'payment_money',
                "sum(if(a.cooperation_type = 11, a.refund_money, 0))" => 'refund_money',
            ])
            ->find();
    }

    // 获取会员公司名称重复次数
    public function getCompanyRepeatCount($report_id, $days = 1){
        $map = new Query();
        $map->where("b.is_delete", 1);
        $map->where("b.exam_status", "in", [2,3,4]);
        $map->where("b.submit_time <= a.submit_time");
        $map->where("b.submit_time > a.submit_time - " . 86400 * $days);
        $map->where("b.id <> a.id");

        if (is_array($report_id)) {
            $map->where("a.id", "in", $report_id);
        } else {
            $map->where("a.id", $report_id);
        }

        return $this->alias("a")->where($map)
            ->join("sale_report_payment b", "a.company_name = b.company_name")
            ->field(["a.id", "count(b.id) as repeat_count"])
            ->group("a.id")
            ->select();
    }

    // 获取返点小报备统计
    public function getOrderBackTotal($orderIds){
        $map = new Query();
        $map->where("order_id", "in", $orderIds);
        $map->where("cooperation_type", "eq", 8);
        $map->where("exam_status", "eq", 3);
        $map->where("is_delete", "eq", 1);

        return $this->where($map)->field([
                    "order_id",
                    "count(id) as payment_num",
                    "sum(payment_total_money) as payment_money"
                ])
            ->group("order_id")
            ->select();
    }

    /*
    * 查看已存在的符合条件的报备
    */
    public function getAlreadyReport($com_id, $report_id){
        $map = [
            ['p.company_id', '=', $com_id],
            ['p.exam_status', '<>', 1],
            ['zx.status', '<>', 8]
        ];

        if($report_id){
            $map[] = ['p.id', '<>', $report_id];
        }

        return $this->alias('p')
        ->leftjoin('qz_sale_report_related r', 'p.id=r.report_payment_id')
        ->leftjoin('qz_sale_report_zx_company zx', 'r.report_id=zx.id')
        ->where($map)
        ->select();
    }
}