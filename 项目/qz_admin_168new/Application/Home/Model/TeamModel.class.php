<?php
/** 设计师团队表  对应qz_team表*/
namespace Home\Model;
Use Think\Model;
class TeamModel extends Model{
    protected $tableName = "team";

    //根据userid查询是否有已入驻的装修公司
    public function searchTeamInfoByUserId($id){
        if(empty($id)){
            return false;
        }else{
            $map = [];
            $map['t.userid'] = $id;
            $map['t.zt'] = 2;
            return $this->alias('t')
                ->where($map)
                ->field('t.*,u.name')
                ->join('qz_user u on u.id = t.userid')
                ->find();
        }
    }


    //根据设计师id 删除qz_team表中的数据
    public function deleteTeamInfoById($id){
        $map = [];
        $map['userid'] = $id;
        return $this->where($map)->delete();
    }


}