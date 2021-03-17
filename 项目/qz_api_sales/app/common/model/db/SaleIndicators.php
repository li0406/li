<?php

namespace app\common\model\db;


use think\Model;

class SaleIndicators extends Model
{
    public function editIndicators($current_id,$date,$module,$data)
    {
        $map[] = ["current_id","EQ",$current_id];
        $map[] = ["date","EQ",$date];
        $map[] = ["module","EQ",$module];
        return $this->where($map)->update($data);
    }

    public function delIndicatorsByUserIds($user_id,$year,$module = 1)
    {
        $map[] = ["current_id","IN",$user_id];
        $map[] = ["module","IN",$module];
        if (!empty($year)) {
            $map[] = ["year","EQ",$year];
        }
        return $this->where($map)->delete();
    }

    public function addAllInfo($data)
    {
        return $this->allowField(true)->insertAll($data);
    }

    public function getTeamIndicatorsListCount($year,$team_name)
    {
        $map[] = ["a.year","EQ",$year];
        $map[] = ["a.module","EQ",1];
        if (!empty($team_name)) {
            $map[] = ["b.team_name","LIKE","%$team_name%"];
        }
        $buildSql = $this->where($map)->alias("a")
                    ->join("sale_team b","a.current_id = b.id")
                    ->field("a.current_id")
                    ->group("a.year,a.current_id")->buildSql();

        return $this->table($buildSql)->alias("t")->count("t.current_id");
    }

    public function getTeamIndicatorsList($year,$team_name,$pageIndex,$pageCount)
    {
        $map[] = ["a.year","EQ",$year];
        $map[] = ["a.module","EQ",1];
        if (!empty($team_name)) {
            $map[] = ["b.team_name","LIKE","%$team_name%"];
        }
        $buildSql = $this->where($map)->alias("a")
            ->join("sale_team b","a.current_id = b.id")
            ->field("a.current_id,b.team_name,a.year,
                max(IF(a.`month` = 1,a.`value`,0))  as 'Jan',
                max(IF(a.`month` = 2,a.`value`,0))  as 'Feb',
                max(IF(a.`month` = 3,a.`value`,0))  as 'Mar',
                max(IF(a.`month` = 4,a.`value`,0))  as 'Apr',
                max(IF(a.`month` = 5,a.`value`,0))  as 'May',
                max(IF(a.`month` = 6,a.`value`,0))  as 'Jun',
                max(IF(a.`month` = 7,a.`value`,0))  as 'Jul',
                max(IF(a.`month` = 8,a.`value`,0))  as 'Aug',
                max(IF(a.`month` = 9,a.`value`,0))  as 'Sep',
                max(IF(a.`month` = 10,a.`value`,0))  as 'Oct',
                max(IF(a.`month` = 11,a.`value`,0))  as 'Nov',
                max(IF(a.`month` = 12,a.`value`,0))  as 'Dec'")
            ->group("a.year,a.current_id")->buildSql();

        return $this->table($buildSql)->alias("t")->order("current_id")->limit($pageIndex,$pageCount)->select();
    }

    public function getTeamMemberIndicatorsCount($year,$name)
    {
        $map[] = ["a.year","EQ",$year];
        $map[] = ["a.module","EQ",2];
        if (!empty($name)) {
            $map[] = ["b.user","LIKE","%$name%"];
        }
        $buildSql = $this->where($map)->alias("a")
            ->join("adminuser b","a.current_id = b.id")
            ->field("a.current_id")
            ->group("a.year,a.current_id")->buildSql();

        return $this->table($buildSql)->alias("t")->count("t.current_id");
    }

    public function getTeamMemberIndicators($year,$name,$pageIndex,$pageCount)
    {
        $map[] = ["a.year","EQ",$year];
        $map[] = ["a.module","EQ",2];
        if (!empty($name)) {
            $map[] = ["b.user","LIKE","%$name%"];
        }
        $buildSql = $this->where($map)->alias("a")
            ->join("adminuser b","a.current_id = b.id")
            ->field("a.current_id,b.user as name,a.year,
                max(IF(a.`month` = 1,a.`value`,0))  as 'Jan',
                max(IF(a.`month` = 2,a.`value`,0))  as 'Feb',
                max(IF(a.`month` = 3,a.`value`,0))  as 'Mar',
                max(IF(a.`month` = 4,a.`value`,0))  as 'Apr',
                max(IF(a.`month` = 5,a.`value`,0))  as 'May',
                max(IF(a.`month` = 6,a.`value`,0))  as 'Jun',
                max(IF(a.`month` = 7,a.`value`,0))  as 'Jul',
                max(IF(a.`month` = 8,a.`value`,0))  as 'Aug',
                max(IF(a.`month` = 9,a.`value`,0))  as 'Sep',
                max(IF(a.`month` = 10,a.`value`,0))  as 'Oct',
                max(IF(a.`month` = 11,a.`value`,0))  as 'Nov',
                max(IF(a.`month` = 12,a.`value`,0))  as 'Dec'")
            ->group("a.year,a.current_id")->buildSql();

        return $this->table($buildSql)->alias("t")->order("current_id")->limit($pageIndex,$pageCount)->select();
    }

    public function getTeamIndicators($team_id,$month_start,$month_end,$module = 1)
    {
        $map[] = ["date","EGT",$month_start];
        $map[] = ["date","ELT",$month_end];
        $map[] = ["current_id","IN",$team_id];
        $map[] = ["module","EQ",$module];

        return $this->where($map)->field("current_id,date,value")->order("date")->select();
    }
}