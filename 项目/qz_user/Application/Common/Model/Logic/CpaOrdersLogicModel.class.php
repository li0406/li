<?php
// +----------------------------------------------------------------------
// | CpaOrdersLogicModel  CPA装修订单逻辑层
// +----------------------------------------------------------------------
namespace Common\Model\Logic;

use Common\Enums\LiangFangInfo;
use Common\Enums\OrderStatus;
use Think\Exception;

class CpaOrdersLogicModel
{
    /**
     * 获取家具公司的订单分配信息
     * @return [type] [description]
     */
    public function getCpaOrder($orderId, $comId)
    {
        $map['order_id'] = ['EQ', $orderId];
        $map['company_id'] = ['EQ', $comId];
        return D('Common/Db/CpaOrdersInfo')->getOne($map);
    }

    /**
     * 修改家具公司的订单阅读状态
     * @return [type] [description]
     */
    public function setRead($orderId, $comId)
    {
        $map['order_id'] = ['EQ', $orderId];
        $map['company_id'] = ['EQ', $comId];
        return D('Common/Db/CpaOrdersInfo')->changeRead($map);
    }

    /**
     * 根据订单编号获取订单信息
     * @return [type] [description]
     */
    public function getOrderInfoById($id)
    {
        $map['a.id'] = ['EQ', $id];
        return D('Common/Db/CpaOrders')->getFullOne($map);
    }

    /**
     * 根据装修公司编号查询分配的订单信息数量
     * @param  [type] $comid [description]
     * @param  [type] $text  [description]
     * @return [type]        [description]
     */
    public function getCpaOrderCountByComid($comid, $text)
    {
        $map = array(
            't.company_id' => array('EQ', $comid),
        );
        import('Library.Org.Util.App');
        $app = new \App();

        if (!empty($text)) {
            $map['_complex'] = array(
                'b.xiaoqu' => array('LIKE', '%'.$text.'%'),
                'b.tel_encrypt' => array('EQ', $app->order_tel_encrypt($text)),
                'b.name' => array('LIKE', '%'.$text.'%'),
                '_logic' => 'OR'
            );
        }
        return D('Common/Db/CpaOrdersInfo')->getCount($map);
    }

    /**
     * 根据装修公司编号查询分配的订单信息
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getCpaOrderListByComid($comid, $text, $page, $pageSize)
    {
        $map = array(
            't.company_id' => array('EQ', $comid),
        );
        if (!empty($text)) {
            import('Library.Org.Util.App');
            $app = new \App();
            $map['_complex'] = array(
                'b.xiaoqu' => array('LIKE', '%'.$text.'%'),
                'b.tel_encrypt' => array('EQ', $app->order_tel_encrypt($text)),
                'b.name' => array('LIKE', '%'.$text.'%'),
                '_logic' => 'OR'
            );
        }
        return D('Common/Db/CpaOrdersInfo')->getList($map,$page,$pageSize);
    }
}