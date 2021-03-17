<?php
/**
 * 装修订单
 */
namespace Common\Model\Logic;


use Common\Model\Db\OrdersModel;

class OrdersLogicModel
{
    //获取报价页面订单
    public function getBaojiaOrder($cityid,$cityname){
        // 获取当前城市20条订单记录
        $ordersModel = new OrdersModel();
        $where['on'] = ['eq',4];
        $where['type_fw'] = ['in',[1,2]];
        $where['cs'] = ['eq',$cityid];
        $where['time_real'] = ['egt',strtotime("-3 month",time())];
        $field="name,xiaoqu,mianji";
        $limit = 20;
        $list = S('Cache:Sub:BaoJiaList:'.$cityid);
        if(!$list){
            $list = $ordersModel->getBaojiaOrdersList($field,$where,$limit);
            //如果当前城市订单不足 获取全国
            if(count($list) < 20){
                unset($where['cs']);
                $other_list  = $ordersModel->getBaojiaOrdersList($field,$where,$limit-count($list));
                $list = array_merge($list,$other_list);
            }
            // 订单城市的最低半包价格
            $cityprice = D("Orders")->getCityPrice($cityid);
            $price = round( ($cityprice["half_price_min"]+$cityprice["half_price_max"])/2);
            if (empty($price)) {
                $price = 300;
            }

            // 数据整理
            foreach($list as $key=>$val){
                $list[$key]['cname'] = $cityname;
                $list[$key]['current_time'] = date('Y-m-d');
                $list[$key]['xiaoqu'] = isset($val['xiaoqu'])? mbstr($val['xiaoqu'],0,10):'--';
                $list[$key]['name'] =mbstr($val['name'],0,4);
                if(is_numeric($val['mianji'])&&!empty($val['mianji'])){
                    $list[$key]['baojia'] = number_format($price*1.2*$val["mianji"]/10000,1).'万' ;
                    $list[$key]['mianji'] = $val['mianji'].'㎡';
                }else{
                    $list[$key]['baojia'] = "面议";
                    $list[$key]['mianji'] = empty($val['mianji'])?"--":mbstr($val['mianji'],0,6);
                }
            }

            S('Cache:Sub:BaoJiaList:'.$cityid,$list,86400);
        }

        return $list;
    }


}