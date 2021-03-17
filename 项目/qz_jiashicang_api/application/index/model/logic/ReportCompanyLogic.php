<?php
// +----------------------------------------------------------------------
// | ReportCompanyLogic 大报备相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\index\model\adb\SaleReportZxCompany;

class ReportCompanyLogic {

    protected $reportZxModel;

    public function __construct(){
        $this->reportZxModel = new SaleReportZxCompany();
    }

    // 大报备汇款金额统计
    public function getCityReportAmountTotal($date_begin, $date_end, $city_ids = []){
        $list = $this->reportZxModel->getCityReportAmountTotal($date_begin, $date_end, $city_ids);
        return $list;
    }

    // 大报备汇款金额统计
    public function getCompanyReportAmountTotal($date_begin, $date_end, $company_ids = []){
        $list = $this->reportZxModel->getCompanyReportAmountTotal($date_begin, $date_end, $company_ids);
        return array_column($list, null, "company_id");
    }

}