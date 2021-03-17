<?php

namespace app\common\model\db;

use think\Model;

class SaleTeam extends Model
{
    public function getTeamInfoByName($name)
    {
        $map[] = ["team_name","EQ",$name];
        return $this->where($map)->find();
    }

    public function addTeam($data)
    {
         $this->allowField(true)->insert($data);
         return $this->getLastInsID();
    }

    public function updateTeam($id,$data)
    {
        $map[] = ["id","EQ",$id];
        return $this->where($map)->update($data);
    }

    public function updateGroupTeam($team_id,$data)
    {
        $map[] = ["id","IN",$team_id];
        return $this->where($map)->update($data);
    }

    public function getTeamInfoById($id,$module = 1,$status = 0)
    {
        $map[] = ["a.id","EQ",$id];
        $map[] = ["b.module","EQ",$module];

        $buildSql = $this->where($map)->alias("a")
                    ->join("sale_team_map b","b.current_id = a.id")
                    ->join("sale_team c","c.id = b.top_id","left")
                    ->join("adminuser u","u.id = a.team_leader","left")
                    ->field("b.current_id,a.team_name,c.team_name as top_name,b.top_id,a.description,a.px,a.is_top,a.status,a.team_leader,u.name as team_leader_name,a.team_assistant")
                    ->buildSql();
        return $this->table($buildSql)->alias("t")
                    ->join("sale_team_manage_extend b","b.team_id = t.current_id","left")
                    ->join("adminuser u","u.id = b.user_id","left")
                    ->field("t.*,group_concat(b.user_id) as manager_id,group_concat(u.name) as manager_name")->find();
    }

    public function getTopTeamList($is_top = 0, $status = false)
    {
        $map[] = ["b.module", "EQ", 1];
        if (!empty($is_top)) {
            $map[] = ["a.is_top","EQ", $is_top];
        }

        if (!empty($status)) {
            $map[] = ["a.status","EQ", 1];
        }

        return $this->where($map)->alias("a")
                    ->join("sale_team_map b","a.id =b.current_id")
                    ->field("a.team_name,a.id,a.is_top")->order("status,a.is_top,a.id")
                    ->select();
    }

    public function getTeamListCount($name,$leader,$team,$status)
    {
        $map[] = [ "b.module" ,"EQ",1];
         if (!empty($name)) {
             $map[] = ["a.team_name","LIKE","%".$name."%"];
         }

         if (!empty($leader)) {
             $map[] = ["a.team_leader","EQ",$leader];
         }

         if (!empty($team)) {
            $map[] = ["a.id","IN",$team];
         }

         if (!empty($status)) {
             $map[] = ["a.status","EQ",$status];
         }

         return $this->alias("a")->where($map)
                     ->join("sale_team_map b","b.current_id = a.id","left")
                     ->count();
    }

    public function getTeamList($name,$leader,$team,$status,$pageIndex,$pageCount)
    {
        $map[] = [ "b.module" ,"EQ",1];
        if (!empty($name)) {
            $map[] = ["a.team_name","LIKE","%".$name."%"];
        }

        if (!empty($leader)) {
            $map[] = ["a.team_leader","EQ",$leader];
        }

        if (!empty($team)) {
            $map[] = ["a.id","IN",$team];
        }

        if (!empty($status)) {
            $map[] = ["a.status","EQ",$status];
        }
        $buildSql = $this->alias("a")->where($map)
                    ->join("sale_team_map b","b.current_id = a.id","left")
                    ->join("sale_team c","c.id = b.top_id","left")
                    ->field("a.id,a.team_name,a.team_leader,a.description,c.team_name as top_name,a.status")->limit($pageIndex,$pageCount)
                    ->order("a.status,b.top_id,a.id")
                    ->buildSql();

        return $this->table($buildSql)->alias("t")
                    ->join("adminuser u","u.id = t.team_leader")
                    ->field("t.*,u.name as team_leader_name")
                    ->select();
    }

}