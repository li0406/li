<?php
// +----------------------------------------------------------------------
// | ReportCustomerMaintenance  客户维护列表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class ReportCustomerMaintenance extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $table = 'qz_report_customer_maintenance';

    /**
     * 获取客户维护列表
     *
     * @param $startField
     * @param $endField
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getListWithContract($startField, $endField)
    {
        return self::alias('a')
            ->leftJoin('')
            ->select();
    }

    /**
     * 获取客户维护总数量
     *
     * @param $startField
     * @param $endField
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getCountWithContract()
    {

    }
}