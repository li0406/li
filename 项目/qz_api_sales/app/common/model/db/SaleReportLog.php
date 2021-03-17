<?php
// +----------------------------------------------------------------------
// | SaleReportLog
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class SaleReportLog extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_sale_report_log';

    public function saveLog($data)
    {
        return $this->insert($data);
    }

    public function getCount($map)
    {
        return $this->where(new Where($map))->count('id');
    }

    public function getList($map, $page = null, $pageSize = null, $field = '*', $orderby = 'created_at desc')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return $this->field($field)->where(new Where($map))->limit($offset, $pageSize)->order($orderby)->select();
    }

    public function delList($map)
    {
        return $this->where(new Where($map))->delete();
    }

    public function getHistroyModifyList($map, $limit)
    {
        return $this->where($map)->limit($limit)->order('created_at DESC')->select();
    }
}