<?php
// +----------------------------------------------------------------------
// | YxbOrderSource
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class YxbOrderSource extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_yxb_order_source';

    public function getOrderSource($data)
    {
        $where[] = ['company_id', 'in', $data];
        $where[] = ['default_rule', '=', 1];
        return $this->where($where)->field('id,company_id')->select();
    }
}