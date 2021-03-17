<?php

namespace User\Controller;

use Common\Model\Logic\RobOrdersLogicModel;
use User\Common\Controller\CompanyBaseController;

class RoborderController extends CompanyBaseController
{
    /**
     * 抢单
     */
    public function index()
    {
        $robLogic = new RobOrdersLogicModel();
        $list = $robLogic->getRobOrderList($this->baseInfo, I('get.'));

        $list["user"] = $this->baseInfo;
        //预算
        $yusuan = D("Jiage")->getJiage();
        //装修方式
        $fangshi = D("Fangshi")->getfs();
        $this->assign('info', $list);
        $this->assign('yusuan', $yusuan);
        $this->assign('fangshi', $fangshi);
        $this->assign("nav",12);
        $this->display();
    }

    /**
     * 抢单订单详细信息
     *
     * @return void
     */
    public function detail()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(['code' => 404, 'errmsg' => '未查询到该订单']);
        }
        $robLogic = new RobOrdersLogicModel();
        $robinfo = $robLogic->getRobOrderInfo($id);
        if (!$robinfo) {
            $this->ajaxReturn(['code' => 404, 'errmsg' => '该订单不可抢']);
        }
        //获取订单信息
        $order = D("Orders")->getOrderInfoById($id);
        if ($order) {
            array_walk($order, function (&$val, $key) {
                if ($val && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                    $val = date('Y-m-d H:i:s', $val);
                } else {
                    $val = $val ?: '';
                }
            });
            $this->ajaxReturn(['code' => 200, 'info' => $order]);
        } else {
            $this->ajaxReturn(['code' => 404, 'errmsg' => '未查询到该订单']);
        }
    }

    /**
     * 抢单操作
     *
     * @return void
     */
    public function getRobOrder()
    {
        $id = I('post.id');
        //todo 暂时写死token
        $token = md5(session("u_userInfo.id") . 'roborder' . date('Y-m-d H:i'));
        $post = [
            'order_id' => $id,
            'user_id' => session("u_userInfo.id"),
            'user' => session("u_userInfo.user"),
            'cs' => session("u_userInfo.cs"),
            'token' => $token
        ];
        $info = curl(C('QZ_YUMING_SCHEME').'://168new.qizuang.com/roborderapi/roborder', $post);
        $this->ajaxReturn(['code' => $info['code'], 'errmsg' => $info['errmsg']]);
    }

    /**
     * 不抢操作
     */
    public function setNoRobOrder(){
        $id = I('post.id');
        //验证数据
        $rob = new RobOrdersLogicModel();
        $info = $rob->checkNoRobOrder($id);
        if($info['code'] != 200){
            $this->ajaxReturn($info);
        }
        //添加数据
        $status = $rob->saveUnOrder($id,session("u_userInfo.id"));
        if($status){
            $this->ajaxReturn(['code' => 200, 'errmsg' => '温馨提示：该订单已从订单池中删除']);
        }else{
            $this->ajaxReturn(['code' => 400, 'errmsg' => '操作失败!']);
        }
    }
}