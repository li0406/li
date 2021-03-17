<?php

/**
 * 后台用户信息
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\OrdersLogic;
use app\common\model\logic\AdminuserLogic;

Class Adminuser extends Controller
{
    /**
     * 后台用户信息
     * @param Request $request
     * @return array
     */
    public function user_info(Request $request)
    {
        $user = $request->user;
        $userLogic = new AdminuserLogic();
        $info = $userLogic->getListUser($user['user_id'],1,'u.id user_id,u.name user_name,u.user,d.name dept_name,r.role_name,b.name as second_name,c.name as three_name');
        if(count($info) > 0){
            //添加头像
            $user['logo'] = '/assets/common/img/default_portrait3.jpg';
            if(strpos($user['logo'],config('QZ_YUMING')) !== false){
                $info[0]['logo'] = $user['logo'];
            }else{
                $info[0]['logo'] = config('UC_URL').$user['logo'];
            }
            return sys_response(0,'',['info'=>$info[0]]);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 后台用户管辖城市订单
     * @param Request $request
     * @return array
     */
    public function user_job()
    {
        //获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if(count($citys) > 0){
            //根据城市获取所需的订单列表
            $order = new OrdersLogic();
            $list = $order->getUserJobOrders($citys);
            return sys_response(0,'',['list'=>$list]);
        }else{
            return sys_response(4000001);
        }
    }
}