<?php
// +----------------------------------------------------------------------
// | Salers 销售管理控制器
// +----------------------------------------------------------------------
namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\OrdersLogic;
use app\common\model\logic\CompanyLogic;
use app\common\model\logic\AdminuserLogic;

use app\common\enum\BaseStatusCodeEnum;

class Saler extends Controller
{
    /**
     * 获取用户签单
     *
     * @param Request $request
     * @return array
     */
    public function signlist(Request $request, OrdersLogic $ordersLogic)
    {
        $p = $request->get('p', '1', 'intval');
        $psize = $request->get('psize', '10', 'intval');
        $get = $request->get();
        return $list = $ordersLogic->getQiandanList($get, $p, $psize);
    }

    /**
     * 签单订单具体信息
     *
     * @param Request $request
     * @return array
     */
    public function signinfo(Request $request, OrdersLogic $ordersLogic)
    {
        $get = $this->request->get();
        if (($error_msg = $this->validate($get, 'app\index\validate\Qiandan.qdinfo')) !== true) {
            return sys_response(4000600, $error_msg, []);
        }
        $order_id = $request->get('id', '');
        if ($order_id) {
            return sys_response(0, '',
                $ordersLogic->orderInfo($order_id, ['companys'])
            );
        } else {
            return sys_response(9000001);
        }
    }

    /**
     * 销售城市管辖
     *
     * @param Request $request
     * @return array
     */
    public function citys(Request $request, AdminuserLogic $adminuserLogic)
    {
        $saler = $request->get('saler', '');
        $p = $request->get('p', '1', 'intval');
        $psize = $request->get('psize', '10', 'intval');

        // 获取查询条件
        $map = $adminuserLogic->analysisCityMap($saler,true);
        if ($map === false) {
            return sys_response(4000001);
        }

        // 获取API列表
        $count = $adminuserLogic->getSalerCount($map);
        if ($count) {
            $citys = $adminuserLogic->getSalerWithCitys($map, $p, $psize);
        }
        return sys_response(0, '', [
            'list' => !empty($citys) ? $citys : [],
            'page' => sys_paginate($count, $psize, $p)
        ]);
    }

    /**
     * 销售管辖城市信息
     *
     * @param Request $request
     * @return array
     */
    public function cityinfo(Request $request, AdminuserLogic $adminuserLogic)
    {
        $id = $request->get('id','','intval');
        if (empty($id)) {
            return sys_response(4000002);
        }
        $citys = $adminuserLogic->getSalerWithCitys(['u.id' => $id], 1, 1);
        return sys_response(0, '',
            !empty($citys[0]) ? $citys[0] : null
        );
    }
    /**
     * 保存销售管辖城市
     *
     * @param Request $request
     * @return array
     */
    public function savecitys(Request $request, AdminuserLogic $adminuserLogic)
    {
        $param = $request->post();
        if (empty($param['id'])) {
            return sys_response(4000002);
        }
        $flag = $adminuserLogic->saveSalerCitys($param['id'], $param['citys']);
        if ($flag) {
            return sys_response(0, '');
        } else {
            return sys_response(5000002);
        }
    }

    /**
     * 清除销售城市管辖
     *
     * @param Request $request
     * @return array
     */
    public function clearcity(Request $request, AdminuserLogic $adminuserLogic)
    {
        $id = $request->post('id','','intval');
        if (empty($id)) {
            return sys_response(4000002);
        }
        $flag = $adminuserLogic->clearSalerCitys($id);
        if ($flag) {
            return sys_response(0, '');
        } else {
            return sys_response(5000002);
        }
    }

    /**
     * 获取销售人员列表
     *
     * @param Request $request
     * @return array
     */
    public function user_list_sale(Request $request,AdminuserLogic $adminuserLogic)
    {
        if (empty($request->user_name)) {
            return sys_response(4000002);
        }
        $info = $adminuserLogic->getSaleList($request->user_name);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }
}