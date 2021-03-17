<?php

namespace Home\Controller;

use Home\Model\Logic\CompanyLogicModel;
use Home\Model\Logic\OrdersGiveCompanyLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\RobOrdersLogicModel;
use Home\Model\OrdersModel;
use Think\Controller;

class RoborderapiController extends Controller
{

    public function sendcompany()
    {
        if (IS_POST) {
            $data = I('post.');
            $this->ajaxReturn(['code' => 404, 'errmsg' => REQUEST_METHOD,'dd'=>$data['test']]);
        }
    }

    /**
     * 抢单操作
     */
    public function roborder()
    {
        if (IS_POST) {
            //todo 验证是否允许请求,先固定写死
            $post = I('post.');
            //验证token
            if(C('APP_ENV') != 'dev'){
                if ($post['token'] != md5($post['user_id'] . 'roborder' . date('Y-m-d H:i'))) {
                    $this->ajaxReturn(['code' => 404, 'errmsg' => '验证失败! 请刷新后再试!','dd' => '1,']);
                }
            }
            //验证当前发送的数据
            $robLogic = new RobOrdersLogicModel();
            $info = $robLogic->checkUserByOrder($post);
            if ($info['code'] != 200) {
                $this->ajaxReturn(['code' => $info['code'], 'errmsg' => $info['errmsg'] ,'dd' => '2,'.$info['dd']]);
            }
            //添加抢单成功信息
            $info = $robLogic->addRobOrder($post);
            if ($info['code'] != 200) {
                $this->ajaxReturn(['code' => $info['code'], 'errmsg' => $info['errmsg'] ,'dd' => '3,'.$info['dd']]);
            }
            $this->ajaxReturn(['code' => 200,'errmsg'=>'success']);
        }
    }

    /**
     * 直接赠送
     */
    public function giveCompanyOrder(){
        if (IS_POST) {
            //todo 验证是否允许请求,先固定写死
            $post = I('post.');
            //验证token(线上环境需要验证)
            if (C('APP_ENV') == 'prod') {
                if ($post['token'] != md5('changeOrder' . date('Y-m-d H:i'))) {
                    $this->ajaxReturn(['code' => 404, 'errmsg' => '验证失败! 请刷新后再试!', 'dd' => '1,']);
                }
            }
            $order_ids = $post['id'];
            $errmsg = '';
            $order_ids_list = explode(',',$order_ids);
            if (count($order_ids_list) > 0) {
                $giveLogic = new OrdersGiveCompanyLogicModel();
                $robLogic = new RobOrdersLogicModel();
                $order = new OrdersModel();
                $company = new CompanyLogicModel();
                foreach ($order_ids_list as $k => $v) {
                    //验证当前发送的数据
                    $info = $robLogic->checkGiveOrder($v);
                    if ($info['code'] == 404) {
                        $errmsg .= '错误订单:'.$v.',错误信息:'.$info['errmsg'].'\n';
                        continue;
                    }
                    //获取订单信息
                    $info = $order->findOrderInfo($v);

                    //获取可以分配的公司
//                    $list = $this->getCompanyDetailsList(array($info['cs']), 2);
                    $list = $company->getGiveCompanyListByOrder($info);
                    if (count($list) == 0) {
                        $errmsg .= '错误订单:'.$v.',错误信息:未查询到可分配公司\n';
                        $giveLogic->changeRobOrder($v);
                        continue;
                    }
                    $giveStatus = $giveLogic->giveCompanyApi($info, $list);
                    if($giveStatus['code'] == 404){
                        $errmsg .= '错误订单:'.$v.',错误信息:'.$giveStatus['errmsg'].'\n';
                        continue;
                    }else{
                        $errmsg = $giveStatus['errmsg'];
                    }
                    sleep(1);
                }
            }
            $this->ajaxReturn(['errmsg' => $errmsg, 'code' => 200]);
        }
    }

    /**
     * 获取装修公司详细信息
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    private function getCompanyDetailsList($cs,$on,$companys = []){
        $companys = D("User")->getCompanyDetailsList($cs,$on,$companys);
        //给公司数据添加设置接单区域
        $comLogic = new CompanyLogicModel();
        $companys = $comLogic->setCompanyDeliverArea($companys);
        foreach ($companys as $key => $value) {
            //计算到期时间
            $offset = (strtotime($value["end"]) - strtotime(date("Y-m-d")))/86400+1;

            if ($offset <= 15 && empty($value["start_time"])) {
                $companys[$key]["end_time"] = $offset;
            }

            //合同开始时间如果大于本月1号显示新
            if ($value["start"] >= date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")))) {
                $companys[$key]["newMark"] = 1;
            }
            $ids[] = $value["id"];
        }

        //获取装修公司暂停信息
        if (count($ids) > 0) {
            $result = D("UserVip")->getParseContractList($ids);
            foreach ($result as $key => $value) {
                //计算到期时间
                $offset = (strtotime(date("Y-m-d")) - strtotime($value["end_time"]))/86400+1;
                if ($offset <= 15 && empty($value["start_time"])) {
                    $parseList[$value["company_id"]]["parseMark"] = 1;
                }
            }

            foreach ($companys as $key => $value) {
                if (array_key_exists($value["id"],$parseList)) {
                    $companys[$key]["parseMark"] = $parseList[$value["id"]];
                }
            }
        }
        return $companys;
    }
}
