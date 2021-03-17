<?php

namespace app\common\model\db;

use app\index\enum\WorkSiteLiveStepCodeEnum;
use think\Db;
use think\db\Query;
use think\db\Where;
use think\Model;

class WorkSiteLive extends Model
{

    protected $autoWriteTimestamp = false;
    protected $table = 'qz_worksite_live';

    public function getWorkSiteInfo($where, $field = '*')
    {
        return $this->field($field)->where(new Where($where))->find();
    }

    public function getWorkInfo($where, $field = 'i.*'){
        return $this->alias('l')
            ->field($field)
            ->join('qz_worksite_info i','l.id = i.live_id')
            ->where(new Where($where))
            ->select();
    }

    public function saveWorkSite($save)
    {
        return $this->insertGetId($save);
    }

    public function delWorkSiteLive($where = []){
        return $this->where($where)->delete();
    }
    public function delWorkSiteMedia($where = []){
        return Db::table('qz_worksite_media')->where($where)->delete();
    }
    public function delWorkSiteInfo($where = []){
        return Db::table('qz_worksite_info')->where($where)->delete();
    }
    public function delWorkSiteUserRelate($where = []){
        return Db::table('qz_worksite_user_relate')->where($where)->delete();
    }

    /**
     * 工地直播列表查询总数
     * @param $input
     * @return float|string
     */
    public function getPageCount($input){
        $dbQuery = $this->getPageQuery($input);
        return $dbQuery->count("t.id");
    }

    /**
     * 工地直播列表查询数据
     * @param $input
     * @param int $offset
     * @param int $limit
     * @return array|string|\think\Collection
     */
    public function getPageList($input, $offset = 0, $limit = 0){
        $dbQuery = $this->getPageQuery($input);

        return $dbQuery
            ->join("quyu q", "q.cid = t.cs", "left")
            ->join("area area", "area.qz_areaid = t.qx", "left")
            ->join("huxing hx", "hx.id = t.huxing", "left")
            ->field(["t.*", "q.cname as city_name", "area.qz_area as area_name","hx.`name` as huxing_name"])
            ->order("t.datetime desc,t.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    /**
     * 工地直播查询条件
     * @param $input
     * @return Query
     */
    public function getPageQuery($input){
        // 普通单查询条件
        $orderMap = new Query();
        $orderMap->where("o.cs", "in",$input["cs"]);
        $orderMap->where("a.order_type", 1);
        $orderMap->where("o.on", 4);
        $orderMap->where("o.deleted", "<>", 1);

        // 咨询单查询条件
        $reportMap = new Query();
        $reportMap->where("r.cs", "in",$input["cs"]);
        $reportMap->where("a.order_type", 2);
        $reportMap->where("r.deleted", 0);

        //搜索地区
        if (!empty($input["qx"])) {
            $orderMap->where("o.qx", "=", $input["qx"]);
            $reportMap->where("r.qx", "=", $input["qx"]);
        }

        //装修公司
        if (!empty($input["company"])) {
            if (is_numeric($input["company"])) {
                $orderMap->where("a.company_id", "=", $input["company"]);
                $reportMap->where("a.company_id", "=", $input["company"]);
            } else {
                $orderMap->where("u.jc", "LIKE", "%" . $input["company"] . "%");
                $reportMap->where("u.jc", "LIKE", "%" . $input["company"] . "%");
            }
        }

        //业主
        if (!empty($input["proprietor"])) {
            $orderMap->where("o.name|o.xiaoqu", "LIKE", "%" . $input["proprietor"] . "%");
            $reportMap->where("r.name|r.xiaoqu", "LIKE", "%" . $input["proprietor"] . "%");
        }

        $search_table = 1;//查询两张表融合
        //查询订单分类 (要区分正常单和主动咨询单)
        if (!empty($input["type"])) {
            switch ($input["type"]) {
                case WorkSiteLiveStepCodeEnum::LIST_STATUS_CODE_F:
                    $orderMap->where("o.type_fw", '=', 1);
                    $search_table = 2;//只查询正常单
                    break;
                case WorkSiteLiveStepCodeEnum::LIST_STATUS_CODE_Z:
                    $orderMap->where("o.type_fw", '=', 2);
                    $search_table = 2;//只查询正常单
                    break;
                case WorkSiteLiveStepCodeEnum::LIST_STATUS_CODE_FZ:
                    $orderMap->where("o.type_fw", 'in', [1, 2]);
                    $search_table = 2;//只查询正常单
                    break;
                case WorkSiteLiveStepCodeEnum::LIST_STATUS_CODE_ZX:
                    $search_table = 3;//只查询主动咨询单
                    break;
            }
        }

        //签约时间
        if (!empty($input["start_time"]) || !empty($input["end_time"])) {
            $start = !empty($input["start_time"])?strtotime($input["start_time"]) : strtotime('-6 month', strtotime($input["end_time"]));
            $end = !empty($input["end_time"])?strtotime($input["end_time"].' 23:59:59'): strtotime('+6 month', strtotime($input["start_time"]));
            $orderMap->where("o.qiandan_addtime", "between", [$start, $end]);
            $reportMap->where("r.time_add", "between", [$start, $end]);
        }

        // 工地号
        if (!empty($input["code"])) {
            $orderMap->where("a.order_id|a.code", "=", $input["code"]);
            $reportMap->where("a.order_id|a.code", "=", $input["code"]);
        }

        //施工状态
        if (!empty($input["step"])) {
            $orderMap->where("i.step", "=", $input["step"]);
            $reportMap->where("i.step", "=", $input["step"]);
        }

        // 普通订单查询（使用union必须保证两个SQL字段一致）
        $orderSql = $this->alias("a")->where($orderMap)
            ->join("orders o", "o.id = a.order_id", "inner")
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("worksite_info i", "i.live_id = a.id", "left")
            ->field([
                "a.id", "a.code", "a.order_id", "a.order_type", "a.company_id", "a.state", "a.endtime",
                "o.xiaoqu", "o.`name` as yz_name", "o.qiandan_addtime as datetime", "o.mianji", "o.type_fw",
                "o.cs", "o.qx", "o.huxing", "0 as shi", "0 as ting", "0 as wei", "o.qiandan_jine", "u.jc company_jc"
            ])->group('a.id')
            ->buildSql();

        // 咨询单查询（使用union必须保证两个SQL字段一致）
        $reportSql = $this->alias("a")->where($reportMap)
            ->join("orders_company_report r", "a.order_id = r.id", "inner")
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("worksite_info i", "i.live_id = a.id", "left")
            ->field([
                "a.id", "a.code", "a.order_id", "a.order_type", "a.company_id", "a.state", "a.endtime",
                "r.xiaoqu", "r.`name` as yz_name", "r.time_add as datetime", "r.mianji", "0 as type_fw",
                "r.cs", "r.qx", "r.huxing", "r.shi", "r.ting", "r.wei","0 as qiandan_jine","u.jc company_jc"
            ])->group('a.id')
            ->buildSql();

        switch ($search_table){
            case 1:
                $sql = sprintf("(%s UNION ALL %s)", $orderSql, $reportSql);
                break;
            case 2:
                $sql = $orderSql;
                break;
            case 3:
                $sql = $reportSql;
                break;
        }
        $dbQuery = Db::table($sql)->alias("t");
        return $dbQuery;
    }

    /**
     * 获取装修订单信息
     * @param $order_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderInfo($order_id){
        return Db::name("orders")->alias("o")
            ->join(["qz_user" => "u"], "u.id = o.qiandan_companyid", "inner")
            ->join("quyu q", "q.cid = o.cs", "left")
            ->join("area area", "area.qz_areaid = o.qx", "left")
            ->join("huxing hx", "hx.id = o.huxing", "left")
            ->where("o.id", $order_id)
            ->field([
                "o.xiaoqu", "o.`name` as yz_name", "o.qiandan_addtime as datetime", "o.mianji", "o.type_fw", "o.dz",
                "o.qiandan_companyid as company_id","o.cs", "o.qx", "o.huxing", "0 as shi", "0 as ting", "0 as wei",
                "q.cname as city_name", "area.qz_area as area_name", "hx.`name` as huxing_name", "u.jc company_jc", "o.qiandan_jine"
            ])->find();
    }

    /**
     * 获取咨询单信息
     * @param $order_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderReportInfo($order_id){
        return Db::name("orders_company_report")->alias("r")
            ->join(["qz_user" => "u"], "u.id = r.order_company_id", "inner")
            ->join("quyu q", "q.cid = r.cs", "left")
            ->join("area area", "area.qz_areaid = r.qx", "left")
            ->join("huxing hx", "hx.id = r.huxing", "left")
            ->where("r.id", $order_id)
            ->field([
                "r.xiaoqu", "r.`name` as yz_name", "r.tel168 as tel", "r.time_add as datetime", "r.mianji", "0 as type_fw", "r.dz",
                "r.order_company_id as company_id", "r.cs", "r.qx", "r.huxing", "r.shi", "r.ting", "r.wei","r.yusuanjt",
                "q.cname as city_name", "area.qz_area as area_name", "hx.`name` as huxing_name",'u.jc company_jc'
            ])->find();
    }
}