<?php

namespace app\common\model\db;

use think\Model;
use think\Db\Query;

class SaleTeamAchievement extends Model
{
    public function getAllPerformance($begin,$end)
    {
        $map[] = ["datetime","EGT",$begin];
        $map[] = ["datetime","ELT",$end];
        return $this->where($map)->field("FROM_UNIXTIME(datetime,'%m-%d') as date,sum(achievement) as achievement")
             ->group("date")->order("date")->select();

    }

    // 个人业绩回滚
    public function salerAchievementRollback($saler_id, $achievement, $datetime){
        $map = new Query();
        $map->where("module", 1);
        $map->where("saler_id", $saler_id);
        $map->where("datetime", $datetime);

        return $this->where($map)->setDec("achievement", $achievement);
    }

    // 团队领导人业绩回滚
    public function teamLeadersAchievementRollback($team_leaders, $achievement, $datetime){
        $map = new Query();
        $map->where("module", 2);
        $map->where("datetime", $datetime);
        $map->where("saler_id", "in", $team_leaders);

        return $this->where($map)->setDec("achievement", $achievement);
    }

    // 团队业绩回滚
    public function teamAchievementRollback($team_ids, $achievement, $datetime)
    {
        $map = new Query();
        $map->where("module", 3);
        $map->where("datetime", $datetime);
        $map->where("team_id", "in", $team_ids);

        return $this->where($map)->setDec("achievement", $achievement);
    }

    // 查询团队业绩
    public function getTeamAchievement($team_ids, $month_start, $month_end)
    {
        $month_start = strtotime($month_start);
        $month_end = strtotime(date('Y-m-d 00:00:00', strtotime($month_end)) . '+1 month -1 seconds');

        $map = new Query();
        $map->where('module', 3);
        $map->where('team_id', 'IN', $team_ids);
        $map->where('datetime', '>=', $month_start);
        $map->where('datetime', '<=', $month_end);
        return $this->field('team_id,SUM(achievement) achievement, FROM_UNIXTIME(datetime, \'%Y%m\') reportdate')->where($map)->group('team_id, reportdate')->select();
    }

    /**
     * 个人业绩统计
     * @param $id
     * @param $begin
     * @param $end
     */
    public function getPersonAchievement($ids,$begin,$end)
    {
        $map[] = ["module","EQ",1];
        $map[] = ["saler_id","IN",$ids];
        $map[] = ["datetime","EGT",$begin];
        $map[] = ["datetime","ELT",$end];
        $buildSql = $this->where($map)->field("from_unixtime(datetime,'%Y%m') as date,achievement")->buildSql();

        return $this->table($buildSql)->alias("t")
                    ->field("date,sum(achievement) as achievement")->group("t.date")->select();
    }
}