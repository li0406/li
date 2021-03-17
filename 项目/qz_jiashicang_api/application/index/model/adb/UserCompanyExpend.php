<?php

// +----------------------------------------------------------------------
// | UserCompanyExpend 1.0会员日消耗金额落档数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class UserCompanyExpend extends BaseModel {

    // 城市会员日消耗
    public function getCityExpendAmount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("vip_status", 2);
        $map->where("cooperate_mode", 1);
        $map->where("datetime", ">=", strtotime($date_begin));
        $map->where("datetime", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("city_id", "in", $city_ids);
        }

        $list = $this->link()->name("user_company_expend")->where($map)
            ->field([
                "city_id", "sum(expend_amount) as expend_amount"
            ])
            ->group("city_id")
            ->select();

        return $list;
    }

    // 城市会员数量
    public function getCityUserCount($date, $city_ids = []){
        $map = new Query();
        $map->where("vip_status", 2);
        $map->where("cooperate_mode", 1);
        $map->where("datetime", strtotime($date));

        if (!empty($city_ids)) {
            $map->where("city_id", "in", $city_ids);
        }

        return $this->link()->name("user_company_expend")->where($map)
            ->field([
                "city_id", "count(company_id) as vip_count", "sum(viptype) as vip_num"
            ])
            ->group("city_id")
            ->select();
    }

    // 会员日消耗
    public function getCompanyExpendAmount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("vip_status", 2);
        $map->where("cooperate_mode", 1);
        $map->where("datetime", ">=", strtotime($date_begin));
        $map->where("datetime", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("company_id", "in", $company_ids);
        }

        return $this->link()->name("user_company_expend")->where($map)
            ->field([
                "company_id", "sum(expend_amount) as expend_amount"
            ])
            ->group("company_id")
            ->select();
    }

    /**
     *  @des:大报备会员1.0日消耗金额汇总
     * @param array $param
     * @return mixed
     */
    public function getUserConsumptionV1($param = [])
    {
        $map = new Query();
        $map->where("vip_status", 2);
        $map->where("cooperate_mode", 1);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('datetime', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('city_id', 'in', $param['cs']);
        }
        return $this->link()->name("user_company_expend")
            ->where($map)
            ->sum("expend_amount");
    }

    /**
     * @des:获取1.0落档会员数
     * @param array $param
     * @return mixed
     */
    public function getUserAmountV1($param=[])
    {
        $map = new Query();
        $map->where("vip_status", 2);
        $map->where("cooperate_mode", 1);
        if (isset($param['datetime']) && !empty($param['datetime'])) {
            $map->where("datetime", strtotime($param['datetime']));
        }
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where("city_id", "in", $param['cs']);
        }
        return $this->link()->name("user_company_expend")->where($map)
            ->sum('viptype');
    }
}