<?php
/**
 * 家具商用户账单
 */

namespace Common\Model\Db;

use Think\Model;

class CpaUserExpensesRecordModel extends Model
{
    protected $tableName = 'cpa_user_expenses_record';

    /**
     * 根据筛选条件计算总金额
     */
    public function SumBill($map)
    {
        $count = $this->where($map)->sum('use_amount');
        return $count;
    }

    /**
     * 获取数据列表总和
     */
    public function getDataListCount($map)
    {
        return $this->field('id,user_id,o_id,use_amount,remain_amount,remark,type,create_time')->where($map)->count(1);
    }

    /**
     * 获取记录列表
     */
    public function getDataList($map, $page, $pageSize)
    {
        return $this->field('id,user_id,o_id,use_amount,remain_amount,remark,type,create_time')->where($map)->page($page, $pageSize)->order('create_time desc')->select();
    }
}