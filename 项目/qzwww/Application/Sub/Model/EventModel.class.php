<?php

//分站活动 Model

namespace Common\Model;
use Think\Model;

class EventModel extends Model{

    protected $autoCheckFields = false;

    //获取列表
    public function getList($condition,$pageIndex,$pageCount){
        $Db = M('company_activity');

        $count  = $Db->alias("c")->join("")->where($condition)->count();
        $result = $Db->alias("c")->field('id,cid,title,text,start,end,time,check,types,del,state')
                                ->where($condition)
                                ->order($orderby)
                                ->limit($pageIndex.",".$pageCount)->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询单个
    public function getEvent($id){
        $map['check'] = array('EQ','1');
        $map['del'] = array('EQ','1');
        $map["id"] = array("EQ",$id);
        return M('company_activity')->field('*')->where($map)->find();
    }

    //更新浏览量
    public function updateViews($id){
        return M("company_activity")->where(array('id' => $id))->setInc('views');
    }
}