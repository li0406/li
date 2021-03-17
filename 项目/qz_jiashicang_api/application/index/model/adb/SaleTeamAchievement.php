<?php

// +----------------------------------------------------------------------
// | SaleTeamAchievement 销售团队业绩数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\db\Query;

use app\common\model\adb\BaseModel;

class SaleTeamAchievement extends BaseModel {

    // 获取团队业绩统计
    public function getTeamAchievementTotal($date_begin, $date_end, $team_id = 0){
        $map = new Query();
        $map->where("a.module", 3);
        $map->where("a.datetime", "between", [
            strtotime($date_begin),
            strtotime($date_end),
        ]);

        if (!empty($team_id)) {
            if (is_array($team_id)) {
                $map->where("a.team_id", "in", $team_id);
            } else {
                $map->where("a.team_id", $team_id);
            }
        }

        $list = $this->link()->name("sale_team_achievement")->alias("a")->where($map)
            ->field([
                "sum(a.achievement) as achievement",
                "FROM_UNIXTIME(a.datetime, '%Y%m') as `month`"
            ])
            ->group("`month`")
            ->select();

        return $list;
    }

    // 获取团队业绩统计
    public function getTeamMonthAchievementTotal($date_begin, $date_end, $team_id = 0){
        $map = new Query();
        $map->where("a.module", 3);
        $map->where("a.datetime", "between", [
            strtotime($date_begin),
            strtotime($date_end),
        ]);

        if (!empty($team_id)) {
            if (is_array($team_id)) {
                $map->where("a.team_id", "in", $team_id);
            } else {
                $map->where("a.team_id", $team_id);
            }
        }

        $list = $this->link()->name("sale_team_achievement")->alias("a")->where($map)
            ->field([
                "a.team_id",
                "sum(a.achievement) as achievement",
                "FROM_UNIXTIME(a.datetime, '%Y%m') as `month`"
            ])
            ->group("a.team_id,`month`")
            ->select();

        return $list;
    }
}