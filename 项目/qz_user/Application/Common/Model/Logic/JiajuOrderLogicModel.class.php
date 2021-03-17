<?php
// +----------------------------------------------------------------------
// | CpaOrdersLogicModel  CPA装修订单逻辑层
// +----------------------------------------------------------------------
namespace Common\Model\Logic;

use Common\Enums\LiangFangInfo;
use Common\Enums\OrderStatus;
use Think\Exception;

class JiajuOrderLogicModel
{
    /**
     * 获取家具公司的订单分配信息
     * @return [type] [description]
     */
    public function getJiajuOrder($orderId, $comId)
    {
        $map['order_id'] = ['EQ', $orderId];
        $map['company_id'] = ['EQ', $comId];
        return D('Common/Db/JiajuOrderInfo')->getOne($map);
    }

    /**
     * 修改家具公司的订单阅读状态
     * @return [type] [description]
     */
    public function setRead($orderId, $comId)
    {
        $map['order_id'] = ['EQ', $orderId];
        $map['company_id'] = ['EQ', $comId];
        return D('Common/Db/JiajuOrderInfo')->changeRead($map);
    }

    /**
     * 根据订单编号获取订单信息
     * @return [type] [description]
     */
    public function getOrderInfoById($id)
    {
        $map['a.id'] = ['EQ', $id];
        return D('Common/Db/JiajuOrder')->getFullOne($map);
    }

    /**
     * 根据装修公司编号查询分配的订单信息数量
     * @param  [type] $comid [description]
     * @param  [type] $text  [description]
     * @return [type]        [description]
     */
    public function getJiajuOrderCountByComid($comid, $text)
    {
        $map = array(
            't.company_id' => array('EQ', $comid),
        );
        if (!empty($text)) {
            $map['_complex'] = array(
                'b.xiaoqu' => array('LIKE', '%'.$text.'%'),
                'sa.tel8' => array('EQ', $text),
                'b.name' => array('LIKE', '%'.$text.'%'),
                '_logic' => 'OR'
            );
        }
        return D('Common/Db/JiajuOrderInfo')->getCount($map);
    }

    /**
     * 根据装修公司编号查询分配的订单信息
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getJiajuOrderListByComid($comid, $text, $page, $pageSize)
    {
        $map = array(
            't.company_id' => array('EQ', $comid),
        );
        if (!empty($text)) {
            $map['_complex'] = array(
                'b.xiaoqu' => array('LIKE', '%'.$text.'%'),
                'sa.tel8' => array('EQ', $text),
                'b.name' => array('LIKE', '%'.$text.'%'),
                '_logic' => 'OR'
            );
        }
        return D('Common/Db/JiajuOrderInfo')->getList($map,$page,$pageSize);
    }

    /**
     * 订单详细信息获取
     * @param $orderId
     * @param $comId
     * @return mixed
     */
    public function getJiajuOrderWithOther($orderId, $comId)
    {
        $map['order_id'] = ['EQ', $orderId];
        $map['company_id'] = ['EQ', $comId];
        return D('Common/Db/JiajuOrder')->getOneWithOther($map);
    }

    /**
     * 已分配公司的订单是否都已读
     * @param  [int] $orderid 订单号
     * @return [bool]
     */
    public function getJiajuOrderFenpeiAllIsRead($orderid)
    {
        $map['order_id'] = array('EQ', $orderid);
        $list = D('Common/Db/JiajuOrderInfo')->where($map)->select();
        $flag = true;
        foreach ($list as $key => $value) {
            if (0 == $value['isread']) {
                $flag = false;
                break;
            }
        }
        return $flag;
    }

    /**
     * 编辑家具订单信息
     */
    public function editJiajuOrder($id,$data)
    {
        return D('Common/Db/JiajuOrder')->editJiajuOrder($id,$data);
    }
}