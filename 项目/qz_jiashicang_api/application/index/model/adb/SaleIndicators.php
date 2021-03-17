<?php

// +----------------------------------------------------------------------
// | SaleIndicators 销售指标数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\db\Query;

use app\common\model\adb\BaseModel;

class SaleIndicators extends BaseModel {

    // 获取团队业绩统计
    public function getTeamIndicatorsTotal($begin, $end, $team_id = 0){
        $map = new Query();
        $map->where("a.module", 1);
        $map->where("a.date", "between", [$begin, $end]);

        if (!empty($team_id)) {
            if (is_array($team_id)) {
                $map->where("a.current_id", "in", $team_id);
            } else {
                $map->where("a.current_id", $team_id);
            }
        }

        $list = $this->link()->name("sale_indicators")->alias("a")->where($map)
            ->field([
                "a.date as `month`",
                "sum(a.value) as indicators"
            ])
            ->group("date")
            ->select();

        return $list;
    }

}