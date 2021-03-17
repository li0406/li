<?php

namespace app\common\model\db;
use think\Model;

class SaleTeamMap extends Model
{
    public function delTeamMap($current_id)
    {
        $map[] = ["current_id","EQ",$current_id];
        return $this->where($map)->delete();
    }

    public function addTeamMap($data)
    {
        return $this->allowField(true)->insert($data);
    }

    public function getAllTeamMember()
    {
        return $this->alias("a")
                    ->join("adminuser u","u.id = a.current_id and a.module = 2","left")
                    ->join("rbac_role r","r.id = u.uid","left")
                    ->join("sale_team t","t.id = a.top_id","left")
                    ->join("sale_team d","d.id = a.current_id and a.module = 1","left")
                    ->join("adminuser e","e.id = d.team_leader and a.module = 1","left")
                    ->field("d.team_leader as team_leader_id,e.`name` as team_leader,d.description as current_description,d.`status` as team_status,d.team_name as current_name,d.team_assistant as team_assistant_id,top_id,current_id,module,u.`user` as user_name,r.role_name,t.team_name as top_name,u.`stat` as status,u.`state`")->order("module,top_id,current_id")->select();
    }

    public function delTeamMemberMap($current_id)
    {
        $map[] = ["current_id","EQ",$current_id];
        $map[] = ["module","EQ",2];
        return $this->where($map)->delete();
    }

    public function getTeamMemberInfo($current_id)
    {
        $map[] = ["a.current_id","EQ",$current_id];
        $map[] = ["a.module","EQ",2];
        return $this->where($map)->alias("a")
                    ->join("sale_team t","t.id = a.top_id")
                    ->join("adminuser u","u.id = a.current_id")
                    ->join("rbac_role r","r.id = u.uid")
                    ->field("r.role_name,u.name as user_name,a.top_id,t.team_name,a.current_id")
                    ->find();
    }


    public function getTeamListByTop($top_id,$module)
    {
        $map[] = ["a.top_id","EQ",$top_id];
        $map[] = ["a.module","EQ",$module];
        return $this->where($map)->alias("a")
                    ->join("qz_sale_team b","b.id = a.top_id")
                    ->join("qz_adminuser u","u.id = b.team_leader")
                    ->field("b.team_name,a.current_id, u.name,b.team_leader")->select();
    }
}