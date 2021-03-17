<?php
namespace app\common\model\db;

use think\Db;
use think\db\Query;
use think\Model;

class InvoiceLog extends Model
{
    protected $table = 'qz_sale_report_invoice_log';

    public function getCheckAllList($invoice_id, $type){
        $map = [
            ['invoice_id', '=', $invoice_id],
            ['action_type', 'IN', $type]
        ];
        return $this->where($map)->order('id DESC')->select();
    }
}