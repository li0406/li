<?php

//城市区域管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class CityavgvipController extends HomeBaseController
{

    // 城市平均会员统计
    public function index()
    {
        if (I("get.begin") !== "") {
            $begin = I("get.begin");
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
        }

        if (I("get.city") !== "") {
            $cs = I("get.city");
        }

        self::cityavgvipTimeChk($begin, $end);

        //获取所有城市信息
        $citys = D("Quyu")->getAllQuyuOnly();
        $this->assign("citys", $citys);
        $result = D('Home/Logic/LogUserRealCompany')->cityAvgVip($cs, $begin, $end);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    // 城市平均会员统计 导出excel
    public function exportcityavgvip () {
        if (I("get.begin") !== "") {
            $begin = I("get.begin");
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
        }

        if (I("get.city") !== "") {
            $cs = I("get.city");
        }

        if (I("get.city_name") !== "") {
            $cs_name = I("get.city_name");
        }

        self::cityavgvipTimeChk($begin, $end);

        if (!empty($begin) && !empty($end)) {
            $begin = strtotime($begin);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
        } else {
            $begin = strtotime(date('Y-m-01', strtotime(date("Y-m-d")) ) ); //当月第一天
            $end = time();
        }


        ini_set('memory_limit','512M');
        ini_set('max_execution_time',  60 * 3);
        $excelData = [];
        $excelData['header'] =   ['城市' => 'string',
                                  '平均会员数' => 'string',
                                  '平均会员总数' => 'string',
                                  '城市订单量' => 'string',
                                  '城市分单量' => 'string',
                                  '平均分单量' => 'string',
        ];
        $excelData['sheet'] = 'sheet1';
        $excelData['row'] = [];
        $result = D('Home/Logic/LogUserRealCompany')->cityAvgVipList($cs, $begin, $end, null, null);
        $rowAll = [];
        foreach ($result as $key => $value) {
            $row1 = [];
            foreach ($value as $rk => $rv) {
                $row1[] =  $rv;
            }
            $rowAll[] = $row1;
        }
        //dump($rowAll);
        //die();
        $excelData['row'] = $rowAll;
        $excelData['filename'] = '城市平均会员统计 '. $cs_name . ' ' . date('Y-m-d', $begin) . '到' . date('Y-m-d', $end) .'.xlsx';
        export_excel_download($excelData);
    }

    // 输入参数检查
    private function cityavgvipTimeChk($begin, $end) {

        if ((!empty($begin) && !empty($end) && strtotime($begin) > strtotime($end))) {
            $this->error('时间范围错误');
            die();
        }

        $days = diffBetweenTwoDays($begin,$end) + 1;
        if ($days > 31*3 ) {
            $this->error('选取的时间范围不能大于三个月');
            die();
        }

        if ((!empty($begin) && (strtotime($begin) === false || strtotime($begin) < strtotime('2018-01-01 00:00:00'))) || (!empty($end) && (strtotime($end) === false || (strtotime($end) < strtotime('1970-01-01 01:00:00'))))) {
            $this->error('时间选择错误,请选择2018年以后时间');
            die();
        }

    }

}