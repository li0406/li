<?php

/**
 * 用户活跃度
 */
namespace Common\Model;
use Think\Model;

class UserActiveModel extends Model{

    protected $autoCheckFields = false;

    //取用户登录次数  近一月 （每天登陆多次即默认一次）
    public function getLoginCount($uid){
        $map = array(
            'time' => array("EXP",' >= date_sub(curdate(),interval 1 month)'),
            'userid' => array("EQ",$uid),
            'action' => array("EQ",'Index/login')
        );
        $result = M('log_user')->field('DATE_FORMAT(time,"%Y-%m-%d") AS days')->where($map)->group('days')->select();
        return count($result);
    }

    //获取 参与齐装网组织的活动次数
    public function getActivityEnroll($uid){
        $map = array(
            'uid' => array("EQ",$uid),
        );
        return M('activity_enroll')->field('id')->where($map)->count();
        //return count($result);
    }

    //获取 新版装修公司活动数 近一月
    public function getNewEvents($uid){
        $map = array(
            'time' => array("EXP",' >= UNIX_TIMESTAMP(date_sub(curdate(),interval 1 month)) '),
            'cid' => array("EQ",$uid),
            'check' => array("EQ",'1'),//审核通过
            'del' => array("EQ",'1'),//不删除
        );
        return M('company_activity')->field('id')->where($map)->count();
        //return count($result);
    }

    //获取 旧版装修公司活动数 近一月
    public function getOldEvents($uid){
        $map = array(
            'time' => array("EXP",' >= UNIX_TIMESTAMP(date_sub(curdate(),interval 1 month)) '),
            'uid' => array("EQ",$uid),
            'type' => array("EQ",'1'),
        );
        return M('info')->field('id')->where($map)->count();
    }

    //获取 装修公司通过审核的百科数
    public function getBaikeCount($uid){
        $map = array(
            'uid' => array("EQ",$uid),
            'visible' => array("EQ",'0'),
            'remove' => array("EQ",'0'),
        );
        return M('baike')->field('id')->where($map)->count();
    }

    //获取 装修公司提出问题数量 近一月
    public function getAskCount($uid){
        $map = array(
            'time' => array("EXP",' >= UNIX_TIMESTAMP(date_sub(curdate(),interval 1 month)) '),
            'uid' => array("EQ",$uid),
            'visible' => array("EQ",'0'),
        );
        return M('ask')->field('id')->where($map)->count();
    }

    //获取 问题回答数量 近一月
    public function getAnswerCount($uid){
        $map = array(
            'time' => array("EXP",' >= UNIX_TIMESTAMP(date_sub(curdate(),interval 1 month)) '),
            'uid' => array("EQ",$uid),
            'visible' => array("EQ",'0'),
        );
        return M('ask_anwser')->field('id')->where($map)->count();
    }
}