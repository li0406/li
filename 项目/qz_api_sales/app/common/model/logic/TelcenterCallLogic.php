<?php
// +----------------------------------------------------------------------
// | TelcenterCallLogic 电话记录
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\LogHollyOtherTelcenter;
use app\common\model\db\LogTelcenterOtherordercall;

class TelcenterCallLogic
{
    public function getQiandanCall($order_id){
        if(empty($order_id)){
            return [];
        }
        $logDb = new LogTelcenterOtherordercall();
        return $logDb->getOrderTelRecordCount($order_id)->toArray();
    }

    public function getQiandanHollyCall($order_id){
        $logDb = new LogHollyOtherTelcenter();
        return $logDb->getOrderTelRecordCount($order_id)->toArray();
    }
}