<?php
// +----------------------------------------------------------------------
// | Phonelocation
// +----------------------------------------------------------------------

namespace app\common\model\db;

use think\db\Where;
use think\Model;

class Phonelocation extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_phonelocation';

    /**
     * 根据订单ID号获取订单电话归属地
     *
     * @param $orderIds
     * @return array|bool|\PDOStatement|string|\think\Collection
     */
    public static function getOrderTelLocationByOrderIds($orderIds)
    {
        if (empty($orderIds)) {
            return [];
        }

        $map['s.orderid'] = ['IN', $orderIds];
        return self::alias('p')
            ->field('s.orderid AS id, p.c AS cname')
            ->join(['safe_order_tel8'=>'s'], 'p.phone = LEFT(s.tel8, 7)')
            ->where(new Where($map))
            ->group('s.orderid')
            ->select();
    }
}