<?php
/**
 * 抢单池操作
 */
namespace Common\Model\Logic;

use Common\Model\Db\OrderRobPoolModel;
use Common\Model\Db\UserUnrobModel;

class RobOrdersLogicModel
{
    /**
     * 获取抢单列表
     * @param null $user
     * @param $get
     * @return array
     */
    public function getRobOrderList($user = null, $get)
    {
        if (empty($user['deliver_area'])) {
            return [];
        }
        $robDb = new OrderRobPoolModel();
        $where['qx'] = ['in',$user['deliver_area']];
        $where['company'] = $user['id'];

//        if ($user['mianji']) {
//            $where['mianji'] = ['egt', $user['mianji']];
//        }
//
//        if ($user['lxs']) {
//            if (in_array($user['lxs'], [1, 2])) {
//                $where['lxs'] = $user['lxs'] == 1 ? $user['lxs'] : ['in', [2, 3]];
//            }
//        }

        if ($get['yusuan']) {
            $where['yusuan'] = $get['yusuan'];
        }

        if ($user['lx'] && in_array($user['lx'], [1, 2])) {
            $where['lx'] = $user['lx'];
            if (!empty($get['lx']) && $get['lx'] != $user['lx']) {
                return ['page' => '', 'list' => []];
            }
        } else {
            if (!empty($get['lx'])) {
                $where['lx'] = $get['lx'];
            }
        }

        if ($get['fangshi']) {
            $where['fangshi'] = $get['fangshi'];
        }
        if ($get['fangshi']) {
            $where['fangshi'] = $get['fangshi'];
        }
        //过滤当前公司在抢单池中已经处理过(抢单/赠单)的订单
        $passOrder = $robDb->getPassOrderRobList($where);
        //过滤勾选了不抢的订单
        $robPass = $this->getNoRobOrder($user['id']);
        if (count($passOrder) > 0) {
            $where['passOrder'] = array_column($passOrder, 'id');
        }
        if (count($robPass) > 0) {
            if (!empty($where['passOrder'])) {
                $where['passOrder'] = array_merge($where['passOrder'], array_column($robPass, 'order_id'));
            } else {
                $where['passOrder'] = array_column($robPass, 'order_id');
            }
        }
        $where['order_status'] = isset($get['type']) ? 2 : 1;

        //强制数字整数
        $pageIndex = $get['p'] <= 0 ? 1 : intval($get['p']);
        $pageCount = 10;
        //根据城市获取抢单池数据
        $count = $robDb->getOrderRobCount($where,$user['id']);
        if ($count) {
            import('Library.Org.Page.Page');
            $robList = $robDb->getOrderRobList($where, $user['id'], ($pageIndex - 1) * $pageCount, $pageCount);
            $config = array("prev", "first", "last", "next");
            $page = new \Page($pageIndex, $pageCount, $count, $config);
            $pageTmp = $page->show();
            return ['page' => $pageTmp, 'list' => $robList];
        } else {
            return ['page' => '', 'list' => []];
        }
    }

    public function getMobileRobOrderList($user = null, $pageIndex = 1)
    {
        if (empty($user['deliver_area'])) {
            return [];
        }
        $robDb = new OrderRobPoolModel();
        $where['order_status'] = $user['robtype'];
        $where['qx'] = ['in',$user['deliver_area']];
        $where['company'] = $user['id'];

//        if ($user['mianji']) {
//            $where['mianji'] = ['egt', $user['mianji']];
//        }
//
//        if ($user['lxs']) {
//            if (in_array($user['lxs'], [1, 2])) {
//                $where['lxs'] = $user['lxs'] == 1 ? $user['lxs'] : ['in', [2, 3]];
//            }
//        }

        if ($user['lx']) {
            if (in_array($user['lx'], [1, 2])) {
                $where['lx'] = $user['lx'];
            }
        }

        //过滤当前公司在抢单池中已经处理过(抢单/赠单)的订单
        $passOrder = $robDb->getPassOrderRobList($where);
        if (count($passOrder) > 0) {
            $where['passOrder'] = array_column($passOrder, 'id');
        }
        //过滤勾选了不抢的订单
        $robPass = $this->getNoRobOrder($user['id']);
        if (count($robPass) > 0) {
            if (!empty($where['passOrder'])) {
                $where['passOrder'] = array_merge($where['passOrder'], array_column($robPass, 'order_id'));
            } else {
                $where['passOrder'] = array_column($robPass, 'order_id');
            }
        }
        //强制数字整数
        $pageCount = 10;
        $robList = $robDb->getOrderRobList($where, $user['id'], ($pageIndex - 1) * $pageCount, $pageCount);
        return $robList;
    }

    /**
     * 获取抢单信息
     *
     * @param $order_id
     * @return mixed
     */
    public function getRobOrderInfo($order_id)
    {
        $robDb = new OrderRobPoolModel();
        return $robDb->getRobInfo($order_id);
    }

    /**
     * 获取不抢的订单列表
     * @param $company_id
     * @return mixed
     */
    public function getNoRobOrder($company_id){
        $map = [
            'u.company_id' => ['eq', $company_id],
            'p.is_rob' => ['eq', 1]
        ];
        $unrob = new UserUnrobModel();
        return $unrob->getNoRobOrderList($map,'u.order_id');
    }

    /**
     * 验证不抢的订单
     * @param $order_id
     * @return array
     */
    public function checkNoRobOrder($order_id){
        //验证是否添加过
        $unrob = new UserUnrobModel();
        $info = $unrob->getNoRobOrderInfo(['u.order_id' => ['eq', $order_id]]);
        if (!empty($info)) {
            return ['code' => 400, 'errmsg' => '您已操作过不抢!'];
        }
        //验证是否还能抢
        $rob = new OrderRobPoolModel();
        $info = $rob->getRobInfo($order_id);
        if(!empty($info)){
            if($info['is_rob'] == 2 || ($info['allot_num'] == $info['rob_num'])){
                return ['code' => 400, 'errmsg' => '您已操作过不抢!'];
            }
        }else{
            return ['code' => 400, 'errmsg' => '该订单已处理!'];
        }
        return ['code' => 200];
    }

    public function saveUnOrder($order_id, $company_id)
    {
        $save = [
            'order_id' => $order_id,
            'company_id' => $company_id,
            'add_time' => time(),
        ];
        $unrob = new UserUnrobModel();
        return $unrob->saveUnRobOrder($save);
    }
}
