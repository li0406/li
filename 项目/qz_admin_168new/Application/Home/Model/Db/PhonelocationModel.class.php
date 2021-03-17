<?php
// +----------------------------------------------------------------------
// | PhonelocationModel  手机号码归属地
// +----------------------------------------------------------------------
namespace Home\Model\Db;

Use Think\Model;

class PhonelocationModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * [getOrderTelLocationByOrderIds 根据订单ID号获取订单电话归属地]
     * @param  [type] $orderIds [订单ID号数组]
     * @return [type]           [description]
     */
    public function getOrderTelLocationByOrderIds($orderIds){
        if (empty($orderIds)) {
            return false;
        }
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        $map['s.orderid'] = array('IN', $orderIds);
        $result = M('phonelocation')->alias('p')
                                    ->field('s.orderid AS id, p.c AS cname')
                                    ->join('safe_order_tel8 AS s ON p.phone = LEFT(s.tel8, 7)')
                                    ->where($map)
                                    ->group('s.orderid')
                                    ->select();
        return $result;
    }

    public function getOne($map, $field = '*')
    {
        return M('phonelocation')->field($field)->where($map)->find();
    }

    public function getList($map, $offset = null, $listRows = null, $field = '*')
    {
        return M('phonelocation')->field($field)->where($map)->limit($offset, $listRows)->select();
    }
}