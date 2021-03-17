<?php
namespace app\common\model\logic;

use app\index\enum\InvoiceEnum;
use app\common\model\db\InvoiceLog;

class InvoiceLogLogic 
{
    public function getLogList($invoice_id){
        $invoiceLog = new InvoiceLog();
        $type = InvoiceEnum::getCheckStatus();
        $list = $invoiceLog->getCheckAllList($invoice_id, $type);
        $list = $list->toArray();

        $checked = [];
        foreach ($list as $k => $v) {
            $checked[$k]['time'] = date('Y-m-d H:i:s', $v['created_at']);
            $checked[$k]['status'] = $v['action_type'];
            $checked[$k]['action_type'] = InvoiceEnum::getItem('status', $v['action_type']);
            $checked[$k]['op_name'] = $v['operator_name'];
            $checked[$k]['remark'] = $v['remark'];
            $checked[$k]['created_at'] = $v['created_at'];
        }
        
        return $checked;
    }
}