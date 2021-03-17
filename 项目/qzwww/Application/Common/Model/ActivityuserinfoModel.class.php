<?php
/**
 *  活动注册用户
 */
namespace Common\Model;
use Think\Model;
class ActivityuserinfoModel extends Model{
    protected $tableName = "activity_userinfo";

    /**
     * 添加用户
     */
    public function addUserInfo($data){
        return M("activity_userinfo")->add($data);
    }

    /**
     * 查询用户
     */
    public function findUserInfo($order_id)
    {
        return M("activity_userinfo")->where(['order_id' => $order_id])->find();
    }
}