<?php
namespace app\index\model\logic;
use app\index\model\db\User;

class UserLogic
{
    /**
     * 三方平台是否绑定账号
     * @param $unionid 三方唯一id
     * @param $type 三方登陆类型,1:微信;2:微博;3:qq
     * @return \think\db\Query
     */
    public function getUserInfo($unionid,$type){
        $map[] = ["b.unionid",'=',$unionid];
        $map[] = ["b.oauth_type",'=',$type];
        $map[] = ["b.platform",'=',config('app.APP_FROM_PC')];
        $result = (new User())->getUserInfo($map);
        if(!empty($result[0])){
            return $result[0];
        }
    }
}