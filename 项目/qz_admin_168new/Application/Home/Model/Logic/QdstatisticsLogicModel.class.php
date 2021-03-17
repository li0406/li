<?php
// +----------------------------------------------------------------------
// | QdstatisticsLogicModel   渠道分单率统计逻辑层
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

use Home\Model\Db\YyOrderInfoModel;

class QdstatisticsLogicModel
{
    public function rateList($dept, $yearStart, $yearEnd,$is_char = false)
    {
        //统计发单量
        $YyOrderInfoModel = new YyOrderInfoModel();
        $issuanceCount = $YyOrderInfoModel->getIssuanceCountByMonthWithDept($dept, $yearStart, $yearEnd, $is_char);
        $divideCount = $YyOrderInfoModel->getDivideCountByMonthWithDept($dept, $yearStart, $yearEnd, $is_char);
        return  ['issuance' => $issuanceCount , 'divide' => $divideCount];
    }
}
