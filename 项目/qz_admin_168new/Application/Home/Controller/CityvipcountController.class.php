<?php

//首页快速发布问题

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class CityvipcountController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','6');
    }

    //首页
    public function index(){

        //城市管理类别  0商务 1外销 2商务外销
        $dept = I('get.dept');
        if(!empty($dept)){
            $isSetDept = true;
            //$realDept = $dept == '1' ? '0' : '1';
            if($dept == 1){
                $realDept = [1];
            }else{
                $realDept = [0,2];
            }
            $info['dept'] = $dept;
        }

        //城市会员等级
        $level = I('get.level');
        if(!empty($level)){
            $info['level'] = $level;
        }

        $_list = D('Cityvip')->getCityVipCount();

        foreach ($_list as $key => $v) {

            //判断管辖内类别
            //if($isSetDept == true && $v['manager'] != $realDept){
            if($isSetDept == true && in_array($v['manager'], $realDept)){
                continue;
            }

            //判断管辖内类别
            if(!empty($level) && $v['vipcnt'] < $level){
                //dump($v);
                continue;
            }
            $num['allnum'] += $v['vipcnt'];
            $num['companynum'] += $v['vipnum'];
            $num['mulitnum'] += $v["mulitnum"];
            $num['halfnum'] += $v["halfnum"];
            $list[] = $v;
        }
        $num['citynum'] = count($list);
        $num['doublenum'] = $num['mulitnum']+$num['halfnum']/2;

        if(I('get.dl') == '1'){
            $this->downExcel($list);
            die;
        }

        $new_info = D('Cityvip')->getNewCityVipCount();
        $this->assign("new_info",$new_info);
        $this->assign('num',$num);
        $this->assign("info",$info);
        $this->assign("list",$list);
        $this->display();
    }

    //下载Excel
    public function downExcel($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头
        $title = array(
            '城市',
            '会员数',
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        //设置表内容
        $j = 1;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cname']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['vipcnt']);

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
        header('Content-Disposition:attachment;filename="城市会员统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

}