<?php
/**
 *  子帐号，假帐号
 */
namespace Common\Model;
use Think\Model;

class PeruserModel extends Model{

    //设置虚拟模型
    protected $autoCheckFields = false;

    //取用户列表
    public function getUserList($condition,$pagesize= 1,$pageRow = 10){
        if(isset($condition['user_type'])){
            $map['user_type']  = array("EQ",$condition['user_type']);
        }
        //如果关键词不为空
        if(isset($condition['register_admin_id'])){
            $map['register_admin_id']  = array('EQ',$condition['register_admin_id']);
        }
        $Db = M('user');
        $count = $Db->where($map)->count();
        $result = $Db->field('*')
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }



    //取提问中图片列表
    public function getQuestionImg($id){
        $map["fid"] = $id;
        $map["type"] = '1';
        return M('ask_file')->field('type,qid,fid,path')->order('time DESC')->where($map)->select();
    }

    /**
     * 根据 Anwser ID 查询单个答案 （包含用户信息）
     */
    public function getAnwserById($aid){
        $map = array(
            "a.id"=>$aid,
            'a.visible'=>'0'
        );
        return M('ask_anwser')->alias("a")
                      ->field('a.*,u.id uid,u.name,u.logo,u.ask_anwsers,u.ask_adopts,u.ask_agrees')
                      ->join("inner join qz_user as u on a.uid = u.id")
                      ->where($map)
                      ->find();
    }

    /**
     * 查询单个问题
     */
    public function getAskByid($id){
        $map = array(
            "id"=>$id,
            'visible'=>'0'
        );
        return M('ask')->field('*')->where($map)->find();
    }

    //添加用户
    public function addUser($data){
        return M("user")->add($data);
    }


    //采纳答案
    public function adoptAnwser($data){
        $map = array(
                "id"=>$data['id'],
        );
        return M("ask")->where($map)->save($data);
    }


    //更新浏览量
    public function updateViews($id){
        return M("Ask")->where('id='.$id)->setInc('views');
    }
}