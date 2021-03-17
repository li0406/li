<?php

/**
 * 专题页需要用到的Model
 */

namespace Common\Model;
use Think\Model;

class ZtModel extends Model{

    protected $autoCheckFields = false;


    //查询最新签约公司
    public function getNewVipCompany($limit = 10){
        $map['v.new_state']  = array("EQ",'2');
        //$map['v.old_state'] = array(array('EQ','-1'),array('EQ','0'), 'or') ;
        $map['v.old_state'] = array('IN','-1,0');

        $result = M('log_vip')->alias("v")
                      ->field('u.qc,q.bm,u.logo,v.optime,v.comid')
                      ->join("inner join qz_user as u on u.id = v.comid")
                      ->join("inner join qz_quyu as q on u.cs = q.cid")
                      ->order('v.optime DESC')
                      ->limit("0,".$limit)
                      ->where($map)
                      ->select();
        return $result;
    }


    public function getActivity($name){
        $map['name'] = array('EQ',$name);
        return M('activity')->field('*')->where($map)->find();
    }

    //修改活动信息
    public function updateActivity($id,$data){
        $map['id'] = array('EQ',$id);
        return M("activity")->where($map)->save($data);
    }

    //修改活动信息
    public function updateEnrollCount($id,$data){
        $map['id'] = array('EQ',$id);
        return M("activity")->where($map)->setInc('fake_enroll_count',$data);
    }

}