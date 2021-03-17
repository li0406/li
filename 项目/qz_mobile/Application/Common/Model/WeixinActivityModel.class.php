<?php
/**
 *  微信活动信息表
 */

namespace Common\Model;

use Think\Model;

class WeixinActivityModel extends Model
{
    protected $tableName = "weixin_activity";

    public function getInfoByOpenid($openid,$activity = '')
    {
        $map = array(
            "openid" => array("EQ", $openid)
        );
        if($activity){
            $map['activity_id'] = ['eq',$activity];
        }
        return M("weixin_activity")->where($map)->find();
    }

    //根据id获取用户
    public function getInfoById($id)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        return M("weixin_activity")->where($map)->find();
    }

    //第一次进来保存用户数据
    public function saveActivityInfo($data)
    {
        //获取活动id
        $activityInfo = M('activity')->where(['name' => ['eq', '齐装网双节活动-助力抢双人游']])->field('id,name')->find();
        $save = [
            'activity_sign' => $activityInfo['name'],
            'activity_id' => $activityInfo['id'],
            'openid' => $data['openid'],
            'integral_amount' => 1000,
            'addtime' => time()
        ];
        return M('weixin_activity')->add($save);
    }

    //根据id更新用户数据
    public function updateActivityInfo($data, $id)
    {
        $where['id'] = ['eq', $id];
        return M('weixin_activity')->where($where)->save($data);
    }

    public function getWinningCount($map){
        return M('weixin_activity')->where($map)->count();
    }

    public function saveUserInfo($data){;
        return M('weixin_activity')->add($data);
    }
}