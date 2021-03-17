<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Logic;


use Common\Enums\OrderSource;

class YxbOrdersLogicModel
{
    public function setYxbOrder($order, $companys)
    {
        if (empty($companys)) {
            return true;
        }
        $companys_id = [];
        foreach ($companys as $company) {
            $companys_id[] = $company['compnay_id'];
        }

        $erp_orders = D("Home/Db/YxbOrders")->selectYxbOrder($order['id']);

        $has_handle_companys_id = [];
        foreach ($erp_orders as $v) {
            $has_handle_companys_id[] = $v['company_id'];
        }
        $todo_company = array_diff($companys_id, $has_handle_companys_id);

        $conpanys_array = D("Home/Db/Company")->selectCompany($todo_company);
        $erp_orders_insert = [];
        $erp_orders_manage_insert = [];
		$reception_insert = [];
        if (empty($conpanys_array)) {
            return true;

        }
        foreach ($conpanys_array as $k => $vv) {
            $order_no = getOrderNo();
            $erp_orders_insert[$k]['order_no'] = $order_no;
            $erp_orders_insert[$k]['company_id'] = $vv['id'];
            $erp_orders_insert[$k]['source'] = (empty(OrderSource::getOrderSource($vv['id'])) ? 0 :OrderSource::getOrderSource($vv['id']));
            $erp_orders_insert[$k]['consumer_name'] = $order['name'] . $order['sex'];
            $erp_orders_insert[$k]['consumer_tel'] = $order['tel8'];
            $erp_orders_insert[$k]['house_type'] = $order['huxing'];
            $erp_orders_insert[$k]['build_address'] = $order['cname'].$order['qz_area'].$order['xiaoqu'];
            $erp_orders_insert[$k]['qz_order'] = $order['id'];
            $erp_orders_insert[$k]['add_time'] = time();
            $erp_orders_insert[$k]['publish_time'] = strtotime($order['time_real']);
			$erp_orders_insert[$k]['house_area'] = empty($order['mianji'])?'':$order['mianji'];
			//预留
            if (!empty($order['cs'])) {
                $erp_orders_insert[$k]['city'] = $order['cs'];
            }
            if (!empty($order['area'])) {
                $erp_orders_insert[$k]['area'] = $order['qx'];
            }
            if(in_array($order['type_fw'],[1,2])){
				$erp_orders_insert[$k]['type_fw'] = $order['type_fw'];
			}
            if(!empty($order['xiaoqu'])){
                $erp_orders_insert[$k]['xiaoqu'] = $order['xiaoqu'];
            }
            $erp_orders_manage_insert[$k]['order_no'] = $order_no;
            $erp_orders_manage_insert[$k]['company_id'] = $vv['id'];
            //接单日志
			$reception_insert[$k]['add_time'] =  time();
			$reception_insert[$k]['order_no'] =  $order_no;
			$reception_insert[$k]['contact_name'] =  $vv['qc'];
			$reception_insert[$k]['company_id'] =  $vv['id'];

		}
        D('Home/Db/YxbOrders')->addOrders($erp_orders_insert);
        $orderManage =  D('Home/Db/YxbOrdersManage');
		$orderManage->addOrders($erp_orders_manage_insert);
		$orderManage->addReceptionLog($reception_insert);
		return true;
    }

}