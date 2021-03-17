<?php

/**
 * 专题页需要用到的Model
 */

namespace Common\Model;
use Think\Model;

class ZtModel extends Model{

    protected $autoCheckFields = false;


    //查询活动下报名的装修公司
    public function getEventCompanys($cid,$aid){
        $map['a.cid']  = array("EQ",$cid);
        $map['a.aid'] = array('EQ',$aid);

        return M('activity_enroll')->alias("a")
                      ->field('a.*,u.id as uid,u.qc,u.logo')
                      ->join("inner join qz_user as u on u.id = a.uid and u.classid = '3' ")
                      ->order('a.time DESC')
                      //->limit("0,".$limit)
                      ->where($map)
                      ->select();
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

    //增加投诉数据
    public function addComplain($data){
        $data['time'] = time();
        return M("activity_complain")->add($data);
    }

    //是否参加活动，查询订单表
    public function isPostOrder($tel){

        import('Library.Org.Util.App');
        $app = new \App();
        $tel_md5 =  $app->order_tel_encrypt($tel);

        $map['tel_encrypt'] = array('EQ',$tel_md5);
        $map['name'] = array('like','%[老客户]');
        return M('orders')->field('id')->where($map)->find();
    }

}