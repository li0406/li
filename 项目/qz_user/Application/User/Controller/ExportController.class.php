<?php

namespace User\Controller;

use Common\Model\Logic\CompanyTrackLogicModel;
use Common\Model\Logic\CompanyVisitLogicModel;
use User\Common\Controller\CompanyBaseController;

class ExportController extends CompanyBaseController {

    public function ordersExport(){
        $state = I("get.state");
        $isread = I("get.isread");
        $keyword = I("get.keyword");
        $begin = I("get.begin");
        $end = I("get.end");

        $user = session('u_userInfo');
        $orderList = D("Orders")->getOrderListByComid($user["id"], $keyword, $isread, $state, $begin, $end, 0, 0, true);

        $orderTrackList = [];
        $orderVisitList = [];
        if (!empty($orderList)) {
            $orderIds = array_column($orderList, "order");

            $companyTrackLogic = new CompanyTrackLogicModel();
            $orderTrackList = $companyTrackLogic->getCompanyOrderTrack($user["id"], $orderIds);

            $companyVisitLogic = new CompanyVisitLogicModel();
            $orderVisitList = $companyVisitLogic->getCompanyOrderVisit($user["id"], $orderIds);
        }

        import('Library.Org.PHPExcel.PHPExcel',"",".php");
        import('Library.Org.PHPExcel.PHPExcel.Writer.Excel2007',"",".php");

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet(0);

        $activeSheet->getColumnDimension("A")->setWidth(25);
        $activeSheet->getColumnDimension("B")->setWidth(25);
        $activeSheet->getColumnDimension("C")->setWidth(20);
        $activeSheet->getColumnDimension("D")->setWidth(25);
        $activeSheet->getColumnDimension("E")->setWidth(20);
        $activeSheet->getColumnDimension("F")->setWidth(18);
        $activeSheet->getColumnDimension("G")->setWidth(30);
        $activeSheet->getColumnDimension("H")->setWidth(30);
        $activeSheet->getColumnDimension("I")->setWidth(30);
        
        $TYPE_STRING = \PHPExcel_Cell_DataType::TYPE_STRING2;
        $VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
        $VERTICAL_TOP = \PHPExcel_Style_Alignment::VERTICAL_TOP;
        $HORIZONTAL_LEFT = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $activeSheet->getStyle("A:F")->getAlignment()->setHorizontal($HORIZONTAL_CENTER)->setVertical($VERTICAL_CENTER);
        $activeSheet->getStyle("G:I")->getAlignment()->setHorizontal($HORIZONTAL_LEFT)->setVertical($VERTICAL_TOP);
        $activeSheet->getStyle("G1:I1")->getAlignment()->setHorizontal($HORIZONTAL_CENTER)->setVertical($VERTICAL_CENTER);
        $activeSheet->getStyle("G:I")->getAlignment()->setWrapText(true);

        // 表头
        $activeSheet->setCellValue("A1", "订单号")
            ->setCellValue("B1", "发布日期")
            ->setCellValue("C1", "业主姓名")
            ->setCellValue("D1", "所在区域")
            ->setCellValue("E1", "小区名称")
            ->setCellValue("F1", "订单状态")
            ->setCellValue("G1", "装修要求")
            ->setCellValue("H1", "跟单小计")
            ->setCellValue("I1", "齐装回访");
        
        $activeSheet->freezePaneByColumnAndRow(0, 2);
        $activeSheet->getRowDimension(1)->setRowHeight(20);

        // 表头加粗
        $activeSheet->getStyle('A1:I1')->applyFromArray(array(
            'font' => array (
                'name' => 'Microsoft Yahei',
                'bold' => true
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,  
                'startcolor' => array (
                   'argb' => 'E7E6E6E6'
                )
            )
        ));

        $indexXls = 2;
        foreach ($orderList as $key => $item) {
            $order_id = $item["order"];

            // 订单状态
            $orderstatus = "-";
            switch ($item["status"]) {
                case "1":
                    $orderstatus = "已量房";
                    break;
                case "2":
                    $orderstatus = "已到店/见面";
                    break;
                case "3":
                    $orderstatus = "未量房";
                    break;
                case "4":
                    $orderstatus = "已签约";
                    break;
            }

            // 跟单小计
            $track_info = "";
            if (array_key_exists($order_id, $orderTrackList)) {
                foreach ($orderTrackList[$order_id] as $track) {
                    $track_info .= $track["track_date"] . " " . $track["track_step_name"] . "\n";
                    $track_info .= $track["track_content"] . "\n";
                }
            }

            // 订单回访
            $visit_info = "";
            if (array_key_exists($order_id, $orderVisitList)) {
                foreach ($orderVisitList[$order_id] as $visit) {
                    $visit_info .= $visit["visit_date"] . " " . $visit["visit_step_name"] . "\n";
                    $visit_info .= $visit["visit_content_sales"] . "\n";
                }
            }

            $track_info = trim($track_info, "\n");
            $visit_info = trim($visit_info, "\n");

            $activeSheet->setCellValueExplicit('A' . $indexXls, $item["order"], $TYPE_STRING);
            $activeSheet->setCellValueExplicit('B' . $indexXls, date("Y-m-d H:i:s", $item["ordertime"]), $TYPE_STRING);
            $activeSheet->setCellValueExplicit('C' . $indexXls, $item["ordername"] . $item["sex"], $TYPE_STRING);
            $activeSheet->setCellValueExplicit('D' . $indexXls, $item["qx"], $TYPE_STRING);
            $activeSheet->setCellValueExplicit('E' . $indexXls, $item["xiaoqu"], $TYPE_STRING);
            $activeSheet->setCellValueExplicit('F' . $indexXls, $orderstatus, $TYPE_STRING);
            $activeSheet->setCellValueExplicit('G' . $indexXls, $item["text"], $TYPE_STRING);
            $activeSheet->setCellValueExplicit('H' . $indexXls, $track_info, $TYPE_STRING);
            $activeSheet->setCellValueExplicit('I' . $indexXls, $visit_info, $TYPE_STRING);
            $activeSheet->getRowDimension($indexXls)->setRowHeight(-1);
            $indexXls++;
        }

        $styleArray = array(  
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框  
                    'color' => array("argb" => "55555555") 
                )
           )
        );

        $activeSheet->getStyle('A1:I'.($indexXls - 1))->applyFromArray($styleArray);

        ob_end_clean();//清除缓冲区,避免乱码
        
        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="订单数据.xls"');
        header('Cache-Control: max-age=0');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

}