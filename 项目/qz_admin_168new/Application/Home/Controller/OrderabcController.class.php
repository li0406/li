<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*   每月ABC订单分析
*/
class OrderabcController extends HomeBaseController {

    public function _initialize() {
        parent::_initialize();
    }

    public function index (){
        $year = I('get.year');
        $startMonth = I('get.month_start');
        $endMonth = I('get.month_end');
        $year = empty($year) ? date('Y') : $year; //年份 默认当年
        $startMonth = empty($startMonth) ? '1' : $startMonth; //开始 默认1月份
        if($year == date('Y')){
            $endMonth = empty($endMonth) ? date('n') : $endMonth; //结束 默认当月
        }else{
            $endMonth = empty($endMonth) ? 12 : $endMonth; //结束 默认12月份
        }
        
        //取所有城市
        $quyu = D('Orderabc')->getQuyu();       

        for ($i = $startMonth; $i <= $endMonth; $i++) {
            $start = mktime(0,0,0,$i,1,$year);
            $end = mktime(23,59,59,$i,date('t',mktime(0,0,0,$i,1,$year)),$year);
            $list[$i] = $this->getOrderAbcByMonth($start,$end,$quyu);
        }

        //dump($list);

        //下载Excel
        if(I('get.dl') == '1'){
            $tab = I('get.tab');
            if($tab == 'city'){
                $this->downCityExcel($list);
                die;
            }
            if($tab == 'dept'){
                $this->downDeptExcel($list);
                die;
            }
            if($tab == 'abc'){
                $this->downABCExcel($list);
                die;
            }
        }


        $info['year'] = $year;
        $info['start'] = $startMonth;
        $info['end'] = $endMonth;

        $this->assign('list',$list);
        $this->assign('info',$info);
        $this->display();       
    }

    //按月份取 每月ABC订单分析
    public function getOrderAbcByMonth($start,$end,$quyu){

        $result = S("C:OrderAbc:".date('Ym',$start).date('Ym',$end));
        if(!empty($result)){
            return $result;
        }

        //订单数
        $orders = D('Orderabc')->getCityOrderByTime($start,$end);

        //会员数     
        $vipNum = $this->getVipNum($start,$end);
        //dump($orders);       
        
        foreach ($quyu as $k => $v) {
            //管理类别 0商务 1外销 2商务外销
            $dept = $v['manager'] == '0' ? 'in' : 'out';
            //城市级别 ABC
            switch ($v['little']) {
                case '0':
                    //a类城市
                    $ABC = 'A';
                    break;
                case '1':
                    //b类城市
                    $ABC = 'B';
                    break;
                case '2':
                    //c类城市
                    $ABC = 'C';
                    break;
            }

            //分单量
            if(!empty($orders[$v['cid']])){                
                $cityList[$dept][$ABC]['fendan'] = $cityList[$dept][$ABC]['fendan'] + $orders[$v['cid']]['fendan'];
                $deptList[$dept]['fendan'] = $deptList[$dept]['fendan'] + $orders[$v['cid']]['fendan'];
                $abcList[$ABC]['fendan'] = $abcList[$ABC]['fendan'] + $orders[$v['cid']]['fendan'];
            }

            //会员数
            if(!empty($vipNum[$v['cid']])){                
                $cityList[$dept][$ABC]['vipNum'] = $cityList[$dept][$ABC]['vipNum'] + $vipNum[$v['cid']];
                $deptList[$dept]['vipNum'] = $deptList[$dept]['vipNum'] + $vipNum[$v['cid']];
                $abcList[$ABC]['vipNum'] = $abcList[$ABC]['vipNum'] + $vipNum[$v['cid']];
            }

            $allCount['fendan'] = $allCount['fendan'] + $orders[$v['cid']]['fendan'];
            $allCount['vipNum'] = $allCount['vipNum'] + $vipNum[$v['cid']];
        }

        $cityList['in']['A']['vipNum'] = round($cityList['in']['A']['vipNum'],2);
        $cityList['in']['B']['vipNum'] = round($cityList['in']['B']['vipNum'],2);
        $cityList['in']['C']['vipNum'] = round($cityList['in']['C']['vipNum'],2);
        $cityList['out']['A']['vipNum'] = round($cityList['out']['A']['vipNum'],2); 
        $cityList['out']['B']['vipNum'] = round($cityList['out']['B']['vipNum'],2); 
        $cityList['out']['C']['vipNum'] = round($cityList['out']['C']['vipNum'],2);        

        //平均分单量 当月该城市类别的分单量/会员数
        $cityList['in']['A']['avg'] = round($cityList['in']['A']['fendan'] / $cityList['in']['A']['vipNum'],2);
        $cityList['in']['B']['avg'] = round($cityList['in']['B']['fendan'] / $cityList['in']['B']['vipNum'],2);
        $cityList['in']['C']['avg'] = round($cityList['in']['C']['fendan'] / $cityList['in']['C']['vipNum'],2);
        $cityList['out']['A']['avg'] = round($cityList['out']['A']['fendan'] / $cityList['out']['A']['vipNum'],2);
        $cityList['out']['B']['avg'] = round($cityList['out']['B']['fendan'] / $cityList['out']['B']['vipNum'],2);
        $cityList['out']['C']['avg'] = round($cityList['out']['C']['fendan'] / $cityList['out']['C']['vipNum'],2);

        $deptList['out']['vipNum'] = round($deptList['out']['vipNum'],2);
        $deptList['in']['vipNum'] = round($deptList['in']['vipNum'],2);

        $deptList['in']['avg'] = round($deptList['in']['fendan'] / $deptList['in']['vipNum'],2);
        $deptList['out']['avg'] = round($deptList['out']['fendan'] / $deptList['out']['vipNum'],2);

        $abcList['A']['vipNum'] = round($abcList['A']['vipNum'],2);
        $abcList['B']['vipNum'] = round($abcList['B']['vipNum'],2);
        $abcList['C']['vipNum'] = round($abcList['C']['vipNum'],2);

        $abcList['A']['avg'] = round($abcList['A']['fendan'] / $abcList['A']['vipNum'],2);
        $abcList['B']['avg'] = round($abcList['B']['fendan'] / $abcList['B']['vipNum'],2);
        $abcList['C']['avg'] = round($abcList['C']['fendan'] / $abcList['C']['vipNum'],2);


        $allCount['avg'] = round($allCount['fendan'] / $allCount['vipNum'],2);
        $allCount['vipNum'] = round($allCount['vipNum'],2);

        ksort($cityList['in']);
        ksort($cityList['out']);

        $result = array('city' => $cityList,'dept' => $deptList,'abc' => $abcList,'count' => $allCount);
        S("C:OrderAbc:".date('Ym',$start).date('Ym',$end),$result,3600 * 2);
        return $result;
    }

    //计算会员数 按单个月份查询
    public function getVipNum($start_time,$end_time){

        $list = D('Orderabc')->getUserVip($start_time,$end_time);

        //开始时间
        $start = date('Y-m-d',$start_time);
        //结束时间
        $end = date('Y-m-d',$end_time);
        //当月开始时间
        $monthStart = date('Y-m-d',mktime(0,0,0,date('m',$start_time),1,date('Y',$start_time)));
        //当月结束时间
        $monthEnd = date('Y-m-d',mktime(0,0,0,date('m',$end_time),date('t',$end_time),date('Y',$end_time)));
        //当月天数
        $monthDay = date('t',$end_time);

        foreach ($list as $k => $v) {
            //整月会员
            if($v['start_time'] <= $start && $v['end_time'] >= $end){
                $vipNum[$v['cs']] =  $vipNum[$v['cs']] + 1;
                continue;
            }
            $isAM = 1;

            //上会员时间和操作时间为同一天
            if(date('Y-m-d',$v['time']) == $v['start_time']){                
                //下午上会员
                if(date('H',$v['time']) > '12'){
                    $isAM = 0;
                }
            }

            //开始时间小于本月
            if($v['start_time'] < $monthStart){                
                $theStart = 1;
            }else{//结束时间小于本月
                $theStart = date('j',strtotime($v['start_time']));
            }

            //结束时间大于本月
            if($v['end_time'] > $monthEnd){                
                $theEnd = $monthDay;
            }else{//结束时间小于本月
                $theEnd = date('j',strtotime($v['end_time']));
            }

            // dump($v['start_time'].' | '.$v['end_time'].' / 操作时间：'.date('Y-m-d H',$v['time']));
            // dump($theEnd.' - '.$theStart .' + '.$isAM);

            $days = ($theEnd - $theStart + $isAM) / $monthDay;
            $vipNum[$v['cs']] = $vipNum[$v['cs']] + $days;
        }

        //会员数 四舍五入取一位小数
        foreach ($vipNum as $key => $value) {
            //$vipNum[$key] = round($value,1);
        }

        return $vipNum;
    }

    //下载Excel
    public function downABCExcel($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        //设置表头
        $title = array(
            '',
            'A类城市',
            'B类城市',
            'C类城市',
            '合计',
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1; 
            if($i != 1){
                $i =  $i + 2;
            }         
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        $phpExcel->getActiveSheet()->mergeCells('B1:D1');
        $phpExcel->getActiveSheet()->mergeCells('E1:G1');
        $phpExcel->getActiveSheet()->mergeCells('H1:J1');
        $phpExcel->getActiveSheet()->mergeCells('K1:M1');
        $phpExcel->getActiveSheet()->getStyle('A1:M2')->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            )
        );

        $subtitle = array(
            '月份',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
        );
        $i = 0;
        foreach ($subtitle as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 2;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
                                
        //设置表内容
        $j = 2;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$k);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['A']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['A']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['A']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['B']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['B']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['B']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['C']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['C']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['abc']['C']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['avg']);

            $j++;
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="每月ABC订单分析-城市类别统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    //下载Excel
    public function downDeptExcel($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头
        $title = array(
            '',
            '外销部',
            '商务部',
            '合计',
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1; 
            if($i != 1){
                $i =  $i + 2;
            }         
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        $phpExcel->getActiveSheet()->mergeCells('B1:D1');
        $phpExcel->getActiveSheet()->mergeCells('E1:G1');
        $phpExcel->getActiveSheet()->mergeCells('H1:J1');
        $phpExcel->getActiveSheet()->getStyle('A1:J2')->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            )
        );

        $subtitle = array(
            '月份',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
        );
        $i = 0;
        foreach ($subtitle as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 2;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
                                
        //设置表内容
        $j = 2;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$k);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['dept']['out']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['dept']['out']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['dept']['out']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['dept']['in']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['dept']['in']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['dept']['in']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['avg']);

            $j++;
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="每月ABC订单分析-部门统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    //下载Excel
    public function downCityExcel($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头
        $title = array(
            '',
            '外销A类',
            '外销B类',
            '外销C类',
            '商务A类',
            '商务B类',
            '商务C类',
            '合计',
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1; 
            if($i != 1){
                $i =  $i + 2;
            }         
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        $phpExcel->getActiveSheet()->mergeCells('B1:D1');
        $phpExcel->getActiveSheet()->mergeCells('E1:G1');
        $phpExcel->getActiveSheet()->mergeCells('H1:J1');
        $phpExcel->getActiveSheet()->mergeCells('K1:M1');
        $phpExcel->getActiveSheet()->mergeCells('N1:P1');
        $phpExcel->getActiveSheet()->mergeCells('Q1:S1');
        $phpExcel->getActiveSheet()->mergeCells('T1:V1');

        $phpExcel->getActiveSheet()->getStyle('A1:V2')->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            )
        );

        $subtitle = array(
            '月份',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
            '分单量',
            '会员数',
            '平均分单量',
        );
        $i = 0;
        foreach ($subtitle as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 2;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
                                
        //设置表内容
        $j = 2;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$k);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['A']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['A']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['A']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['B']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['B']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['B']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['C']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['C']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['out']['C']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['A']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['A']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['A']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['B']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['B']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['B']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['C']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['C']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['city']['in']['C']['avg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['fendan']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['vipNum']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['count']['avg']);

            $j++;
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="每月ABC订单分析-城市统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }
}