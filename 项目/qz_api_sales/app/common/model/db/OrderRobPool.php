<?php
namespace app\common\model\db;

use think\db\Where;
use think\Model;
/**
 *
 */
class OrderRobPool extends Model
{
    protected $autoWriteTimestamp = false;

    public function getOrderPoolInfo($where = [], $field = '*')
    {
        return $this->field($field)->where(new Where($where))->find();
    }
}