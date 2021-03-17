<?php


namespace app\common\model\db;

use think\Model;

class OrderUnremedy extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_order_unremedy';

    public function addUnRemedy($data)
    {
        return $this->saveAll($data);
    }

    public function getPassUnRemedy($where,$field = '*')
    {
        return $this->where($where)->field($field)->select();
    }
}