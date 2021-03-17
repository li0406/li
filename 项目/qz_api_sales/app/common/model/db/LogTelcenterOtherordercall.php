<?php

namespace app\common\model\db;

use think\db\Query;
use think\db\Where;
use think\Model;

class LogTelcenterOtherordercall extends Model {

    protected $autoWriteTimestamp = false;

    protected $table = 'qz_log_telcenter_otherordercall';

    /**
     * 获取电话记录数量
     * @param $ids
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderTelRecordCount($ids)
    {
        $map[] = [
            "orderid","in",$ids
        ];
        return $this->where($map)->field("orderid,count(orderid) as count")->group("orderid")->select();
    }
}