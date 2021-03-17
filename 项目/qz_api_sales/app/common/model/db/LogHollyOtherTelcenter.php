<?php

namespace app\common\model\db;

use think\db\Query;
use think\db\Where;
use think\Model;

class LogHollyOtherTelcenter extends Model {

    protected $autoWriteTimestamp = false;

    protected $table = 'qz_log_holly_other_telcenter';

    public function getOrderTelRecordCount($order_id)
    {
        $map[] = [
            "order_id","in",$order_id
        ];
        return $this->where($map)->field("order_id as orderid,count(order_id) as count")->group("order_id")->select();

    }
}