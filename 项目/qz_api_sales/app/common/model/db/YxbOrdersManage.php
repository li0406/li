<?php
// +----------------------------------------------------------------------
// | YxbOrdersManage
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class YxbOrdersManage extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_yxb_orders_manage';

    public function addOrders($data)
    {
        return $this->saveAll($data);
    }
}