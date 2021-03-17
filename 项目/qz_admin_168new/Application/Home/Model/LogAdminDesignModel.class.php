<?php
/** 设计师团队表  对应qz_log_admin_design表*/
namespace Home\Model;
Use Think\Model;
class LogAdminDesignModel extends Model{
    protected $tableName = "log_admin_design";

    //添加日志信息
    public function addData($data){
        return $this->add($data);
    }

    //获取日志列表
    public function getDesignLogListById($id){
        $map=[];
        $map['designid'] = $id;
        return $this->where($map)->order('addtime desc')->select();
    }

}