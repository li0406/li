<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class SalesettingController extends HomeBaseController{

    //构造
    public function _initialize(){
        parent::_initialize();
    }

    /*
     * 新销售：城市会员指标
     * 
    */
    public function setCityPoint()
    {

        //获取自己的管辖城市
        //$citys = D('Citypoint')->getManageCitys();
        //var_dump($citys);
        //获取所有的操作人
        $managers = D('Citypoint')->getCityPointManagers();
        //搜索关键词 
        $keyword['cityid'] = I("get.cityid");
        $keyword['department'] = I("get.department");
        $keyword['time'] = I("get.time");

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($keyword['cityid']) && in_array($keyword['cityid'], $ids)){
                $keyword['cityid']            = I("get.cityid");
            }else{
                $idstr = implode(',', $ids);
                $keyword['cityid'] = array('IN',$idstr); 
            }
        }

        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        if(!empty($ids)){
            $list = $this->getCityPoint($pageIndex, $pageCount, $keyword);
        }
        
        $keyword['cityid'] = I("get.cityid");

        $points = $list['list'];
        foreach ($points as $k => $v) {
            $points[$k]['time'] = date('Y-m',$v['time']);
            $points[$k]['point'] = $v['point'];
            $points[$k]['lasttime'] = date('Y-m-d',$v['lasttime']);
            //区域表 manager = 1 外销  其他为商务
            if($v['dept'] == 1){
                $points[$k]['department'] = '商务';
            }else{
                $points[$k]['department'] = '外销';
            }
        }

        //var_dump($points);
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('manager',$managers);
        $this->assign('page',$list['page']);
        $this->assign('list',$points);
        $this->assign('keyword',$keyword);
        $this->assign("citys",$citys);
        $this->display();
    }

    /*
    * 城市会员指标：批量导入指标参数
    *
    */
    public function uploadExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  cityid   point  time  lasttime  manager status
        $nuin = '';
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }

            //判断城市是否存在
            $city = D('Citypoint')->getCityInCityManage(trim($v[0]));
            if(empty($city)){
                $unin .= $v[0].'_';
                continue;
            } 

            $cid = $city['id'];//城市ID

            //把所有$cid城市的数据status 设置为1
            $isHave = D('Citypoint')->setOldValues($cid);


            $data['cityid'] = $cid;
            $data['point'] = $v[3];
            $data['time'] = strtotime($v[4]);
            $data['lasttime'] = time();
            $data['manager'] = $_SESSION['uc_userinfo']['name'];
            $data['status'] = 0;

            M('sale_citypoint')->add($data);
            //写入城市级别+重点系数
            
            if(empty($v[2])){
                $c_data['ratio'] =0;
            }else{
                $c_data['ratio'] = $v[2];
            }
            if($v[1] == '地级市'){
                $c_data['level'] = 1;
            }elseif($v[1] == '区'){
                $c_data['level'] = 2;
            }elseif($v[1] == '县城'){
                $c_data['level'] = 3;
            }elseif($v[1] == '县级市'){
                $c_data['level'] = 4;
            }else{
                $c_data['level'] = 0; 
            }
            D('Citypoint')->setCityManagerValues($v[0],$c_data);
        }
        if(!empty($nuin)){
            $nuin = $unin.'等城市不存在，故未写入';
        }

        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 2;
        $logData['opdes']       = '批量导入城市会员指标';
        $num                    = count($excel);
        $logData['content']     = '批量导入城市会员指标'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        
        $this->ajaxReturn(array("data"=>$nuin,"info"=>"修改成功！","status"=>1));
    }


    /*
     * 销售系统设置：公用模板下载
     * GET传入type ,对应关系查看下面$moduleName数组
     * 如果有传入data=1 ，是下载数据，不传data是下载模板
    */
    public function downLoadModule(){
        $type = I("get.type");//
        $data = I('get.data');//有data=1 时，填写表格内容
        $moduleName = [
                    1 => '岗位城市权限',
                    2 => '城市会员指标',
                    3 => '部门续费月度系数',
                    4 => '中心续费月度系数',
                    6 => '城市QQ群',
                    7 => '到期数',
                    8 => '实际续费数',
                    9 => '实际续费月数',
                    10 => '城市会员合作时长',
                    11 => '城市新签合作时长',
                    12 => '城市续费合作时长',
                    13 => '城市续费率',
                    14 => '城市续费月数完成率',
                    15 => '续费月数指标',
                    16 => '城市分单量满足率',
                    17 => '会员分单量满足率'
                ];
        $name = $moduleName[$type];
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        if($type == 1){
            //岗位城市权限
            $title = [
                '城市',
                '开站状态',
                '城市级别',
                '部门',
                '重点系数',
                '军长',
                '拓展师长',
                '拓展团长',
                '城市经理',
                '品牌师长',
                '品牌团长',
                '品牌师',
                '操作人',
                '操作时间',
            ];
        }elseif($type == 2){
            //城市会员指标
            if($data == 1){
                //导出数据表结构
                $title = [
                    '城市',
                    '部门',
                    '城市级别',
                    '重点系数',
                    '会员指标',
                    '生效月份',
                    '操作时间',
                    '操作人'
                ];
            }else{
                //下载模板
                $title = [
                    '城市',
                    '城市级别',
                    '重点系数',
                    '会员指标',
                    '生效月份',
                ];
            }
        }elseif($type == 3){
            //部门续费月度系数
            if($data == 1){
                $title = [
                    '财年月份',
                    '部门',
                    '续费率月度系数',
                    '续费月度完成率月度系数',
                    '续费率指标',
                    '续费月数完成率指标',
                    '全年续费率均值',
                    '续费率最高值',
                    '全年续费月数完成率均值',
                    '续费月数完成率最高值',
                    '操作时间',
                    '操作人'
                ];
            }else{
                $title = [
                    '财年月份',
                    '部门',
                    '续费率月度系数',
                    '续费月度完成率月度系数',
                    '续费率指标',
                    '续费月数完成率指标',
                    '全年续费率均值',
                    '续费率最高值',
                    '全年续费月数完成率均值',
                    '续费月数完成率最高值'
                ];
            }
        }elseif($type == 4){
            //中心续费月度系数
            if($data == 1){
                $title = [
                    '财年月份',
                    '部门',
                    '续费率月度系数',
                    '续费月度完成率月度系数',
                    '实际群成员数',
                    '续费率指标',
                    '续费月数完成率指标',
                    '全年续费率均值',
                    '续费率最高值',
                    '全年续费月数完成率均值',
                    '续费月数完成率最高值',
                    '操作时间',
                    '操作人',
                    '操作'
                ];
            }else{
                $title = [
                    '财年月份',
                    '部门',
                    '续费率月度系数',
                    '续费月度完成率月度系数',
                    '实际群成员数',
                    '续费率指标',
                    '续费月数完成率指标',
                    '全年续费率均值',
                    '续费率最高值',
                    '全年续费月数完成率均值',
                    '续费月数完成率最高值'
                ];

                //写入操作日志  opid optime opip optype opdes conten
                $logData['optype']      = 8;
                $logData['opdes']       = '导出中心续费月数系数模板';
                $logData['content']     = '导出中心续费月数系数模板';
                
            }
        }elseif($type == 6){
            //城市QQ群
            if($data == 1){
                $title = [
                    '城市',
                    '部门',
                    'QQ群名称',
                    '群成员指标',
                    '实际群成员数',
                    '录入时间',
                    '操作人'
                ];
            }else{
                $title = [
                    '城市',
                    'QQ群名称',
                    '群成员指标',
                    '实际群成员数'
                ];
            }
        }elseif($type == 7){
            //到期数
            $time = I("get.time");
            if(!empty($time)){
                $baseyear = strtotime($map['time'].'-01');//转化为时间戳
            }else{
                $baseyear = date('Y',time());
            }
            $nextyear = $baseyear + 1;
            if($data == 1){
                $title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01',
                    '操作时间',
                    '操作人'
                ];
            }else{
                $title = [
                    '城市',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01'
                ];
            }
        }elseif($type == 8){
            //实际续费数
            $time = I("get.time");
            if(!empty($time)){
                $baseyear = strtotime($map['time'].'-01');//转化为时间戳
            }else{
                $baseyear = date('Y',time());
            }
            $nextyear = $baseyear + 1;
            if($data == 1){
                $title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01',
                    '操作时间',
                    '操作人'
                ];
            }else{
                $title = [
                    '城市',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01'
                ];
            }
        }elseif($type == 9){
            //续费月数指标
            $time = I("get.time");
            if(!empty($time)){
                $baseyear = strtotime($map['time'].'-01');//转化为时间戳
            }else{
                $baseyear = date('Y',time());
            }
            $nextyear = $baseyear + 1;
            if($data == 1){
                $title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01',
                    '操作时间',
                    '操作人'
                ];
            }else{
                $title = [
                    '城市',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01'
                ];
            }
        }elseif($type == 10){
            //城市会员合作时长
            $title = [
                '部门',
                '城市',
                '品师长',
                '品团长',
                '品牌师',
                '拓师长',
                '拓团长',
                '城市经理',
                '实际会员总数',
                '会员数>=1年',
                '占比',
                '1年>会员数>=0.5年',
                '占比',
                '0.5年>会员数>=0.25年',
                '占比',
                '会员数<0.25年',
                '占比'
            ];
        }elseif($type == 11){
            //城市新签合作时长
            $title = [
                '部门',
                '城市',
                '拓师长',
                '拓团长',
                '城市经理',
                '实际会员总数',
                '会员数>=1年',
                '占比',
                '1年>会员数>=0.5年',
                '占比',
                '0.5年>会员数>=0.25年',
                '占比',
                '会员数<0.25年',
                '占比'
            ];
        }elseif($type == 12){
            //城市续费合作时长
            $title = [
                '部门',
                '城市',
                '品师长',
                '品团长',
                '品牌师',
                '实际会员总数',
                '会员数>=1年',
                '占比',
                '1年>会员数>=0.5年',
                '占比',
                '0.5年>会员数>=0.25年',
                '占比',
                '会员数<0.25年',
                '占比'
            ];
        }elseif($type == 13){
            //城市续费率
            $title = [
                '城市',
                '城市重点系数',
                '部门',
                '品师长',
                '品团长',
                '品牌师',
                '拓师长',
                '拓团长',
                '城市经理',
                '到期数',
                '实际续费数',
                '续费率',
                '续费率指标',
                '续费率达成',
                '全年续费率均值',
                '系数后续费率',
                '续费率封顶值',
                '超出会员数'
            ];
        }elseif($type == 14){
            //城市续费月数完成率
            $title = [
                '城市',
                '部门',
                '品师长',
                '品团长',
                '品牌师',
                '到期数',
                '续费月数指标',
                '实际续费月数',
                '续费月数完成率',
                '续费月数完成率指标',
                '续费月数完成率达成',
                '全年续费月数完成率均值',
                '系数后续费月数完成率',
                '续费月数完成率封顶值',
                '超出月数'
            ];
        }elseif($type == 15){
            //实际续费月数
            $time = I("get.time");
            if(!empty($time)){
                $baseyear = strtotime($map['time'].'-01');//转化为时间戳
            }else{
                $baseyear = date('Y',time());
            }
            $nextyear = $baseyear + 1;
            if($data == 1){
                $title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01',
                    '操作时间',
                    '操作人'
                ];
            }else{
                $title = [
                    '城市',
                    $baseyear.'-02',
                    $baseyear.'-03',
                    $baseyear.'-04',
                    $baseyear.'-05',
                    $baseyear.'-06',
                    $baseyear.'-07',
                    $baseyear.'-08',
                    $baseyear.'-09',
                    $baseyear.'-10',
                    $baseyear.'-11',
                    $baseyear.'-12',
                    $nextyear.'-01'
                ];
            }
        }elseif($type == 16){
            $title = [
                '城市',
                '部门',
                '城市重点系数',
                '品师长',
                '品团长',
                '品牌师',
                '拓展师长',
                '拓展团长',
                '城市经理',
                '实际总会员数',
                '计划月分单数均值',
                '实际月分单数均值（全月会员）',
                '时间进度比',
                '分单满足率',
                '分单总数',
                '非全月会员数',
                '实际月分单数均值（非全月）'
            ];
        }elseif($type == 17){
            $title = [
                '会员ID',
                '会员简称',
                '会员部门',
                '城市',
                '品牌师长',
                '品牌团长',
                '品牌师',
                '拓展师长',
                '拓展团长',
                '城市经理',
                '会员状态',
                '计划月分单数',
                '实际月分单数',
                '当前累计合作天数',
                '时间进度',
                '分单满足率',
                '本月缺单数',
                '本次合同开始',
                '本次合同结束',
            ];
        }else{
            exit;
        }
        //生成表头
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        //有data=1 时，填写表格内容
        if($data == 1){
            //设置表内容
            $j = 1;
            if($type == 2){
                $info = S("Cache:AllCityPoint");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'城市级别',
                    if($value['level'] == 1){
                        $level = '地级市';
                    }elseif($value['level'] == 2){
                        $level = '区';
                    }elseif($value['level'] == 3){
                        $level = '县城';
                    }elseif($value['level'] == 4){
                        $level = '县级市';
                    }else{
                        $level = '-';
                    }
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$level);
                    //'重点系数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$value['ratio']);
                    //'会员指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$value['point']);
                    //'生效月份',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['time']);
                    //'操作时间',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lasttime']);
                    //'操作人',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 2;
                $logData['opdes']       = '导出城市会员指标数据';
                $logData['content']     = '导出城市会员指标数据';
            }elseif($type == 6){
                $info = S("Cache:AllQQGroups");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);  
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'群名称',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['name']);
                    //'成员指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$value['point']);
                    //实际成员数,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$value['num']);
                    //'操作时间',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['time']);
                    //'操作人',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 9;
                $logData['opdes']       = '导出城市QQ群数据';
                $logData['content']     = '导出城市QQ群数据';
            }elseif($type == 7){
                $info = S("Cache:AllExpireConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage']);
                    foreach ($value['point'] as $k => $v) {
                        //财年月份数据，12条
                        $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$v);
                    }
                    //'操作时间',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lasttime']);
                    //'操作人',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 3;
                $logData['opdes']       = '导出到期数数据';
                $logData['content']     = '导出到期数数据';
            }elseif($type == 8){
                $info = S("Cache:AllLOFConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage']);
                    foreach ($value['point'] as $k => $v) {
                        //财年月份数据，12条
                        $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$v);
                    }
                    //'操作时间',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lasttime']);
                    //'操作人',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 4;
                $logData['opdes']       = '导出实际续费数数据';
                $logData['content']     = '导出实际续费数数据';
            }elseif($type == 9){
                $info = S("Cache:AllLOFMonthConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage']);
                    foreach ($value['point'] as $k => $v) {
                        //财年月份数据，12条
                        $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$v);
                    }
                    //'操作时间',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lasttime']);
                    //'操作人',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 5;
                $logData['opdes']       = '导出实际续费月数数据';
                $logData['content']     = '导出实际续费月数数据';
            }elseif($type == 10){
                $info = S("Cache:AllCityVipConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division_name']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment_name']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage_name']);
                    //'拓师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_division_name']);
                    //'拓团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_regiment_name']);
                    //城市经理,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_manage_name']);
                    //'实际会员总数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['realvipnum']);
                    //'会员数>=1年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gtyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gtyear_percent']);
                    //'1年>会员数>=0.5年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gthalfyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gthalfyear_percent']);
                    //'0.5年>会员数>=0.25年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lthalfyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lthalfyear_percent']);
                    //'会员数<0.25年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ltmonth']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ltmonth_percent']);
                    $j++;
                }
            }elseif($type == 11){
                $info = S("Cache:AllCityNewSigningVipConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'拓师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_division_name']);
                    //'拓团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_regiment_name']);
                    //城市经理,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_manage_name']);
                    //'实际会员总数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['realvipnum']);
                    //'会员数>=1年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gtyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gtyear_percent']);
                    //'1年>会员数>=0.5年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gthalfyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gthalfyear_percent']);
                    //'0.5年>会员数>=0.25年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lthalfyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lthalfyear_percent']);
                    //'会员数<0.25年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ltmonth']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ltmonth_percent']);
                    $j++;
                }
            }elseif($type == 12){
                $info = S("Cache:AllCityRenewVipConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division_name']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment_name']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage_name']);
                    //'实际会员总数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['realvipnum']);
                    //'会员数>=1年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gtyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gtyear_percent']);
                    //'1年>会员数>=0.5年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gthalfyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['gthalfyear_percent']);
                    //'0.5年>会员数>=0.25年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lthalfyear']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lthalfyear_percent']);
                    //'会员数<0.25年',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ltmonth']);
                    //'占比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ltmonth_percent']);
                    $j++;
                }
            }elseif($type == 14){
                $info = S("Cache:AllCityReNewMonthConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division_name']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment_name']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage_name']);
                    //'到期数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['daoqi']);
                    //'续费月数指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_month_point']);
                    //'实际续费月数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_month']);
                    //'续费月数完成率',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_month_rate']);
                    //'续费月数完成率指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_month_rate_point']);
                    //'续费月数完成率达成',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_month_rate_complete']);
                    //'全年续费月数完成率均值',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_year_value']);


                    if($value['dept'] == 2){
                        //外销   系数后续费月数完成率=实际续费月数/（续费月数指标*本月续费月数率指标）*92.3%
                        $renew_monthly_rate = ($value['renew_month']/($value['renew_month_point']*($value['renew_month_rate_point']/100)))*(0.923);
                    }else if($value['dept'] == 1){
                        //商务  系数后续费月数完成率=实际续费月数/（续费月数指标*本月续费月数率指标）*88.8%
                        $renew_monthly_rate = ($value['renew_month']/($value['renew_month_point']*($value['renew_month_rate_point']/100)))*(0.888);
                    }
                    $renew_monthly_rate = (number_format($renew_monthly_rate*100,1,'.','')).'%';
                    //'系数后续费月数完成率',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$renew_monthly_rate);
                    //'续费月数完成率封顶值',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_max']);
                    //'超出月数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['over_month']);
                    $j++;
                }
            }elseif($type == 13){
                $info = S("Cache:AllCityReNewConn".date('Y-m',time()));
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'城市重点系数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ratio']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division_name']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment_name']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage_name']);
                    //'拓师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_division_name']);
                    //'拓团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_regiment_name']);
                    //'城市经理',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_manage_name']);
                    //'到期数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['daoqi']);
                    //'实际续费数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['realnum']);
                    //'续费率',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_percent']);
                    //'续费率指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_point']);
                    //'续费率达成',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_compass']);
                    //'全年续费率均值',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['year_renew_num']);
                    if($value['dept'] == 2){
                        //外销   系数后续费率=实际续费数/（到期数*本月续费率指标）*61.5%
                        $renew_rare = ($value['realnum']/($value['daoqi']*($value['renew_point']/100)))*(0.615);

                    }else if($value['dept'] == 1){
                        //商务  系数后续费率=实际续费数/（到期数*本月续费率指标）*59%
                        $renew_rare = ($value['realnum']/($value['daoqi']*($value['renew_point']/100)))*(0.59);
                    }  
                    $renew_rare = (number_format($renew_rare*100,1,'.','')).'%';

                    //'系数后续费率',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$renew_rare);
                    //'续费率封顶值',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['renew_max']);
                    //'超出会员数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['over_vip']);
                    $j++;
                }
            }elseif($type == 15){
                $info = S("Cache:AllRenewMonthConn");
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division']);
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment']);
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage']);
                    foreach ($value['point'] as $k => $v) {
                        //财年月份数据，12条
                        $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$v);
                    }
                    //'操作时间',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lasttime']);
                    //'操作人',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 6;
                $logData['opdes']       = '导出续费月数指标数据';
                $logData['content']     = '导出续费月数指标数据';
            }elseif($type == 16){
                $info = S("Cache:AllcityFendanMzl");//生成缓存下载使用
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'部门',
                    if($value['dept'] == 1){
                        $department = '商务';
                    }elseif($value['dept'] == 2){
                        $department = '外销';
                    }
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$department);
                    //'城市重点系数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ratio']);
                    //'品师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)getSaleUserName($value['brand_division']));
                    //'品团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)getSaleUserName($value['brand_regiment']));
                    //品牌师,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)getSaleUserName($value['brand_manage']));
                    // '拓展师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)getSaleUserName($value['dev_division']));
                    // '拓展团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)getSaleUserName($value['dev_regiment']));
                    // '城市经理',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)getSaleUserName($value['dev_manage']));
                    // '实际总会员数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['real_vip_num']);
                    // '计划月分单数均值',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['jhyfds_avg']);
                    // '实际月分单数均值（全月会员）',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['sjyfds_avg_qy']);
                    // '时间进度比',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['sjjdb']);
                    // '分单满足率',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['fdmzl']);
                    // '分单总数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['fdzs']);
                    // '非全月会员数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['no_full_vip_num']);
                    // '实际月分单数均值（非全月）',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['sjyfds_avg_fqy']);
                    
                    $j++;
                }
            }elseif($type == 17){
                $info = S("Cache:AllHuiyuanFendanMzl");//生成缓存下载使用
                foreach($info as $key => $value){
                    //初始化$i
                    $i = 0;
                    //     '会员ID',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['uid']);
                    // '会员简称',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['jc']);
                    // '部门',
                    if($value['dept'] == 1){
                        $department = '商务';
                    }elseif($value['dept'] == 2){
                        $department = '外销';
                    }
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$department);
                    // '城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    // '品牌师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_division']);
                    // '品牌团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_regiment']);
                    // '品牌师',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['brand_manage']);
                    // '拓展师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_division']);
                    // '拓展团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_regiment']);
                    // '城市经理',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['dev_manage']);
                    // '会员状态',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)'-');
                    // '计划月分单数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['planFendan']);
                    // '实际月分单数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['fendan']);
                    // '当前累计合作天数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['coopDays']);
                    // '时间进度',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['timeProgress'].'%');
                    // '分单满足率',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['fdmzl']);
                    // '本月缺单数',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['byqds']);
                    // '本次合同开始',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['allstart']);
                    // '本次合同结束',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['allend']);
                    $j++;
                }
            }else{
                exit;
            }
        }else{
            $j = 1;
            if($type == 2){
                //会员指标填充基本城市
                $cityinfo = D('Citypoint')->getAllCityInManage();
                foreach($cityinfo as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'会员指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,'');
                    //'生效月份',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,'');
                    
                    $j++;
                }

                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 2;
                $logData['opdes']       = '导出城市会员指标模板';
                $logData['content']     = '导出城市会员指标模板';
            }elseif($type == 6){
                //会员指标填充基本城市
                $cityinfo = D('Citypoint')->getAllCityInManage();
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                
                foreach($cityinfo as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    //'群名称',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,'');
                    //'成员指标',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,'');
                    //实际成员数,
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,'');
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 9;
                $logData['opdes']       = '导出城市QQ群模板';
                $logData['content']     = '导出城市QQ群模板';
            }elseif($type == 15){
                //续费月数指标填充基本城市
                $cityinfo = D('Citypoint')->getAllCityInManage();
                $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                foreach($cityinfo as $key => $value){
                    //初始化$i
                    $i = 0;
                    //'城市',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['city']);
                    
                    $j++;
                }
                //写入操作日志  opid optime opip optype opdes content
                $logData['optype']      = 6;
                $logData['opdes']       = '导出续费月数指标模板';
                $logData['content']     = '导出续费月数指标模板';
            }
        }
        $log = D('Citypoint')->addLog($logData);//写入操作日志

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$name.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    /*
     * 查询城市重点系数列表
     * $pageIndex  页码
     * $pageCount  分页长度
     * $map包含以下4条：
     * $cityid     城市ID
     * $department     部门
     * $time     时间
     * $manager     操作人
    */
    private function getCityPoint($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }
        $count = D('Citypoint')->getCityPointCount($map);
        $list = D('Citypoint')->getCityPoint($map, 'c.time desc',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //查询所有
        $all = D('Citypoint')->getCityPoint($map,'c.time desc');
        foreach ($all as $k => $v) {
            $all[$k]['time'] = date('Y-m',$v['time']);
            $all[$k]['lasttime'] = date('Y-m-d',$v['lasttime']);
            //区域表 manager = 1 外销  其他为商务
            if($v['dept'] == 1){
                $all[$k]['department'] = '商务';
            }else{
                $all[$k]['department'] = '外销';
            }
        }
        S("Cache:AllCityPoint",$all,15*60);

        return $result;
    }

    //ajax修改单条城市会员指标
    public function editCityPoint()
    {
        $id         = I('post.id');
        $data       = I("post.arr");
        $result     = D('Citypoint')->editcitypoint($id,$data);

        if($result == 1){
            //写入操作日志  opid optime opip optype opdes content
            $logData['opid']        = $_SESSION['uc_userinfo']['id'];
            $logData['optime']      = time();
            $logData['opip']        = get_client_ip();
            $logData['optype']      = 2;
            $logData['opdes']       = '修改城市会员指标';
            $arr = [
                'id' => $id,
                'data' => $data
            ];
            $logData['content']     = json_encode($arr);
            $log = D('Citypoint')->addLog($logData);

            $this->ajaxReturn(array("data"=>$id,"info"=>"修改成功！","status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>"","info"=>"修改失败！","status"=>0));
        }
    }

    //城市QQ群
    public function cityQQMember()
    {
        //获取自己的管辖城市
        //$citys = D('Citypoint')->getManageCitys();

        //搜索关键词 
        $keyword['cityid'] = I("get.cityid");
        $keyword['time'] = I("get.time");

        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($keyword['cityid']) && in_array($keyword['cityid'], $ids)){
                $keyword['cityid']            = I("get.cityid");
            }else{
                $idstr = implode(',', $ids);
                $keyword['cityid'] = array('IN',$idstr); 
            }
            $list = $this->getQQmember($pageIndex, $pageCount, $keyword);
        }
        
        $keyword['cityid'] = I("get.cityid");

        $points = $list['list'];
        foreach ($points as $k => $v) {
            $points[$k]['time'] = date('Y-m-d',$v['time']);
            //区域表 manager = 1 外销  其他为商务
            if($v['dept'] == 1){
                $points[$k]['department'] = '商务';
            }else{
                $points[$k]['department'] = '外销';
            }
            if(empty($v['time'])){
                $points[$k]['time'] = '';
            }
            if($v['point'] !== null){
                $points[$k]['point'] = $v['point'];
            }
            if($v['num'] !== null){
                $points[$k]['num'] = $v['num'];
            }
        }
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$points);
        $this->assign('keyword',$keyword);
        $this->assign("citys",$citys);
        $this->display();
    }

    /*
     * 城市QQ群列表
     * $pageIndex  页码
     * $pageCount  分页长度
     * $map包含以下2条：
     * $cityid     城市ID
     * $time     时间
    */
    private function getQQmember($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);


        $map['time'] = strtotime($keyword['time']);//转化为时间戳
        $count = D('Citypoint')->getQQmemberCount($map);
        $list = D('Citypoint')->getQQmember($map, 'c.id ASC',($pageIndex-1)*$pageCount,$pageCount);

        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //查询所有
        $all = D('Citypoint')->getQQGroups($map, 'c.id ASC');
        foreach ($all as $k => $v) {
            $all[$k]['time'] = date('Y-m',$v['time']);
            //区域表 manager = 1 外销  其他为商务
            if($v['dept'] == 1){
                $all[$k]['department'] = '商务';
            }else{
                $all[$k]['department'] = '外销';
            }
            if(empty($v['time'])){
                $all[$k]['time'] = '';
            }
        }
        S("Cache:AllQQGroups",$all,15*60);
        return $result;
    }

    /*
    * 城市QQ群：批量导入QQ群信息
    *
    */
    public function uploadQQgroupExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  cityid name   point  num  time   manager 
        $nuin = '';
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }

            //判断城市是否存在
            $city = D('Citypoint')->getCityInCityManage(trim($v[0]));
            if(empty($city)){
                $unin .= $v[0].'_';
                continue;
            }

            $cid = $city['id'];//城市ID

            $data['cityid'] = $cid;
            $data['name'] = trim($v[1]);
            $data['point'] = $v[2];
            $data['num'] = $v[3];
            $data['manager'] = $_SESSION['uc_userinfo']['name'];
            $data['time'] = time();

            //查询该城市是否有QQ群信息
            $isHave = D('Citypoint')->checkCityIdsExist($cid);
            if(empty($isHave)){
                //没有这个城市QQ群，写入新的
                M('sale_qqgroup')->add($data);
            }else{
                //有这个城市QQ群，更新
                $qmap['id'] = $isHave['id'];
                M('sale_qqgroup')->where($qmap)->save($data);
            }
        }
        if(!empty($nuin)){
            $nuin = $unin.'等城市不存在，故未写入';
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 9;
        $logData['opdes']       = '批量导入城市QQ群';
        $num                    = count($excel);
        $logData['content']     = '批量导入城市QQ群'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        
        $this->ajaxReturn(array("data"=>$nuin,"info"=>"修改成功！","status"=>1));
    }

    /**
     * ajax修改单条城市QQ群
     * @param   string    $cid  城市ID
     * @param   array     $arr  QQ群名称、群成员指标数、实际群成员数
     * @return  mixed     ajax返回值
    */
    public function editQQGgroup()
    {
        $cid     = I("post.cid");
        $arr     = I("post.arr");

        $data['cityid']     = $cid;
        $data['name']       = $arr[0];
        $data['point']      = $arr[1];
        $data['num']        = $arr[2];
        $data['time']       = time();
        $data['manager']    = $_SESSION['uc_userinfo']['name'];
        //$map['cityid'] = $cid;
        $result = D('Citypoint')->editqqgroup($data);

        if(!empty($result)){
            //写入操作日志  opid optime opip optype opdes content
            $logData['opid']        = $_SESSION['uc_userinfo']['id'];
            $logData['optime']      = time();
            $logData['opip']        = get_client_ip();
            $logData['optype']      = 9;
            $logData['opdes']       = '修改城市QQ群';
            $logData['content']     = json_encode($data);
            $log = D('Citypoint')->addLog($logData);
            //$this->ajaxReturn(array("data"=>$data,"info"=>"修改成功！","status"=>1));
            $this->ajaxReturn(array("data"=>$result,"info"=>"修改成功！","status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>"","info"=>"修改失败！","status"=>0));
        }
    }

    /**
     * 部门续费月度系数
     * @param void    $_GET  参数：bm(部门)  cnyf(财年)
     * @return void
    */
    public function buMenXuFei()
    {
        $keyword['bm'] = I("get.bm");
        $keyword['cnyf'] = I("get.cnyf");
       
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $map['dept'] = $_SESSION['uc_userinfo']['department_id'];
        //部门
        if($map['dept'] == 5){
            //外销
            $keyword['bm'] = '外销';
        }elseif($map['dept'] == 6){
            //商务
            $keyword['bm'] = '商务';
        }
        $count = D('Citypoint')->getBMXFCount($keyword);
        $list = D('Citypoint')->getBMXFContent($keyword, 'id ASC',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $page =  $page->show();
        }
        foreach ($list as $k => $v) {
            $list[$k]['lasttime'] = date('Y-m-d',$v['lasttime']);
            if($v['xfxs'] !== null){
                $list[$k]['xfxs'] = number_format($v['xfxs'],2,'.','');
            } 
            if($v['xfywclxs'] !== null){
                $list[$k]['xfywclxs'] = number_format($v['xfywclxs'],2,'.','');
            } 
            //所有的率统一保存为一位小数
            $list[$k]['xflzb'] = (number_format($v['xflzb'],1,'.','')).'%';
            $list[$k]['xfyswczb'] = (number_format($v['xfyswczb'],1,'.','')).'%';
            $list[$k]['qnxfjz'] = (number_format($v['qnxfjz'],1,'.','')).'%';
            $list[$k]['xflzg'] = (number_format($v['xflzg'],1,'.','')).'%';
            $list[$k]['qnxfyswcljz'] = (number_format($v['qnxfyswcljz'],1,'.','')).'%';
            $list[$k]['xflyswczg'] = (number_format($v['xflyswczg'],1,'.','')).'%';
        }
        $this->assign('keyword',$keyword);
        $this->assign('totalnum',$count);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }

    /**
     * 部门续费月度系数：批量导入
     * @param   void 
     * @return  array  ajax返回值 
    */
    public function uploadBMXFExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  cnyf  bm  xfxs  xfywclxs  xflzb  xfyswczb  qnxfjz xflzg  qnxfyswcljz  xflyswczg  manager lasttime 
        
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }
            
            //构建数据数组
            $data['cnyf'] = $v[0];
            $data['bm'] = trim($v[1]);
            $data['xfxs'] = strval($v[2]);
            $data['xfywclxs'] = strval($v[3]);
            $data['xflzb'] = $v[4];
            $data['xfyswczb'] = $v[5];
            $data['qnxfjz'] = $v[6];
            $data['xflzg'] = $v[7];
            $data['qnxfyswcljz'] = $v[8];
            $data['xflyswczg'] = $v[9];
            $data['manager'] = $_SESSION['uc_userinfo']['name'];
            $data['lasttime'] = time();
            //查询该城市是否有QQ群信息 
            $isHave = D('Citypoint')->checkXSExist($data['cnyf'],$data['bm'],1);
            if(empty($isHave)){
                //没有这个系数，写入新的
                $ids[] = M('sale_renewpoint')->add($data);
            }else{
                //有这个系数，更新
                $where['id'] = $isHave['id'];
                M('sale_renewpoint')->where($where)->save($data);
            }
        }
        
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 7;
        $logData['opdes']       = '批量导入部门续费月度系数';
        $num                    = count($excel);
        $logData['content']     = '批量导入部门续费月度系数'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        
        $this->ajaxReturn(array("data"=>'',"info"=>"修改成功！","status"=>1));
    }

    /**
     * 中心续费月度系数
     * @param void    $_GET  参数： cnyf(财年)
     * @return void
    */
    public function zhongXinXuFei()
    {
        $keyword['cnyf'] = I("get.cnyf");
       
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $count = D('Citypoint')->getZXCount($keyword);
        $list = D('Citypoint')->getZXContent($keyword, 'id DESC',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $page =  $page->show();
        }
        foreach ($list as $k => $v) {
            $list[$k]['lasttime'] = date('Y-m-d',$v['lasttime']);
            if($v['xfxs'] !== null){
                $list[$k]['xfxs'] = number_format($v['xfxs'],1,'.','');
            } 
            if($v['xfywclxs'] !== null){
                $list[$k]['xfywclxs'] = number_format($v['xfywclxs'],1,'.','');
            }
        }
        
        $this->assign('keyword',$keyword);
        $this->assign('totalnum',$count);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }

    /**
     * 中心续费月度系数：批量导入
     * @param   void 
     * @return  array  ajax返回值 
    */
    public function uploadZXXFExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  cnyf  bm  xfxs  xfywclxs  xflzb  xfyswczb  qnxfjz xflzg  qnxfyswcljz  xflyswczg  manager lasttime 
        
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }
            
            //构建数据数组
            $data['cnyf'] = $v[0];
            $data['bm'] = $v[1];
            $data['xfxs'] = $v[2];
            $data['xfywclxs'] = $v[3];
            $data['xflzb'] = $v[4];
            $data['xfyswczb'] = $v[5];
            $data['qnxfjz'] = $v[6];
            $data['xflzg'] = $v[7];
            $data['qnxfyswcljz'] = $v[8];
            $data['xflyswczg'] = $v[9];
            $data['manager'] = $_SESSION['uc_userinfo']['name'];
            $data['lasttime'] = time();
            //查询该城市是否有QQ群信息 
            $isHave = D('Citypoint')->checkXSExist($data['cnyf'],$data['bm'],2);
            if(empty($isHave)){
                //没有这个系数，写入新的
                $ids[] = M('sale_centerpoint')->add($data);
            }else{
                //有这个系数，更新
                $where['id'] = $isHave['id'];
                M('sale_centerpoint')->where($where)->save($data);
            }
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 8;
        $logData['opdes']       = '批量导入中心续费月度系数';
        $num                    = count($excel);
        $logData['content']     = '批量导入中心续费月度系数'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        $this->ajaxReturn(array("data"=>'',"info"=>"修改成功！","status"=>1));
    }

    /**
     * 中心续费月度系数：编辑单条内容
     * @param   string  $id    中心系数ID  
     * @param   array   $arr   修改的内容数组
     * @return  array  ajax返回值 
    */
    public function editZXPoint()
    {
        $id     = I("post.id");
        $arr    = I("post.arr");
        if(!empty($id) && !empty($arr)){
            //根据ID更新中心系数
            //传入参数包括 xfxs  xfywclxs  xflzb  xfyswczb  qnxfjz  xflzg  qnxfyswcljz  xflyswczg
            $data['xfxs']           = $arr[0];
            $data['xfywclxs']       = $arr[1];
            $data['xflzb']          = $arr[2];
            $data['xfyswczb']       = $arr[3];
            $data['qnxfjz']         = $arr[4];
            $data['xflzg']          = $arr[5];
            $data['qnxfyswcljz']    = $arr[6];
            $data['xflyswczg']      = $arr[7];
            $data['manager'] = $_SESSION['uc_userinfo']['name'];
            $data['lasttime'] = time();

            $map['id'] = $id;

            $result = D("Citypoint")->editZXpoint($map, $data);
            if($result){
                //写入操作日志  opid optime opip optype opdes content
                $logData['opid']        = $_SESSION['uc_userinfo']['id'];
                $logData['optime']      = time();
                $logData['opip']        = get_client_ip();
                $logData['optype']      = 8;
                $logData['opdes']       = '修改中心续费月度系数';
                $logData['content']     = json_encode($data);
                $log = D('Citypoint')->addLog($logData);
                $this->ajaxReturn(array("data"=>'',"info"=>"修改成功！","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>'',"info"=>"写入失败，请重试！","status"=>0));
            }

            
        }else{
            $this->ajaxReturn(array("data"=>'',"info"=>"网络错误，请重试！","status"=>1));
        }
    }

    /**
     * 部门续费月度系数：编辑单条内容
     * @param   string  $id    中心系数ID  
     * @param   array   $arr   修改的内容数组
     * @return  array  ajax返回值 
    */
    public function editBMPoint()
    {
        $id     = I("post.id");
        $arr    = I("post.arr");
        if(!empty($id) && !empty($arr)){
            //根据ID更新中心系数
            //传入参数包括 xfxs  xfywclxs  xflzb  xfyswczb  qnxfjz  xflzg  qnxfyswcljz  xflyswczg
            $data['xfxs']           = $arr[0];
            $data['xfywclxs']       = $arr[1];
            $data['xflzb']          = $arr[2];
            $data['xfyswczb']       = $arr[3];
            $data['qnxfjz']         = $arr[4];
            $data['xflzg']          = $arr[5];
            $data['qnxfyswcljz']    = $arr[6];
            $data['xflyswczg']      = $arr[7];
            $data['manager'] = $_SESSION['uc_userinfo']['name'];
            $data['lasttime'] = time();

            $map['id'] = $id;

            $result = D("Citypoint")->editBMpoint($map, $data);
            if($result){
                //写入操作日志  opid optime opip optype opdes content
                $logData['opid']        = $_SESSION['uc_userinfo']['id'];
                $logData['optime']      = time();
                $logData['opip']        = get_client_ip();
                $logData['optype']      = 7;
                $logData['opdes']       = '修改部门续费月度系数';
                $logData['content']     = json_encode($data);
                $log = D('Citypoint')->addLog($logData);
                $this->ajaxReturn(array("data"=>'',"info"=>"修改成功！","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>'',"info"=>"写入失败，请重试！","status"=>0));
            }

            
        }else{
            $this->ajaxReturn(array("data"=>'',"info"=>"网络错误，请重试！","status"=>1));
        }
    }

    //城市会员到期数
    public function expire()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Citypoint')->getBrandInfo();
        //生成table的title
        //搜索关键词 
        //$keyword['cityid'] = I("get.cityid");
        //$keyword['department'] = I("get.department");
        $keyword['city']        = I("get.city");
        $keyword['department']  = I("get.department");
        $keyword['pshizhang']   = I("get.pshizhang");
        $keyword['ptuanzhang']  = I("get.ptuanzhang");
        $keyword['pinpai']      = I("get.pinpai");
        $keyword['time']        = I("get.time");
        //var_dump($keyword);
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        //获取所有城市及对应数据
        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
            $list = $this->getExpireContents($pageIndex, $pageCount, $keyword);
        }

        
        $keyword['city']        = I("get.city");
        if(empty($keyword['time'])){
            //今年
            $baseyear = date('Y',time());
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }else{
            //指定年
            $baseyear = $keyword['time'];
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$brand);
        $this->assign('t_title',$t_title);
        $this->assign("citys",$citys);
        $this->display();
    }

    /*
     * 查询到期数列表
     * $pageIndex  页码
     * $pageCount  分页长度
     * $map包含以下5条：
     *      $city            城市
     *      $department      部门
     *      $time            时间
     *      $pshizhang       品牌师长
     *      $ptuanzhang      品牌团长
     *      $pinpai          品牌师
    */
    private function getExpireContents($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $time = strtotime($map['time'].'-01');//转化为时间戳
            $map['time'] = date('Y',$time);
        }else{
            $map['time'] = date('Y',time());
        }
        $count = D('Citypoint')->getExpireCount($map);
        $list = D('Citypoint')->getExpireContents($map, 'm.id ASC',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //查询所有
        $all = D('Citypoint')->getExpireCon($map, 'm.id ASC');
        S("Cache:AllExpireConn",$all,15*60);
        return $result;
    }

    /*
    * 到期数：批量导入指标参数
    *
    */
    public function uploadDQExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  城市        系数月份
        //数据          城市名称    系数数据
        $d_title = $excel[0];
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }

            //根据城市名称，查询城市的ID（sales_city_manage）
            $cityid = D("Citypoint")->getCityManageId(trim($v[0]));

            foreach ($v as $key => $val) {
                if($key == 0){
                    continue;
                }
                $data['manage_id'] = $cityid['id'];
                $data['start'] = $d_title[$key];
                $data['module'] = 1;
                $data['point'] = $val;
                $data['uid'] = $_SESSION['uc_userinfo']['id'];
                $data['lasttime'] = date('Y-m-d',time());

                //写入数据库
                $result = D('Citypoint')->writeDQInfo($data);

                if(empty($result)){
                    $err_str .= $cityid['city'].'的'.$data['start'].'参数未写入！';
                }
            }
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 3;
        $logData['opdes']       = '批量导入到期数';
        $num                    = count($excel);
        $logData['content']     = '批量导入到期数'.$num.'条';
        $log = D('Citypoint')->addLog($logData);

        $this->ajaxReturn(array("data"=>'',"info"=>$err_str,"status"=>1));
    }

    /*
    * 实际续费数
    *
    */
    public function loanOriginationFee()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Citypoint')->getBrandInfo();
        //生成table的title
        //搜索关键词 
        //$keyword['cityid'] = I("get.cityid");
        //$keyword['department'] = I("get.department");
        $keyword['city']        = I("get.city");
        $keyword['department']  = I("get.department");
        $keyword['pshizhang']   = I("get.pshizhang");
        $keyword['ptuanzhang']  = I("get.ptuanzhang");
        $keyword['pinpai']      = I("get.pinpai");
        $keyword['time']        = I("get.time");
        //var_dump($keyword);
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
            $list = $this->getLOFContents($pageIndex, $pageCount, $keyword);
        }

        $keyword['city']        = I("get.city");

        if(empty($keyword['time'])){
            //今年
            $baseyear = date('Y',time());
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }else{
            //指定年
            $baseyear = $keyword['time'];
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$brand);
        $this->assign('t_title',$t_title);
        $this->assign("citys",$citys);
        $this->display();
    }

    //ajax修改单条续费相关指标
    public function editExpirePoint()
    {
        $id         = I('post.id');
        $arr       = I("post.arr");
        $year       = I('post.year');
        $type       = I('post.type');
        if($year == ''){
            $time = date('Y-m',strtotime('-1 month')).'-01';
        }else{
            $time = $year.'-'.date('m',strtotime('-1 month')).'-01';
        }

        $map['manage_id']   = $id;
        $map['module']      = $type;//1到期数 2实际续费数 3实际续费月数 6续费月数指标
        $map['start']       = $time;

        $data['point'] = $arr[0];
        $data['lasttime'] = date('Y-m-d',time());
        $data['uid'] = $_SESSION['uc_userinfo']['id'];
        $result = D('Citypoint')->editexpirepoint($map,$data);
        //$this->ajaxReturn(array("data"=>$data,"info"=>"修改成功！","status"=>1));
        if($result >= 1){
            //写入操作日志  opid optime opip optype opdes content
            $logData['opid']        = $_SESSION['uc_userinfo']['id'];
            $logData['optime']      = time();
            $logData['opip']        = get_client_ip();
            if($type == 1){
                $logData['optype']      = 3;
                $logData['opdes']       = '修改到期数';
            }elseif($type == 2){
                $logData['optype']      = 4;
                $logData['opdes']       = '修改实际续费数';
            }elseif($type == 3){
                $logData['optype']      = 5;
                $logData['opdes']       = '修改实际续费月数';
            }elseif($type == 6){     
                $logData['optype']      = 6;
                $logData['opdes']       = '修改续费月数指标';
            }
            $arr = array_merge($map,$data);
            $logData['content']     = json_encode($arr);
            $log = D('Citypoint')->addLog($logData);
            $this->ajaxReturn(array("data"=>$data,"info"=>"修改成功！","status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>"","info"=>"修改失败！","status"=>0));
        }
    }

    /*
     * 查询到期数列表
     * $pageIndex  页码
     * $pageCount  分页长度
     * $map包含以下5条：
     *      $city            城市
     *      $department      部门
     *      $time            时间
     *      $pshizhang       品牌师长
     *      $ptuanzhang      品牌团长
     *      $pinpai          品牌师
    */
    private function getLOFContents($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $time = strtotime($map['time'].'-01');//转化为时间戳
            $map['time'] = date('Y',$time);
        }else{
            $map['time'] = date('Y',time());
        }
        $count = D('Citypoint')->getLOFCount($map);
        $list = D('Citypoint')->getLOFContents($map, 'm.id ASC',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //查询所有
        $all = D('Citypoint')->getLOFCon($map, 'm.id ASC');
        S("Cache:AllLOFConn",$all,15*60);
        return $result;
    }

    /*
    * 实际续费数：批量导入指标参数
    *
    */
    public function uploadLOFExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  城市        系数月份
        //数据          城市名称    系数数据
        $d_title = $excel[0];
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }

            //根据城市名称，查询城市的ID（sales_city_manage）
            $city = trim($v[0]);
            $cityid = D("Citypoint")->getCityManageId($city);

            foreach ($v as $key => $val) {
                if($key == 0){
                    continue;
                }
                $data['manage_id'] = $cityid['id'];
                $data['start'] = $d_title[$key];
                $data['module'] = 2;
                $data['point'] = $val;
                $data['uid'] = $_SESSION['uc_userinfo']['id'];
                $data['lasttime'] = date('Y-m-d',time());

                //写入数据库
                $result = D('Citypoint')->writeDQInfo($data);

                if(empty($result)){
                    $err_str .= $cityid['city'].'的'.$data['start'].'参数未写入！';
                }
            }
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 4;
        $logData['opdes']       = '批量导入实际续费数';
        $num                    = count($excel);
        $logData['content']     = '批量导入实际续费数'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        
        $this->ajaxReturn(array("data"=>'',"info"=>$err_str,"status"=>1));
    }

    /*
    * 实际续费月数
    *
    */
    public function lofMonth()
    {
        //获取自己的管辖城市
        $citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Citypoint')->getBrandInfo();
        //生成table的title
        //搜索关键词 
        //$keyword['cityid'] = I("get.cityid");
        //$keyword['department'] = I("get.department");
        $keyword['city']        = I("get.city");
        $keyword['department']  = I("get.department");
        $keyword['pshizhang']   = I("get.pshizhang");
        $keyword['ptuanzhang']  = I("get.ptuanzhang");
        $keyword['pinpai']      = I("get.pinpai");
        $keyword['time']        = I("get.time");
        //var_dump($keyword);
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
            $list = $this->getLOFMonthContents($pageIndex, $pageCount, $keyword);
        }
        
        $keyword['city']        = I("get.city");
        if(empty($keyword['time'])){
            //今年
            $baseyear = date('Y',time());
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }else{
            //指定年
            $baseyear = $keyword['time'];
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$brand);
        $this->assign('t_title',$t_title);
        $this->assign("citys",$citys);
        $this->display();
    }

    /*
     * 查询实际续费月数列表
     * $pageIndex  页码
     * $pageCount  分页长度
     * $map包含以下5条：
     *      $city            城市
     *      $department      部门
     *      $time            时间
     *      $pshizhang       品牌师长
     *      $ptuanzhang      品牌团长
     *      $pinpai          品牌师
    */
    private function getLOFMonthContents($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $time = strtotime($map['time'].'-01');//转化为时间戳
            $map['time'] = date('Y',$time);
        }else{
            $map['time'] = date('Y',time());
        }
        $count = D('Citypoint')->getLOFMonthCount($map);
        $list = D('Citypoint')->getLOFMonthContents($map, 'm.id ASC',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //查询所有
        $all = D('Citypoint')->getLOFMonthCon($map, 'm.id ASC');
        S("Cache:AllLOFMonthConn",$all,15*60);
        return $result;
    }
    
    /*
    * 实际续费月数：批量导入指标参数
    *
    */
    public function uploadLOFMonthExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  城市        系数月份
        //数据          城市名称    系数数据
        $d_title = $excel[0];
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }

            //根据城市名称，查询城市的ID（sales_city_manage）
            $city = trim($v[0]);
            $cityid = D("Citypoint")->getCityManageId($city);

            foreach ($v as $key => $val) {
                if($key == 0){
                    continue;
                }
                $data['manage_id'] = $cityid['id'];
                $data['start'] = $d_title[$key];
                $data['module'] = 3;
                $data['point'] = $val;
                $data['uid'] = $_SESSION['uc_userinfo']['id'];
                $data['lasttime'] = date('Y-m-d',time());

                //写入数据库
                $result = D('Citypoint')->writeDQInfo($data);

                if(empty($result)){
                    $err_str .= $cityid['city'].'的'.$data['start'].'参数未写入！';
                }
            }
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 5;
        $logData['opdes']       = '批量导入实际续费月数';
        $num                    = count($excel);
        $logData['content']     = '批量导入实际续费月数'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        
        $this->ajaxReturn(array("data"=>'',"info"=>$err_str,"status"=>1));
    }

    /*
    * 续费月数指标：
    *
    */
    public function renewMonthPoint()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Citypoint')->getBrandInfo();
        //生成table的title
        //搜索关键词 
        //$keyword['cityid'] = I("get.cityid");
        //$keyword['department'] = I("get.department");
        $keyword['city']        = I("get.city");
        $keyword['department']  = I("get.department");
        $keyword['pshizhang']   = I("get.pshizhang");
        $keyword['ptuanzhang']  = I("get.ptuanzhang");
        $keyword['pinpai']      = I("get.pinpai");
        $keyword['time']        = I("get.time");
        //var_dump($keyword);
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
            //获取所有城市及对应数据
            $list = $this->getRenewMonthPoint($pageIndex, $pageCount, $keyword);
        }
        
        $keyword['city']        = I("get.city");
        if(empty($keyword['time'])){
            //今年
            $baseyear = date('Y',time());
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }else{
            //指定年
            $baseyear = $keyword['time'];
            $nextyear = $baseyear + 1;
            $t_title = [
                    '城市',
                    '部门',
                    '品师长',
                    '品团长',
                    '品牌师',
                    "$baseyear-02",
                    "$baseyear-03",
                    "$baseyear-04",
                    "$baseyear-05",
                    "$baseyear-06",
                    "$baseyear-07",
                    "$baseyear-08",
                    "$baseyear-09",
                    "$baseyear-10",
                    "$baseyear-11",
                    "$baseyear-12",
                    "$nextyear-01",
                    '操作时间',
                    '操作人',
                    '操作'
            ];
        }
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$brand);
        $this->assign('t_title',$t_title);
        $this->assign("citys",$citys);
        $this->display();
    }

    /*
     * 查询续费月数指标列表
     * $pageIndex  页码
     * $pageCount  分页长度
     * $map包含以下5条：
     *      $city            城市
     *      $department      部门
     *      $time            时间
     *      $pshizhang       品牌师长
     *      $ptuanzhang      品牌团长
     *      $pinpai          品牌师
    */
    private function getRenewMonthPoint($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $time = strtotime($map['time'].'-01');//转化为时间戳
            $map['time'] = date('Y',$time);
        }else{
            $map['time'] = date('Y',time());
        }
        $count = D('Citypoint')->getLOFMonthCount($map);//总条数
        $list = D('Citypoint')->getRenewMonthPointContents($map, 'm.id ASC',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //查询所有
        $all = D('Citypoint')->getRenewMonthPointCon($map, 'm.id ASC');
        S("Cache:AllRenewMonthConn",$all,15*60);
        return $result;
    }

    /*
    * 续费月数指标：批量导入续费月数指标参数
    *
    */
    public function uploadRenewMonthExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  城市        系数月份
        //数据          城市名称    系数数据
        $d_title = $excel[0];
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }

            //根据城市名称，查询城市的ID（sales_city_manage）
            $city = trim($v[0]);
            $cityid = D("Citypoint")->getCityManageId($city);

            foreach ($v as $key => $val) {
                if($key == 0){
                    continue;
                }
                $data['manage_id'] = $cityid['id'];
                $data['start'] = $d_title[$key];
                $data['module'] = 6;
                $data['point'] = $val;
                $data['uid'] = $_SESSION['uc_userinfo']['id'];
                $data['lasttime'] = date('Y-m-d',time());

                //写入数据库
                $result = D('Citypoint')->writeDQInfo($data);

                if(empty($result)){
                    $err_str .= $cityid['city'].'的'.$data['start'].'参数未写入！';
                }
            }
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 6;
        $logData['opdes']       = '批量导入续费月数指标';
        $num                    = count($excel);
        $logData['content']     = '批量导入续费月数指标'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        
        $this->ajaxReturn(array("data"=>'',"info"=>$err_str,"status"=>1));
    }

    /**
      * [getHistoryLog 获取最近操作记录]
      * @return [type] [description]
      */
    public function getHistoryLog(){
        $type = I('post.type');
        if (!empty($type)) {
            $result = D('Citypoint')->getHistoryLog($type);
            if (!empty($result)) {
                $this->assign('list',$result);
                $html = $this->fetch("historylog");
                $this->ajaxReturn(array('data'=>$html,'info'=>'操作成功！','status'=>1));
            } else {
                $this->ajaxReturn(array('data'=>'','info'=>'最近操作记录为空！','status'=>0));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'获取失败！','status'=>0));
    }

    /*
    * 权限设置
    *
    */
    public function xsqxsz()
    {
        $roleName = $_SESSION['uc_userinfo']['role_name'];
        //$roleName = '商务助理';
        $role = 0;
        if($roleName == '商务助理'){
            $role = 1;
        }elseif($roleName == '外销助理'){
            $role = 2;
        }
        //查询所有城市
        //获取所有城市列表       
        $citys = D('SaleSetting')->getManageCitys($role);
        //foreach ($citys as $key => $value) {
        //    $indexed = substr($value['bm'],0,1);
        //    $newCity[$indexed][] = array('cid' => $value['cid'],'cname' => $value['cname'],'oldname' => $value['oldname']);
        //}
        //unset($citys);
        //var_dump($citys);
        //查询所有城市
        /*$cityarr = D("Quyu")->getAllQuyuOnly();
        foreach ($cityarr as $key => $value) {
            $cityarr[$key]['cname'] = strtoupper($value['bm']['0']).' '.$value['cname'];
        }
        $info['citys'] = $cityarr;
        $str = '';
        foreach ($info['citys'] as $k => $v) {
            $str .= '<option value="'.$v['cid'].'">'.$v['cname'].'</option>';
        }
        var_dump($info['citys']);

        $this->assign("info",$info['citys']);*/


        $this->assign('citys',$citys);
        $this->assign('role',$role);
        $this->display();
    }

    /*
    * 写入新权限
    *
    */
    public function editcitymanager()
    {
        //角色数组---》对应数据库字段
        $roles = [
            0 => 'corps',
            1 => 'assistant',
            2 => 'dev_division',
            3 => 'brand_division',
            4 => 'dev_regiment',
            5 => 'brand_regiment',
            6 => 'brand_manage',
            7 => 'dev_manage'
        ];

        $userid = I("post.name");
        $department = I("post.department");//1商务  2外销  3营销中心
        $roleName = I("post.roleName");//职位
        $subordinate = I("post.subordinate");
        $checkedcitys = I("post.checkedcitys");
        if(empty($userid[0])){
            return $this->ajaxReturn(array("data"=>'','info'=>'请添加一个用户！',"status"=>0));
        }
        if(empty($department)){
            return $this->ajaxReturn(array("data"=>'','info'=>'你需要选择一个部门！',"status"=>0));
        }

        $roleName = substr($roleName, 0, -1);
        $roleArr =  explode(',' , $roleName);//角色数组
        $role = min($roleArr);//取角色的最小值
        if($roleName != ''){
            //编辑下属职位城市
            if(!empty($subordinate)){
                $subordinate = substr($subordinate, 0, -1);
                //$subArr =  explode(',' , $subordinate);//选择的下属数组
                //根据角色，判断要取的字段 $field
                if($role == 0){
                    $field = $roles[0];
                    $wherestr = $roles[2]." in ($subordinate) or ".$roles[3]." in ($subordinate)"; //一个查询条件
                }elseif($role == 1){
                    $field = $roles[1];
                    $wherestr = $roles[2]." in ($subordinate) or ".$roles[3]." in ($subordinate)";
                }elseif($role == 2){
                    $field = $roles[2];
                    $wherestr = $roles[4]." in ($subordinate)";
                }elseif($role == 3){
                    $field = $roles[3];
                    $wherestr = $roles[5]." in ($subordinate)";
                }elseif($role == 4){
                    $field = $roles[4];
                    $wherestr = $roles[7]." in ($subordinate)";
                }elseif($role == 5){
                    $field = $roles[5];
                    $wherestr = $roles[6]." in ($subordinate)";
                }
                if(!empty($field)){
                    $data[$field] = $userid[0];
                    $wherestr = '('.$wherestr.')'.' and dept = '.$department;
                    //var_dump($wherestr);
                    $result = D('SaleSetting')->addcitymanagers($wherestr,$data);//写入选择的下属
                    //var_dump(M()->getLastSql());
                    //die;
                }
            }

            //编辑直接添加的城市
            if(!empty($checkedcitys)){
                $checkedcitys = substr($checkedcitys, 0, -1);
                //$cityArr =  explode(',' , $checkedcitys);//选择的下属数组
                //根据角色，判断要取的字段 $field
                $field = $roles[$role];

                $data[$field] = $userid[0];
                $where['id'] = array('IN',$checkedcitys); 
                $result = D('SaleSetting')->addcitymanagers($where,$data);//写入直接选的城市
                //var_dump(M()->getLastSql());
                //die;   
            }
            
        }else{
            return $this->ajaxReturn(array("data"=>$roleName,'info'=>'请选择一个职位！',"status"=>0));
        }

        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 1;
        $logData['opdes']       = '修改'.getSaleUserName($userid[0]).'的权限数据';
        $logData['content']     = '修改'.getSaleUserName($userid[0]).'的权限数据';
        $log = D('Citypoint')->addLog($logData);


        return $this->ajaxReturn(array("data"=>$result,'info'=>'操作成功！',"status"=>1));
    }

    /*
    * 根据输入内容，查找adminuser
    *
    */
    public function findUsers()
    {
        if ($_POST) {
            $query = I("post.query");
            $result = D("SaleSetting")->findUsers($query);
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));
        }
    }

    /*
    * 根据传入的角色查询下属及城市
    *
    */
    public function getManageUser()
    {
        //角色数组---》对应数据库字段
        $roles = [
            0 => 'corps',
            1 => 'assistant',
            2 => 'dev_division',
            3 => 'brand_division',
            4 => 'dev_regiment',
            5 => 'brand_regiment',
            6 => 'brand_manage',
            7 => 'dev_manage'      
        ];
        $roles_name = [
            'corps'             => '军长',
            'assistant'         => '助理',
            'dev_division'      => '拓展师长',
            'brand_division'    => '品牌师长',
            'dev_regiment'      => '拓展团长',
            'brand_regiment'    => '品牌团长',
            'brand_manage'      => '品牌师',
            'dev_manage'        => '城市经理'
        ];
        if ($_POST) {
            $department = I("post.department");//1商务  2外销  3营销中心
            $roleName = I("post.roleName");

            //首先，判断部门  如果department = 3 查全部城市
            if($department < 3){
                $map['dept'] = array('EQ',$department);
            }
            if(!empty($roleName)){
                $roleName = substr($roleName, 0, -1);
                $roleArr =  explode(',' , $roleName);//角色数组
                $role = min($roleArr);//取角色的最小值
                //根据角色，判断要取的字段 $field
                if($role == 0){
                    $field = $roles[2].','.$roles[3];
                    $field1 = $roles[4].','.$roles[5];//没有师长时，查团长
                    $field2 = $roles[6].','.$roles[7];//没有团长时，查城市经理
                }elseif($role == 1){
                    $field = $roles[2].','.$roles[3];//助理暂时返回师长角色
                    $field1 = $roles[4].','.$roles[5];//没有师长时，查团长
                    $field2 = $roles[6].','.$roles[7];//没有团长时，查城市经理
                }elseif($role == 2){
                    $field = $roles[4];
                    $field1 = $roles[7];//没有拓展团长时，查城市经理
                }elseif($role == 3){
                    $field = $roles[5];
                    $field1 = $roles[6];//没有品牌团长时，查品牌师
                }elseif($role == 4){
                    $field = $roles[7];
                }elseif($role == 5){
                    $field = $roles[6];
                }elseif($role == 7  || $role == 6){
                    $field = "";
                }
                //$manage_role = $roles[$roleId];//角色对应的数据库字段
                /*foreach ($roleArr as $k => $v) {
                    $field[] = $roles[$v];//要查询的字段
                }*/

                if(!empty($field)){
                    $result = D("SaleSetting")->getManageUser($map,$field);
                    
                    foreach ($result as $k => $v) {
                        foreach ($v as $key => $value) {
                            if($value != 0){
                                $data[$key][] = $value;
                            }
                        }
                    }
                    if($data == null && $field1 != null){
                        $result = D("SaleSetting")->getManageUser($map,$field1);
                        foreach ($result as $k => $v) {
                            foreach ($v as $key => $value) {
                                if($value != 0){
                                    $data[$key][] = $value;
                                }
                            }
                        }
                    }
                    if($data == null  && $field2 != null){
                        $result = D("SaleSetting")->getManageUser($map,$field2);
                        foreach ($result as $k => $v) {
                            foreach ($v as $key => $value) {
                                if($value != 0){
                                    $data[$key][] = $value;
                                }
                            }
                        }
                    }
                    foreach ($data as $k => $v) {
                        $data_p[$k] = array_unique($v);
                    }
                    $i = 0;
                    foreach ($data_p as $k => $v) {
                        $data_f[$i]['zhiwei'] = $roles_name[$k];
                        foreach ($v as $key => $value) {
                            $names[$value] = getSaleUserName($value);
                        }
                        $data_f[$i]['names'] = $names;
                        unset($names);
                        $i++;
                    }
                    //拼接选择组件
                    //<label class="checkbox-inline pinpais"><input type="checkbox" id="inlineCheckbox1" value="option1">拓展团长-徐文翔</label>
                    $str = '';
                    foreach ($data_f as $k => $v) {
                        foreach ($v['names'] as $key => $value) {
                            if($value != null){
                                $str .= '<label class="checkbox-inline pinpais"><input type="checkbox" value="'.$key.'" name="subordinate">'.$v['zhiwei'].'-'.$value.'</label>';
                            }  
                        }
                    }          
                }
                return $this->ajaxReturn(array("data"=>$str,'info'=>'success',"status"=>1));
                /*foreach ($result as $k => $v) {
                    foreach ($v as $key => $value) {
                        $data[$key][] = $value;
                    }
                }
                foreach ($data as $k => $v) {
                    $data_p[$k] = array_unique($v);
                }
                $i = 0;
                foreach ($data_p as $k => $v) {
                    $data_f[$i]['zhiwei'] = $roles_name[$k];
                    foreach ($v as $key => $value) {
                        $names[$value] = getSaleUserName($value);
                    }
                    $data_f[$i]['names'] = $names;
                    $i++;
                }
                //拼接选择组件
                //<label class="checkbox-inline pinpais"><input type="checkbox" id="inlineCheckbox1" value="option1">拓展团长-徐文翔</label>
                $str = '';
                foreach ($data_f as $k => $v) {
                    foreach ($v['names'] as $key => $value) {
                        $str .= '<label class="checkbox-inline pinpais"><input type="checkbox" value="'.$key.'" name="subordinate">'.$v['zhiwei'].'-'.$value.'</label>';
                    }
                }
                return $this->ajaxReturn(array("data"=>$str,'info'=>'success',"status"=>1));*/
            }else{
                return $this->ajaxReturn(array("data"=>$result,'info'=>'未获取到相关职位信息，请重试！',"status"=>0));
            }  
        }else{
            return $this->ajaxReturn(array("data"=>$result,'info'=>'未获取到相关职位信息，请重试！',"status"=>0));
        }
    }

    /*
    * 添加/编辑城市基本信息
    *
    */
    public function setCityInfo()
    {
        $citys = D('SaleSetting')->getAllCitys();//超级管理员及销售司令能查看所有城市
        $this->assign('citys',$citys);
        $this->display();
    }

    /*
    * 写入新的城市信息
    *
    */
    public function addNewCity()
    {
        $act = I("post.act");
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 1;
        if($act == 1){
            //写入新城市
            $citystr = I("post.citys");
            if($citystr != ''){
                $citys = explode(',', $citystr);
            }
            if(!empty($citys)){
                $result = D('SaleSetting')->addNewCity($citys);
            }
            if($result){

                $logData['opdes']       = '添加新城市数据';
                $logData['content']     = '添加新城市数据';
                $log = D('Citypoint')->addLog($logData);
                return $this->ajaxReturn(array("data"=>$result,"status"=>1));
            }else{
                return $this->ajaxReturn(array("data"=>$result,"status"=>0));
            }
            
        }else{
            //更新城市信息
            $citys = I("post.checkedcitys");
            $citys = implode(',', $citys);
            $where['id'] = array('IN',$citys);
            //$data['level'] = I("post.level");
            $data['dept'] = I("post.dept");
            $data['open_status'] = I("post.open_status");
            
            $result = D('SaleSetting')->editCity($where,$data);
            $logData['opdes']       = '修改城市数据';
            $logData['content']     = '修改城市数据';
            $log = D('Citypoint')->addLog($logData);
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));
            
        }
    }

    /*
    * 重置所有城市管理权限
    */
    public function resetAllManager()
    {
        $name = I("post.name");
        $result = D('SaleSetting')->resetAllManager($name[0]);
        $this->ajaxReturn(array('data'=>$result,'info'=>'修改成功！','success'=>1));
    }

    /*
    * 城市排序
    *  默认为当前最大的排序值+1
    */
    public function orderNewPaixu()
    {
        $id = I('post.id');
        $type = I('post.type');
        $result = D('SaleSetting')->updatePaixu($id,$type);
        if($result){
            $this->ajaxReturn(array('data'=>$result,'info'=>'修改成功！','success'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'修改失败！','success'=>0));
        }
    }


    /*
     * 新销售：导入历史数据，上传表格
     * 
    */
    public function exportHistoryData()
    {
        $this->display();
    }

    /*
    * 城市会员指标：批量导入指标参数
    *
    */
    public function uploadHistoryData(){
        ini_set('memory_limit','1000M');

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  cityid   point  time  lasttime  manager status
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;
            }

            //判断城市是否存在
            $city = D('SalesSetting')->getCityInCityManage(trim($v[0]));
            if(empty($city)){
                continue;
            }


            //城市会员数完成率
            /*$data['cid']        = $city['id'];  //'城市ID',
            $data['cs']         = $city['cid'];  //'城市区域编码',
            $data['csxs']       = trim($v[2]);  //'城市系数',
            $data['hyzb']       = trim($v[9]);  //会员指标',
            $data['vipcnt']     = trim($v[10]);  //实际总会员数',
            $data['hywcl']      = round((trim($v[11]))*100,6);  //会员数完成率',
            $data['doublecnt']  = trim($v[12]);  //多倍会员数',
            $data['zhanlue']    = trim($v[13]);  //战略会员数',
            $data['pause']      = trim($v[14]);  //暂停会员数',
            $data['refund']     = trim($v[15]);  //退费会员数',
            $data['qqname']     = trim($v[16]);  //城市QQ群名称',
            $data['qqpoint']    = trim($v[17]);  //实际群成员数',
            $data['qqwcl']      = trim($v[18]);  //成员数完成率',
            $data['days']       = '10';  //天数',
            $data['month']      = '2';  //月份',
            $data['year']       = '2018';  //年份',*/

            //城市分单量满足率 
            
            $data['city_id'] =  $city['id'];//'城市ID',
            $data['cs'] =  $city['cid'];//'城市区域编码',
            $data['city_xs'] =  trim($v[2]);//'城市系数',
            $data['real_vip_num'] = trim($v[9]);//'实际总会员数',
            $data['jhyfds_avg'] = trim($v[10]);//'计划月分单数均值',
            $data['sjyfds_avg_qy'] =  trim($v[11]);//'实际月分单数均值（全月会员）',
            $data['sjjdb'] =  100;//'时间进度比',
            $data['fdmzl'] =  (trim($v[13]))*100;//'分单满足率',
            $data['fdzs'] =  trim($v[14]);//'分单总数',
            $data['no_full_vip_num'] = trim($v[15]);//'非全月会员数',
            $data['sjyfds_avg_fqy'] =  trim($v[16]);//'实际月分单数均值（非全月）',
            $data['days'] =  10;//'天数',
            $data['month'] =  1;//'月份',
            $data['year'] =  2018;//'年份',
            /*$data['cid'] = $city['id'];
            $data['dept'] = 1;//'部门 1商务 2外销',
            $data['vipnum'] = trim($v[9]);  //'城市总会员数',
            $data['jhfds'] = trim($v[10]);//'城市计划分单数',
            $data['fdl'] = ceil($data['vipnum']*$v[11]); //'实际分单量',
            $data['fds5'] ='';  //'5日分单数',
            $data['fds10'] =''; //10日分单数',
            $data['fds15'] =''; //'15日分单数',
            $data['fds20'] =''; //'20日分单数',
            $data['fds25'] =''; //'25日分单数',
            $data['fds30'] =''; //'30日分单数',
            $isenough = trim($v[13]);
            if($isenough < 1){
                $data['isenough'] = 0;//'城市分单是否足够 1足够 0不够',
            }else{
                $data['isenough'] = 1;
            }
            $data['notenough'] = ''; //'城市分单不足会员数',
            $data['month'] = '02'; //'月份',
            $data['year'] = '2018'; //'年份',*/

            //续费月数完成率数据
            /*$data['dept'] = 1;//'部门   1商务  2外销',
            $data['city'] = $city['city'];//'城市名称',
            $data['cid'] = $city['cid'];//'对应quyu表城市ID',
            $data['brand_division'] = $city['brand_division'];//'品牌师长',
            $data['brand_regiment'] = $city['brand_regiment'];//'品牌团长',
            $data['brand_manage'] = $city['brand_manage'];//'品牌师',
            $data['daoqi'] = trim($v[5]);//'到期数',
            $data['renew_month_point'] = trim($v[6]);//'续费月数指标',
            $data['renew_month'] = trim($v[7]);//'实际续费月数',
            $data['renew_month_rate'] = (round(trim($v[8])*100,6)).'%';//'续费月数完成率',
            $data['renew_month_rate_point'] = (round(trim($v[9])*100,6)).'%';// '续费月数完成率指标',
            $data['renew_month_rate_complete'] = (round(trim($v[10])*100,6)).'%';//续费月数完成率达成',
            $data['renew_year_value'] = (round(trim($v[11])*100,6)).'%';//全年续费月数完成率均值',
            $data['renew_monthly_rate'] = (round(trim($v[12])*100,6)).'%';//系数后续费月数完成率',
            $data['renew_max'] = '';//续费月数完成率封顶值',
            $data['over_month'] = ((round(trim($v[8])*100,6))-(round(trim($v[9])*100,6)))*(trim($v[6]));//'超出月数:(续费月数完成率-续费月数完成率指标)*续费月数指标，≤0的情况下均为0
            if($data['over_month'] == 0 || $data['over_month'] == '-0'){
                $data['over_month'] = 0;
            }
            $data['time'] = 1517414400;//数据对应月份',*/


            //城市会员合作时长、新签合作时长、续费合作时长
            /*$data['type'] = 3;// '数据类型  1会员合作时长  2新签合作时长 3续费合作时长',
            $data['dept'] = 1;// '部门   1商务  2外销',
            $data['city'] = $city['city'];// '城市名称',
            $data['cid'] = $city['cid'];// '对应quyu表城市ID',
            $data['brand_division'] = $city['brand_division'];// '品牌师长',
            $data['brand_regiment'] = $city['brand_regiment'];// '品牌团长',
            $data['brand_manage'] = $city['brand_manage'];// '品牌师',
            $data['dev_division'] = $city['dev_division'];// '拓展师长',
            $data['dev_regiment'] = $city['dev_regiment'];// '拓展团长',
            $data['dev_manage'] = $city['dev_manage'];// '拓展城市经理',
            $data['realvipnum'] = trim($v[5]);// '真实会员数（包含多倍）',
            $data['gtyear'] = trim($v[6]);// '会员数>=1年（合作时长>一年）',
            $data['gtyear_percent'] = (round((trim($v[7]))*100,6)).'%';// '会员时长大于一年占比',
            $data['gthalfyear'] = trim($v[8]);// '合作时长大于半年小于一年的会员数',
            $data['gthalfyear_percent'] = (round((trim($v[9]))*100,6)).'%';// '时长小于一年大于半年的会员数',
            $data['lthalfyear'] = trim($v[10]);// '时长大于一季度小于半年的会员数',
            $data['lthalfyear_percent'] = (round((trim($v[11]))*100,6)).'%';// '时长大于一季度小于半年的会员占比',
            $data['ltmonth'] = trim($v[12]);// '会员时长小于一季度的会员数',
            $data['ltmonth_percent'] = (round((trim($v[13]))*100,6)).'%';// '时长小于一季度的会员数占比',
            $data['time'] = 1517414400;// '数据对应月份',2018-02*/

            //更新城市续费率
            /*$data['dept'] = 1;//'部门   1商务  2外销',
            $data['city'] = $city['city'];//'城市名称',
            $data['cid'] = $city['cid'];//'对应quyu表城市ID',
            $data['ratio'] = $city['ratio'];//'重点系数',
            $data['brand_division'] = $city['brand_division'];//'品牌师长',
            $data['brand_regiment'] = $city['brand_regiment'];//'品牌团长',
            $data['brand_manage'] = $city['brand_manage'];//'品牌师',
            $data['dev_division'] = $city['dev_division'];//'拓展师长',
            $data['dev_regiment'] = $city['dev_regiment'];//'拓展团长',
            $data['dev_manage'] = $city['dev_manage'];//'拓展城市经理',
            $data['daoqi'] = trim($v[9]);//'到期数',
            $data['realnum'] = trim($v[10]);//'实际续费数',
            $data['renew_percent'] = (round((trim($v[11]))*100,6)).'%';//'续费率',
            $data['renew_point'] = (round((trim($v[12]))*100,6)).'%';//'续费率指标',
            $data['renew_compass'] = (round((trim($v[13]))*100,6)).'%';//'续费率达成',
            $data['year_renew_num'] = (round((trim($v[14]))*100,6)).'%';//'全年续费率均值',
            $data['renew_rare'] = (round((trim($v[15]))*100,6)).'%';//'系数后续费率',
            $data['renew_max'] = '';//'续费率封顶值',
            $data['over_vip'] = (($data['renew_percent']-$data['renew_point'])*$data['daoqi'])/100;//'超出会员数:（续费率-续费率指标）*到期数，≤0的情况下均为0
            if($data['over_vip'] < 0 || $data['over_vip'] == '-0'){
                $data['over_vip'] = 0;
            }
            $data['time'] = '1517414400';//'数据对应月份',2018-01*/

            //$result[] = $data;
            // $where['time'] = 1514736000;//2017-12
            // $where['dept'] = 1;
            // $where['city'] = $city['city'];
            // $result = M('sale_cityrenew_months')->where($where)->save($data);
            $result = M('sales_csfdlmzl')->add($data);
        }
        
        $this->ajaxReturn(array("data"=>$result,"info"=>"修改成功！","status"=>1));
    }

}