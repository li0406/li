<?php

// +----------------------------------------------------------------------
// | SaleTeam 销售团队数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\db\Query;

use app\common\model\adb\BaseModel;

class SaleTeam extends BaseModel {

    // 获取所有团队
    public function getTeamAll(){
        $map = new Query();
        $map->where("a.status", 1);

        return $this->link()->name("sale_team")->alias("a")->where($map)
            ->join("sale_team_map b", "b.current_id = a.id and b.module = 1", "left")
            ->join("sale_team_manage_extend e", "e.team_id = a.id", "left")
            ->field([
                "a.id", "a.team_name", "a.team_leader",
                "b.top_id", "group_concat(e.user_id) as manage_ids"
            ])
            ->order("a.is_top asc,a.px asc,a.id asc")
            ->group("a.id")
            ->select();
    }

    // 获取团队成员
    public function getTeamMemberList($teamIds, $state = false){
        $map = new Query();
        $map->where("a.status", 1);
        $map->where("a.id", "in", $teamIds);

        if ($state == true) {
            $map->where("u.stat", 1);
            $map->where("u.state", 1);
        }

        return $this->link()->name("sale_team")->alias("a")->where($map)
            ->join("sale_team_map b", "b.top_id = a.id and b.module = 2", "inner")
            ->join("adminuser u", "u.id = b.current_id", "left")
            ->field([ "a.id as team_id", "b.current_id as saler_id"])
            ->select();
    }

    // 获取顶级团队列表
    public function getTeamTopList(){
        $map = new Query();
        $map->where("a.status", 1);
        $map->where("b.top_id", 0);

        return $this->link()->name("sale_team")->alias("a")->where($map)
            ->join("sale_team_map b", "b.current_id = a.id and b.module = 1", "inner")
            ->field([ "a.id as team_id", "a.team_name"])
            ->order("px asc,id asc")
            ->select();
    }

}