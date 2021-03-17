<?php
// +----------------------------------------------------------------------
// | TelcenterLogic 电话呼叫相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\index\model\adb\LogClinkTelcenter;

class TelcenterLogic {

    protected $clinkTelcenterModel;

    public function __construct(){
        $this->clinkTelcenterModel = new LogClinkTelcenter();
    }

    // 区域订单维度天润电话呼叫行为统计
    public function getCityAreaOrderTelcenterStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->clinkTelcenterModel->getAreaOrderTelcenterStatistic($date_begin, $date_end, $city_ids, $area_ids);
        return $list;
    }
}