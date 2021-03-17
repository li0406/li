<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  销售系统 - 分单量
*/
class SalefdlController extends HomeBaseController
{

	//********************[ 会员分单量统计（按城市） ]**************************

	//会员分单量统计（按城市）
    public function index() {

    	//默认查询本月数据
    	$start = strtotime(date('Y-m').'-1');
    	$end = strtotime(date('Y-m-d').' 23:59:59');

    	//如果给定了时间
    	$time1 = I('get.time1');
    	if(!empty($time1)){
    		$time1 = explode(' - ',$time1);
	    	if(date('m',strtotime($time1['0'])) != date('m',strtotime($time1['1']))){
	    		$this->error('时间不能跨月');
	    	}
    		$start = strtotime($time1['0']);
    		$end = strtotime($time1['1'].' 23:59:59');
    	}

    	//如果有对比时间
    	if(I('get.compare') == 'on'){
    		$time2 = explode(' - ',I('get.time2'));
    		if(!empty($time2)){
	    		if(date('m',strtotime($time2['0'])) != date('m',strtotime($time2['1']))){
	    			$this->error('对比时间不能跨月');
	    		}
	    		$compareStart = strtotime($time2['0']);
	    		$compareEnd = strtotime($time2['1'].' 23:59:59');
	    		if($compareStart == $start && $compareEnd == $end){
    				$this->error('对比时间和原时间相同');
    			}

    			$isCompare = true;
    		}
    	}

    	//是否环比
    	if(I('get.prveMonth') == '1'){
    		$compareStart = strtotime(date('Y-m-d',$start).' midnight first day of -1 month');
	    	$compareEnd = strtotime(date('Y-m-01',$start).'-1 day') + 86399;
    	}

    	//是否同比
    	if(I('get.prveYear') == '1'){
    		$compareStart = strtotime($time1['0'].' -1 year');
	    	$compareEnd = strtotime($time1['1'].' 23:59:59 -1 year');
    	}

    	//按时间查询数据
    	$info = $this->getListByDate($start,$end);

    	//开始输出对比数据
    	if(!empty($compareStart) && !empty($compareEnd)){
    		$compareList = $this->getListByDate($compareStart,$compareEnd);
    	}

        //取职能分类
        $cmap['type'] = array('EQ','1');
        $tree = D('SaleSetting')->getCategory($cmap);
        $tree = getSaleCategory($tree);
        $info['tree'] = saleZNBM($tree);

        $info['startTime'] = date('Y-m-d',$start);
        $info['endTime'] = date('Y-m-d',$end);
        $info['compareStart'] = date('Y-m-d',$compareStart);
        $info['compareEnd'] = date('Y-m-d',$compareEnd);

        $info['isCompare'] = $isCompare;

    	//dump($info['list']);

        //判断是否下载Excel
        if(I('get.dl') == '1'){
        	$this->downExcelWithCityFdl($info['list']);
        	die;
        }

    	$this->assign('cityList',getUserCitys());
    	$this->assign('list',$info['list']);
    	$this->assign('cl',$compareList['list']);
    	$this->assign('info',$info);
        $this->display();
    }

    //获取 会员分单量统计（按城市）数据
    public function getListByDate($start,$end,$pageCount = 10){
    	$pageIndex = 1;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        if(I('get.dl') == '1'){
        	$pageCount = 2000;
        }

        $condition = array();

        //取城市
        $cs = I('get.cs');
        if(!empty($cs)){
        	$condition['cs'] = $cs;
        	$info['cs'] = $cs;
        }

        $depart = I('get.znbm');
        if(!empty($depart)){
        	$depCitys = D('User')->searchDepartmentCitys($depart);
    		foreach ($depCitys as $k => $v) {
    			$depCid[] = $v['cid'];
    		}
    		if(!empty($depCid)){
    			$condition['cs'] = implode($depCid,',');
    		}
    		$info['depart'] = $depart;
        }


    	$result = $this->getListByCity($condition,$pageIndex,$pageCount);
    	$list = $result['list'];

    	//取实际会员数 - 按时间
    	$_cityVipNum = D('SaleFdl')->getCityVipNumByDate($start,$end);
    	foreach ($_cityVipNum as $k => $v) {
    		$cityVipNum[$v['city_id']] = $v['vip_count'];
    	}

    	//取分单需求数据
    	$_fdl = D('SaleFdl')->getAllFendanNum($start);
    	foreach ($_fdl as $key => $value) {
    		$fdl[$value['cityid']] = $value['point_num'];
    	}

    	//取城市实际分单量
    	$_cityFdl = D('SaleFdl')->getOrderNumByTime($start,$end);
    	foreach ($_cityFdl as $key => $value) {
    		$cityFdl[$value['cs']] = $value['count'];
    	}

    	//取今天实际分单量
    	$_todayCityFdl = D('SaleFdl')->getOrderNumByTime($start,$end);
    	foreach ($_todayCityFdl as $k => $v) {
    		$todayCityFdl[$v['cs']] = $v['count'];
    	}

    	//取所有城市的职能管辖
    	$_cityManager = D('User')->getCitymanagers();
    	foreach ($_cityManager as $k => $v) {
    		$cityManager[$v['cid']][$v['module']] = array(
    		    'bumen' => $v['bumen'],
    		    'spid' => $v['spid'],
    		    'shi' => $v['shi'],
    		    'shi_name' => $v['shizhang'],
    		    'tpid' => $v['tpid'],
    		    'tid' => $v['tid'],
    		    'tuan' => $v['tuan'],
    		    'tuan_name' => $v['tuanzhang'],
    		    'jingli' => $v['jingli'],
    		    'module' => $v['module'],
    		);
    	}

    	foreach ($list as $k => $v) {
    		//获取当前城市会员数
    		$vipNum = empty($cityVipNum[$v['cid']]) ? '0' : $cityVipNum[$v['cid']];
    		$list[$k]['vipnum'] = $vipNum;

    		//会员完成率 = 当前实际会员/城市会员指标数*100%
    		$list[$k]['wcl'] = round(($vipNum / $v['point']) * 100, 1);

    		//分单需求总数 = 当前城市下的所有会员需要的分单量的总和
    		$list[$k]['fdlAll'] = $fdl[$v['cid']];

    		//平均分单需求数：当前城市下的会员的平均分单需求数量
    		if(!empty($fdl[$v['cid']])){
    			$list[$k]['fdlAvg'] = round($fdl[$v['cid']] / $vipNum, 1);
    		}

    		//当前分单累计数
    		if(!empty($cityFdl[$v['cid']])){
    			$list[$k]['allFdl'] = $cityFdl[$v['cid']];
    		}

    		//今日分单累计数
    		if(!empty($todayCityFdl[$v['cid']])){
    			$list[$k]['todayFdl'] = $todayCityFdl[$v['cid']];
    		}

    		//职能管辖
    		if(!empty($cityManager[$v['cid']])){
    			$list[$k]['cityManager'] = $cityManager[$v['cid']];
    		}
    	}

    	//dump($list);

    	$info['page'] = $result['page'];
    	$info['list'] = $list;

    	return $info;
    }

    //获取 会员分单量统计（按城市）列表并分页
    private function getListByCity($condition,$pageIndex = 1,$pageCount = 10){
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        import('Library.Org.Page.Page');
        $result = D('SaleFdl')->getCityList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp);
    }


    //********************[ 会员分单量统计（按师团） ]**************************

    //会员分单量统计（按师团）
    public function depart() {

    	//默认查询本月数据
    	$start = strtotime(date('Y-m').'-1');
    	$end = strtotime(date('Y-m-d').' 23:59:59');

    	//如果给定了时间
    	$time1 = I('get.time1');
    	if(!empty($time1)){
    		$time1 = explode(' - ',$time1);
	    	if(date('m',strtotime($time1['0'])) != date('m',strtotime($time1['1']))){
	    		$this->error('时间不能跨月');
	    	}
    		$start = strtotime($time1['0']);
    		$end = strtotime($time1['1'].' 23:59:59');
    	}

    	//如果有对比时间
    	if(I('get.compare') == 'on'){
    		$time2 = explode(' - ',I('get.time2'));
    		if(!empty($time2)){
	    		if(date('m',strtotime($time2['0'])) != date('m',strtotime($time2['1']))){
	    			$this->error('对比时间不能跨月');
	    		}
	    		$compareStart = strtotime($time2['0']);
	    		$compareEnd = strtotime($time2['1'].' 23:59:59');
	    		if($compareStart == $start && $compareEnd == $end){
    				$this->error('对比时间和原时间相同');
    			}

    			$isCompare = true;
    		}
    	}

    	//是否环比
    	if(I('get.prveMonth') == '1'){
    		$compareStart = strtotime(date('Y-m-d',$start).' midnight first day of -1 month');
	    	$compareEnd = strtotime(date('Y-m-01',$start).'-1 day') + 86399;
    	}

    	//是否同比
    	if(I('get.prveYear') == '1'){
    		$compareStart = strtotime($time1['0'].' -1 year');
	    	$compareEnd = strtotime($time1['1'].' 23:59:59 -1 year');
    	}


    	//按时间查询数据
    	$info = $this->getListByDate($start,$end,2000);

    	//开始输出对比数据
    	if(!empty($compareStart) && !empty($compareEnd)){
    		$compareList = $this->getListByDate($compareStart,$compareEnd,2000);
    	}


        //取职能分类
        $cmap['type'] = array('EQ','1');
        $tree = D('SaleSetting')->getCategory($cmap);
        $tree = getSaleCategory($tree);
        $info['tree'] = saleZNBM($tree);

        $info['startTime'] = date('Y-m-d',$start);
        $info['endTime'] = date('Y-m-d',$end);
        $info['compareStart'] = date('Y-m-d',$compareStart);
        $info['compareEnd'] = date('Y-m-d',$compareEnd);
        $info['isCompare'] = $isCompare;


        //-------------开始处理师团数据--------------------


        $info['list'] = $this->buildDepData($info['list']);


        if(!empty($compareList)){
        	$compareList['list'] = $this->buildDepData($compareList['list']);
        }



    	//dump($info['list']);

        //判断是否下载Excel
        if(I('get.dl') == '1'){
        	$this->downExcelWithDepFdl($info['list']);
        	die;
        }

    	$this->assign('cityList',getUserCitys());
    	$this->assign('list',$info['list']);
    	$this->assign('cl',$compareList['list']);
    	$this->assign('info',$info);
        $this->display();
    }

    //处理师团数据
    public function buildDepData($list){

        //把城市按团归类
    	foreach ($list as $k => $v){
    		//如果城市管理不为空
    		if(!empty($v['cityManager'])){
	    		foreach ($v['cityManager'] as $key => $value) {
	    			unset($v['cityManager']);
	    			$depart[$value['tid']]['name'] = $value['tuan'];
	    			$depart[$value['tid']]['tid'] = $value['tid'];
	    			$depart[$value['tid']]['depUser'] = $value['tuan_name'];
	    			$depart[$value['tid']]['shi'] = $value['shi'];
	    			$depart[$value['tid']]['jingli'][] = $value['jingli'];
	    			$depart[$value['tid']]['sub'][] = $v;
	    		}
    		}
    	}

		foreach ($depart as $k => $v) {

			foreach ($v['sub'] as $key => $value) {
				//城市会员指标
				$v['point'] = $v['point'] + $value['point'];
				//实际会员数
				$v['vipnum'] = $v['vipnum'] + $value['vipnum'];
				//分单需求总数
				$v['fdlAll'] = $v['fdlAll'] + $value['fdlAll'];
				//当前分单累计数
				$v['allFdl'] = $v['allFdl'] + $value['allFdl'];
				//今日分单累计数
				$v['todayFdl'] = $v['todayFdl'] + $value['todayFdl'];

				$v['citys'][] = $value['cname'];
			}

			//会员完成率 实际会员/城市会员指标数*100%。
			$v['wcl'] = round(($v['vipnum'] / $v['point']) * 100, 1);

			//平均分单需求数：当前城市下的会员的平均分单需求数量
			$v['fdlAvg'] = round($v['fdlAll'] / $v['vipnum'], 1);

			$v['jingli'] = implode(',',array_unique($v['jingli']));
			$v['cityNum'] = count($v['citys']);
			$v['citys'] = implode(',',array_unique($v['citys']));

			unset($v['sub']);

			$result[] = $v;
		}

		return $result;
    }

    //输出会员分单量统计Excel按城市
    public function downExcelWithCityFdl($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        //设置表头
        $title = array(
            '城市',
            '拓展师长',
            '品牌师长',
            '拓展团长',
            '品牌团长',
            '实际会员数',
            '城市会员指标',
            '会员完成率',
            '分单需求总数',
            '分单需求平均数',
            '当前分单累计数',
            '今日分单累计数',
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
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['1']['shi']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['2']['shi']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['1']['tuan']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['2']['tuan']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['vipnum']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['point']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['wcl'].' %');

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fdlAll']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fdlAvg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['allFdl']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['todayFdl']);

            $j++;
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="分单量统计(按城市).xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    //输出会员分单量统计Excel按师团
    public function downExcelWithDepFdl($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        //设置表头
        $title = array(
            '部门',
            '城市',
            '拓展师长',
            '品牌师长',
            '拓展团长',
            '品牌团长',
            '实际会员数',
            '分单需求总数',
            '分单需求平均数',
            '当前分单累计数',
            '今日分单累计数',
            '团长',
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
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['name']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityNum']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['1']['shi']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['2']['shi']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['1']['tuan']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cityManager']['2']['tuan']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['vipnum']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fdlAll']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fdlAvg']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['allFdl']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['todayFdl']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['depUser']);

            $j++;
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="分单量统计(按团师).xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }


}
