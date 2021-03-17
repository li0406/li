<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  销售分析系统首页
*/
class SaleshowController extends HomeBaseController
{
    //会员 发展统计
    public function hyfz() {
    	//返回城市
    	if(count($this->city) > 0){
            $citys = getUserCitys(false);
            $this->assign("citys",$citys);
        }

        $time = "2016-08";
        $starttime = strtotime(date('Y-m-01 H:i:s', strtotime($time)));
        $endtime = strtotime(date('Y-m-d  H:i:s', strtotime(date('Y-m-01', strtotime($time)) . ' +1 month -1 day')));
        //新增多倍会员
        $logDoubleVip = D("User")->getDoubleUsersFromLog($starttime,$endtime);
        //到期会员列表
        $logEndVip = D("User")->getEndVipFromLog($starttime,$endtime);
        //var_dump($logEndVip);
        //查询所有城市，以城市为基础，获取所有对应数据
        $quyu = D("quyu")->getAllCitysForCount();
        foreach ($quyu as $k => $v) {
        	//查询重点系数
        	$citypoint = D("SaleShow")->getCityPointByCid($v['cid'],4);
        	$quyu[$k]['citypoint'] = $citypoint[0]["citypoint"];

        	//查询会员指标
        	//$huiyuanzhibiao = D("SaleShow")->getCityPointByCid($v['cid'],2);
        	//$quyu[$k]['hyzb'] = $huiyuanzhibiao[0]["citypoint"];

        	//部门：师长   经理
            $department = D("SaleShow")->getCityDepartment($v['cid']);
            $quyu[$k]['jingli'] = $department[0]['quyujingli'];
            $quyu[$k]['shizhang'] = $department[0]['shizhang'];
            if($department[0]['type'] == 1) {
                $quyu[$k]['department'] = '商务';
            }elseif($department[0]['type'] == 5) {
                $quyu[$k]['department'] = '外销';
            }elseif($department[0]['type'] == 6) {
                $quyu[$k]['department'] = '客服';
            }
        	
        	//实际会员数 qz_log_user_real_company.vip_count
        	//会员总数   qz_log_user_real_company.vip_num
            $time = "2016-08";
            $realVip = D('SaleShow')->getUserRealCompany($v['cid'],$time);
            if(empty($realVip)){
                $quyu[$k]['vip_count'] = 0;
                $quyu[$k]['vip_num'] = 0;
            }else{
                $quyu[$k]['vip_count'] = $realVip[0]['vip_count'];
                $quyu[$k]['vip_num'] = $realVip[0]['vip_num'];
            }
        	//会员完成率
            //$quyu[$k]['wanchenglv'] = ($quyu[$k]['vip_num']/$quyu[$k]['hyzb'])*100.'%';

        	//多倍会员 ：1，查询当前会员到期时间，在查询时间内的（开始时间小于查询时间）  2，查log_vip表，所有统计时间之后停止的多倍->查询会员开始结束时间（开始时间小于查询时间的）
            $doubleNow = D("User")->getDoubleUsersByCid($v['cid']);
            $quyu[$k]['doublevip']= count($doubleNow);

            foreach ($logDoubleVip as $key => $value) {
                if($value['cs'] == $v['cid']){
                    $quyu[$k]['doublevip'] = $quyu[$k]['doublevip'] + 1;
                }
            }
            
        	//到期会员
            foreach ($logEndVip as $key => $val) {
                if($val['cs'] == $v['cid']){
                    $quyu[$k]['endvip'] = $quyu[$k]['endvip'] + 1;
                }
            }

            //续费会员
        	//暂停会员暂无法统计
        	//退费会员暂无法统计
        }
        //var_dump($quyu);

    	//$this->assign("citys",$citys);
        $this->display();
    }

    //多倍会员统计
    public function dbhytj() {

        //是否传入时间参数
        //$time = I('get.time');
        if(!empty($time)){
            $time = "2016-08";
            $starttime = strtotime(date('Y-m-01 H:i:s', strtotime($time)));
            $endtime = strtotime(date('Y-m-d  H:i:s', strtotime(date('Y-m-01', strtotime($time)) . ' +1 month -1 day')));
        }else{
            $starttime = strtotime(date('Y-m-01 H:i:s', time()));
            $endtime = strtotime(date('Y-m-d  H:i:s', strtotime(date('Y-m-01', time()) . ' +1 month -1 day')));
        }

        //查询当前有效的多倍会员（开始时间在查询月之前）
        $doublevip = D("User")->getAllDoubleVip('',$starttime,$endtime);
        
        //查询log_vip，所有统计时间之后停止的多倍->查询会员开始结束时间（开始时间小于查询时间的）
        
        //公司编号      ：$doublevip[]['id']
        //会员名称      : $doublevip[]['user']
        //倍数          : $doublevip[]['viptype']
        //超出数        : ($doublevip[]['viptype'] - 1) 减一倍基础，即为多倍超出数
        //城市          : $doublevip[]['cname']
        //本次会员时间  : $doublevip[]['start'].'-'$doublevip[]['end']

        //城市重点系数 //师长 //部门 //总合同时间
        foreach ($doublevip as $k => $v) {
            $type       = 4;
            $cityid     = $v['cs'];
            $cityPoint  = D('SaleShow')->getCityPointByCid($cityid,$type);
            $doublevip[$k]['cityPoint'] = $cityPoint[0]['citypoint'];

            $department  = D('SaleShow')->getCityDepartment($cityid);
            $doublevip[$k]['shizhang'] = $department[0]['shizhang'];
            if($department[0]['type'] == 1) {
                $doublevip[$k]['bumen'] = '商务';
            }elseif ($department[0]['type'] == 5) {
                $doublevip[$k]['bumen'] = '外销';
            } elseif ($department[0]['type'] == 6) {
                $doublevip[$k]['bumen'] = '品牌';
            }
            //var_dump($department);
            $doublevip[$k]['contract_time'] = date("Y-m-d",$v['contract_start']).'-'.date("Y-m-d",$v['contract_end']);

        }
        //var_dump($doublevip);
        
        //地级市
        //付款时间
        
        

        $this->display();
    }

    //会员发展统计
    public function hyfzdb() {
        //从 log_user_real_company表查询有会员的城市列表 
        //$start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        //$end = date('Y-m-d H:i:s',strtotime($_GET['end']));
        //$cityid         = I('get.cityid');
        //$department     = I('get.department');
        //$type           = I('get.type');


        $start = '2016-10-01 00:00:00';
        $end = '2016-10-31 00:00:00';

        $startdata = D('User')->getCompanysOfTrue($start);
        $enddata = D('User')->getCompanysOfTrue($end);
        //城市
        foreach ($startdata as $k => $v) {
            //城市系数 //管辖部门 //师长 //区域经理 
            $type       = 4;
            $cityid     = $v['city_id'];
            $cityPoint  = D('SaleShow')->getCityPointByCid($cityid,$type);
            $startdata[$k]['citypoint'] = $cityPoint[0][' citypoint'];

            $department = D("SaleShow")->getCityDepartment($cityid);
            $startdata[$k]['jingli'] = $department[0]['quyujingli'];
            $startdata[$k]['shizhang'] = $department[0]['shizhang'];
            if($department[0]['type'] == 1) {
                $startdata[$k]['department'] = '商务';
            }elseif($department[0]['type'] == 5) {
                $startdata[$k]['department'] = '外销';
            }elseif($department[0]['type'] == 6) {
                $startdata[$k]['department'] = '客服';
            }
        }

        //选择周期 
        //同比周期  :    同比指上一年同期
        $tongbitime = date('Y-m-d  H:i:s', strtotime(date('Y-m-01', strtotime($end)) . ' -11 month -1 day'));
        var_dump('同比时间：'.$tongbitime);
        //环比周期  ：   环比指上一月同期
        $huanbitime = date('Y-m-d  H:i:s', strtotime(date('Y-m-01', strtotime($start)) . ' -1 day'));
        var_dump('环比时间：'.$huanbitime);
        //同比      :     查询上一年同比时间时

        //环比
        //地级市

        //var_dump($startdata);

        $this->display();
    }

    //会员状态统计（按城市，）
    public function memberCount(){
        //var_dump($_GET);
        if(!empty($_GET['city'])){
            $search['city'] = I("get.city");
        }
        if(!empty($_GET['department'])){
            $search['department'] = I("get.department");
        }
        if(!empty($_GET['time1'])){
            $search['time'] = I("get.time1");
            $search['time1'] = I("get.time1");
        }

        //$time = date("Y-m-d",strtotime('2017-03-01'.'-45day'));
        //var_dump($time);
        //取职能分类  1职能部门商务 5职能部门外销 6职能部门品牌
        $cmap['type'] = array('EQ','1');
        $tree = D('SaleSetting')->getCategory($cmap);
        $tree = getSaleCategory($tree);
        $checktree = saleZNBM($tree,'',false);

        //获取列表
        $pagesize = I("get.pagesize");//默认每页显示
        import('Library.Org.Util.Page');
        $count = D("User")->getAllCityUsersCount($search);
        if(empty($pagesize) || $pagesize == 0){
            $pagesize = 0;
            $result['list'] = D("User")->getAllCityUsers($search,0,0);
            $result['total'] = count($result['list']);
        }else{
            $Page       = new \Page($count,$pagesize);
            $result['page'] = $Page->show();                                                           
            $result['total'] = $count;
            $result['list'] = D("User")->getAllCityUsers($search, $Page->firstRow,$Page->listRows);
        }
        //生成结果集缓存
        //$datacache = S('Cache:salesCountCityUsers');
        //if($datacache == ''){
        $data = D("User")->getAllCityUsers($search, 0,1000);
        $datacache = S('Cache:salesCountCityUsers'.session("uc_userinfo.id"),$data,3600);
        //}
        if(!empty($_GET['time2']) || $_GET['tongbi'] == 1 || $_GET['huanbi'] == 1){
            $search['time'] = I("get.time2");//查询对比时间内数据
            $search['time1'] = I("get.time1");//查询对比时间内数据
            $search['time2'] = I("get.time2");//查询对比时间内数据
            if(!empty($_GET['tongbi'])){
                $search['time'] = date("Y-m-d",strtotime($_GET['time1'].'-1 year'));//同比时间 time1 - 1年
                $search['tongbi'] = $search['time'];//查询对比时间内数据
            }
            if(!empty($_GET['huanbi'])){
                $search['time'] = date("Y-m-d",strtotime($_GET['time1'].'-1 month'));//环比时间 time1 - 1月
                $search['huanbi'] = $search['time'];//查询对比时间内数据
            }
            if(empty($pagesize) || $pagesize == 0){
                $duibi = D("User")->getAllCityUsers($search,0,0);
            }else{
                $duibi = D("User")->getAllCityUsers($search, $Page->firstRow,$Page->listRows);
            }
            //var_dump($search);
            //$duibi = D("User")->getAllCityUsers($search, $Page->firstRow,$Page->listRows);
            
            //var_dump($duibi);
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key]['duibi_vipcnt']       =  $duibi[$key]['vipcnt'];//对比综合会员
                $result['list'][$key]['duibi_doublecnt']    =  $duibi[$key]['doublecnt'];//对比多倍会员
                $result['list'][$key]['duibi_realvip']      =  $duibi[$key]['realvip'] != '' ? $duibi[$key]['realvip'] : 0 ;//对比真是会员
                $result['list'][$key]['duibi_wanchenglv']   =  $duibi[$key]['wanchenglv'];//对比完成率
                $result['list'][$key]['duibi_manager']      =  $duibi[$key]['manager'];//对比所属部门
                $result['list'][$key]['duibi_point']        =  $duibi[$key]['point'] != '' ? $duibi[$key]['point'] : 0 ;//对比会员指标
                $result['list'][$key]['duibi_daoqi']        =  $duibi[$key]['daoqi'];//对比到期会员
                $result['list'][$key]['duibi_zhanting']     =  $duibi[$key]['zhanting'];//对比暂停会员
                $result['list'][$key]['duibi_tuifei']       =  $duibi[$key]['tuifei'];//对比退费会员
                $result['list'][$key]['duibi_xufei']        =  $duibi[$key]['xufei'];//对比续费会员
                $result['list'][$key]['duibi_tqxufei']      =  $duibi[$key]['tqxufei'];//对比提前续费
                $result['list'][$key]['duibi_xfyue']        =  $duibi[$key]['xfyue'];//对比续费月数
                $result['list'][$key]['duibi_xfjidu']       =  $duibi[$key]['xfjidu'];//对比续费季度
                $result['list'][$key]['duibi_xufeilv']      =  $duibi[$key]['xufeilv'];//对比续费率
                $result['list'][$key]['duibi_qqname']       =  $duibi[$key]['qqname'];//对比城市QQ群名称
                $result['list'][$key]['duibi_qqnumber']     =  $duibi[$key]['qqnumber'];//对比城市QQ群人数
                $result['list'][$key]['duibi_qqmanager']    =  $duibi[$key]['qqmanager'];//对比QQ群管理员
                $result['list'][$key]['duibi_usercount1']   =  $duibi[$key]['usercount1'];//对比90天内会员数
                $result['list'][$key]['duibi_usercount2']   =  $duibi[$key]['usercount2'];//对比90-177会员数
                $result['list'][$key]['duibi_usercount3']   =  $duibi[$key]['usercount3'];//对比177-396会员数
                $result['list'][$key]['duibi_usercount4']   =  $duibi[$key]['usercount4'];//对比396天上会员数
                $result['list'][$key]['duibi_userconpersent1']  =  $duibi[$key]['userconpersent1'];//对比90天内会员占比
                $result['list'][$key]['duibi_userconpersent2']  =  $duibi[$key]['userconpersent2'];//对比90-177会占比
                $result['list'][$key]['duibi_userconpersent3']  =  $duibi[$key]['userconpersent3'];//对比177-396会占比
                $result['list'][$key]['duibi_userconpersent4']  =  $duibi[$key]['userconpersent4'];//对比396天上会占比
            }
        }
        //var_dump($result['list'][0]);
        //$user = D("User");
        //$allInfo = $user->getAllCityUsers($map);
        //var_dump($allInfo);
        //$data = $user->getCompanyChangeList("2017-02-15");
        //获取管辖城市信息
        $cityIds = getAdminCityIds(true, true, true);
        $citys = getCityListByCityIds($cityIds);
        $this->assign("pagesize",$pagesize);
        $this->assign('citys',$citys);//城市选择
        $this->assign('tree',$checktree);//部门选择
        $this->assign("search",$search);
        $this->assign('result',$result);
        $this->display();
    }

    //会员状态统计（按师、团）
    public function memberCountByGroup(){
        //var_dump($_GET);
        if(!empty($_GET['time1'])){
            $search['time'] = I("get.time1");
            $search['time1'] = I("get.time1");
        }
        
        //先按部门查出师，然后按师查出团，然后查出团下的管理城市，根据城市ID，查出会员信息
        /*$pagesize = 20;//默认每页显示
        import('Library.Org.Util.Page');
        $count = D("User")->getDepartmentUsersCount($search);
        $Page       = new \Page($count,$pagesize);
        $result['page'] = $Page->show();
        $result['total'] = $count;*/
        $result['list'] = D("User")->getDepartmentUsers($search);

        if(!empty($_GET['time2']) || $_GET['tongbi'] == 1 || $_GET['huanbi'] == 1){
            $search['time'] = I("get.time2");
            $search['time1'] = I("get.time1");//查询对比时间内数据
            $search['time2'] = I("get.time2");//查询对比时间内数据
            if(!empty($_GET['tongbi'])){
                $search['time'] = date("Y-m-d",strtotime($_GET['time1'].'-1 year'));//同比时间 time1 - 1年
                $search['tongbi'] = $search['time'];//查询对比时间内数据
            }
            if(!empty($_GET['huanbi'])){
                $search['time'] = date("Y-m-d",strtotime($_GET['time1'].'-1 month'));//环比时间 time1 - 1月
                $search['huanbi'] = $search['time'];//查询对比时间内数据
            }
            //var_dump($search);
            $duibi = D("User")->getDepartmentUsers($search);
            //var_dump($duibi);
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key]['duibi_vipcnt']       =  $duibi[$key]['vipcnt'];//对比综合会员
                $result['list'][$key]['duibi_doublecnt']    =  $duibi[$key]['doublecnt'];//对比多倍会员
                $result['list'][$key]['duibi_realvip']      =  $duibi[$key]['realvip'] != '' ? $duibi[$key]['realvip'] : 0 ;//对比真是会员
                $result['list'][$key]['duibi_wanchenglv']   =  $duibi[$key]['wanchenglv'];//对比完成率
                $result['list'][$key]['duibi_manager']      =  $duibi[$key]['manager'];//对比所属部门
                $result['list'][$key]['duibi_point']        =  $duibi[$key]['point'] != '' ? $duibi[$key]['point'] : 0 ;//对比会员指标
                $result['list'][$key]['duibi_daoqi']        =  $duibi[$key]['daoqi'];//对比到期会员
                $result['list'][$key]['duibi_zhanting']     =  $duibi[$key]['zhanting'];//对比暂停会员
                $result['list'][$key]['duibi_tuifei']       =  $duibi[$key]['tuifei'];//对比退费会员
                $result['list'][$key]['duibi_xufei']        =  $duibi[$key]['xufei'];//对比续费会员
                $result['list'][$key]['duibi_tqxufei']      =  $duibi[$key]['tqxufei'];//对比提前续费
                $result['list'][$key]['duibi_xfyue']        =  $duibi[$key]['xfyue'];//对比续费月数
                $result['list'][$key]['duibi_xfjidu']       =  $duibi[$key]['xfjidu'];//对比续费季度
                $result['list'][$key]['duibi_xufeilv']      =  $duibi[$key]['xufeilv'];//对比续费率
                $result['list'][$key]['duibi_qqname']       =  $duibi[$key]['qqname'];//对比城市QQ群名称
                $result['list'][$key]['duibi_qqnumber']     =  $duibi[$key]['qqnumber'];//对比城市QQ群人数
                $result['list'][$key]['duibi_qqmanager']    =  $duibi[$key]['qqmanager'];//对比QQ群管理员
                $result['list'][$key]['duibi_usercount1']   =  $duibi[$key]['usercount1'];//对比90天内会员数
                $result['list'][$key]['duibi_usercount2']   =  $duibi[$key]['usercount2'];//对比90-177会员数
                $result['list'][$key]['duibi_usercount3']   =  $duibi[$key]['usercount3'];//对比177-396会员数
                $result['list'][$key]['duibi_usercount4']   =  $duibi[$key]['usercount4'];//对比396天上会员数
                $result['list'][$key]['duibi_userconpersent1']  =  $duibi[$key]['userconpersent1'];//对比90天内会员占比
                $result['list'][$key]['duibi_userconpersent2']  =  $duibi[$key]['userconpersent2'];//对比90-177会占比
                $result['list'][$key]['duibi_userconpersent3']  =  $duibi[$key]['userconpersent3'];//对比177-396会占比
                $result['list'][$key]['duibi_userconpersent4']  =  $duibi[$key]['userconpersent4'];//对比396天上会占比
            }
        }
        //var_dump($result['list']);
        //$_GET['type'] = shi  / tuan 
        $type = I("get.type");
        if(!empty($_GET['city'])){
            $search['city'] = I("get.city");
        }
        if(!empty($_GET['department'])){
            $search['department'] = I("get.department");
        }
        if($type == 'shi'){
            //师 level = 1 ,查询出所有的师 module 1拓展2品牌3外销
            $level = 1;
            $shi = D("User")->getAllDepartments($level,$search);
            $tree = D("User")->getAllDepartments($level);
            foreach ($shi as $k => $v) {
                foreach ($result['list'] as $key => $value) {
                    if($value['managers'][$v['module']]['shi'] == $v['name']){
                        $shi[$k]['city'][] = $value['text'];//城市数组
                        $shi[$k]['vipcnt'] += $value['vipcnt'];//综合会员数
                        $shi[$k]['realvip'] += $value['realvip'];//真实会员
                        $shi[$k]['doublecnt'] += $value['doublecnt'];//多倍会员
                        $shi[$k]['manager'] = $value['manager'];//
                        
                        $shi[$k]['daoqi'] += $value['daoqi'];//到期会员
                        $shi[$k]['zhanting'] += $value['zhanting'];//暂停会员
                        $shi[$k]['tuifei'] += $value['tuifei'];//退费会员
                        $shi[$k]['xufei'] += $value['xufei'];//续费会员
                        $shi[$k]['tqxufei'] += $value['tqxufei'];//提前续费
                        $shi[$k]['zhxufei'] += $value['zhxufei'];//滞后续费
                        $shi[$k]['xfyue'] += $value['xfyue'];//续费月
                        $shi[$k]['xfjidu'] += $value['xfjidu'];//续费季度
                        $shi[$k]['shizhang'] = $v['info'];//师长
                        $shi[$k]['usercount1'] += $value['usercount1'];
                        $shi[$k]['usercount2'] += $value['usercount2'];
                        $shi[$k]['usercount3'] += $value['usercount3'];
                        $shi[$k]['usercount4'] += $value['usercount4'];
                        if($shi[$k]['realvip'] == 0){
                            $shi[$k]['userconpersent1'] = 0;//少于90天的会员占比
                            $shi[$k]['userconpersent2'] = 0;//时间大于90天，小于177天的会员占比
                            $shi[$k]['userconpersent3'] = 0;//时间大于177天，小于396天的会员占比
                            $shi[$k]['userconpersent4'] = 0;//时间大于396天的会员占比
                            $shi[$k]['xufeilv'] = "0%";//续费率
                        }else{
                            $shi[$k]['userconpersent1'] = (round($shi[$k]['usercount1']/$shi[$k]['realvip'],2)*100).'%';//少于90天的会员占比
                            $shi[$k]['userconpersent2'] = (round($shi[$k]['usercount2']/$shi[$k]['realvip'],2)*100).'%';//时间大于90天，小于177天的会员占比
                            $shi[$k]['userconpersent3'] = (round($shi[$k]['usercount3']/$shi[$k]['realvip'],2)*100).'%';//时间大于177天，小于396天的会员占比
                            $shi[$k]['userconpersent4'] = (round($shi[$k]['usercount4']/$shi[$k]['realvip'],2)*100).'%';//时间大于396天的会员占比
                            $shi[$k]['xufeilv'] = (round($shi[$k]['xufei']/$shi[$k]['realvip'],2)*100).'%';//续费率
                        }
                        $shi[$k]['duibi_vipcnt'] += $value['duibi_vipcnt'];//对比综合会员数
                        $shi[$k]['duibi_realvip'] += $value['duibi_realvip'];//对比真实会员
                        $shi[$k]['duibi_doublecnt'] += $value['duibi_doublecnt'];//对比多倍会员
                        $shi[$k]['duibi_daoqi'] += $value['duibi_daoqi'];//到期会员
                        $shi[$k]['duibi_zhanting'] += $value['duibi_zhanting'];//暂停会员
                        $shi[$k]['duibi_tuifei'] += $value['duibi_tuifei'];//退费会员
                        $shi[$k]['duibi_xufei'] += $value['duibi_xufei'];//续费会员
                        $shi[$k]['duibi_tqxufei'] += $value['duibi_tqxufei'];//提前续费
                        $shi[$k]['duibi_zhxufei'] += $value['duibi_zhxufei'];//滞后续费
                        $shi[$k]['duibi_xfyue'] += $value['duibi_xfyue'];//续费月
                        $shi[$k]['duibi_xfjidu'] += $value['duibi_xfjidu'];//续费季度
                        $shi[$k]['duibi_usercount1'] += $value['duibi_usercount1'];
                        $shi[$k]['duibi_usercount2'] += $value['duibi_usercount2'];
                        $shi[$k]['duibi_usercount3'] += $value['duibi_usercount3'];
                        $shi[$k]['duibi_usercount4'] += $value['duibi_usercount4'];
                        if($shi[$k]['duibi_realvip'] == 0){
                            $shi[$k]['duibi_userconpersent1'] = 0;//少于90天的会员占比
                            $shi[$k]['duibi_userconpersent2'] = 0;//时间大于90天，小于177天的会员占比
                            $shi[$k]['duibi_userconpersent3'] = 0;//时间大于177天，小于396天的会员占比
                            $shi[$k]['duibi_userconpersent4'] = 0;//时间大于396天的会员占比
                            $shi[$k]['duibi_xufeilv'] = "0%";//续费率
                        }else{
                            $shi[$k]['duibi_userconpersent1'] = (round($shi[$k]['duibi_usercount1']/$shi[$k]['duibi_realvip'],2)*100).'%';//少于90天的会员占比
                            $shi[$k]['duibi_userconpersent2'] = (round($shi[$k]['duibi_usercount2']/$shi[$k]['duibi_realvip'],2)*100).'%';//时间大于90天，小于177天的会员占比
                            $shi[$k]['duibi_userconpersent3'] = (round($shi[$k]['duibi_usercount3']/$shi[$k]['duibi_realvip'],2)*100).'%';//时间大于177天，小于396天的会员占比
                            $shi[$k]['duibi_userconpersent4'] = (round($shi[$k]['duibi_usercount4']/$shi[$k]['duibi_realvip'],2)*100).'%';//时间大于396天的会员占比
                            $shi[$k]['duibi_duibi_xufeilv'] = (round($shi[$k]['duibi_xufei']/$shi[$k]['duibi_realvip'],2)*100).'%';//续费率
                        }
                    }
                    $shi[$k]['citynum'] = count($shi[$k]['city']);//城市数量
                    if(empty($shi[$k]['citynum'])){
                        $shi[$k]['citynum'] = 0;
                    }
                    if(empty($shi[$k]['vipcnt'])){
                        $shi[$k]['vipcnt'] = 0;
                    }
                    if(empty($shi[$k]['realvip'])){
                        $shi[$k]['realvip'] = 0;
                    }
                    if(empty($shi[$k]['doublecnt'])){
                        $shi[$k]['doublecnt'] = 0;
                    }
                    if(empty($shi[$k]['daoqi'])){
                        $shi[$k]['daoqi'] = 0;
                    }
                    if(empty($shi[$k]['zhanting'])){
                        $shi[$k]['zhanting'] = 0;
                    }
                    if(empty($shi[$k]['tuifei'])){
                        $shi[$k]['tuifei'] = 0;
                    }
                    if(empty($shi[$k]['xufei'])){
                        $shi[$k]['xufei'] = 0;
                    }
                    if(empty($shi[$k]['tqxufei'])){
                        $shi[$k]['tqxufei'] = 0;
                    }
                    if(empty($shi[$k]['zhxufei'])){
                        $shi[$k]['zhxufei'] = 0;
                    }
                    if(empty($shi[$k]['xfyue'])){
                        $shi[$k]['xfyue'] = 0;
                    }
                    if(empty($shi[$k]['xfjidu'])){
                        $shi[$k]['xfjidu'] = 0;
                    }
                    if(empty($shi[$k]['usercount1'])){
                        $shi[$k]['usercount1'] = 0;
                    }
                    if(empty($shi[$k]['usercount2'])){
                        $shi[$k]['usercount2'] = 0;
                    }
                    if(empty($shi[$k]['usercount3'])){
                        $shi[$k]['usercount3'] = 0;
                    }
                    if(empty($shi[$k]['usercount4'])){
                        $shi[$k]['usercount4'] = 0;
                    }
                    if(empty($shi[$k]['xufeilv'])){
                        $shi[$k]['xufeilv'] = "0%";
                    }
                    if(empty($shi[$k]['userconpersent1'])){
                        $shi[$k]['userconpersent1'] = "0%";
                    }
                    if(empty($shi[$k]['userconpersent2'])){
                        $shi[$k]['userconpersent2'] = "0%";
                    }
                    if(empty($shi[$k]['userconpersent3'])){
                        $shi[$k]['userconpersent3'] = "0%";
                    }
                    if(empty($shi[$k]['userconpersent4'])){
                        $shi[$k]['userconpersent4'] = "0%";
                    }
                    //对比数据
                    if(empty($shi[$k]['duibi_vipcnt'])){
                        $shi[$k]['duibi_vipcnt'] = 0;
                    }
                    if(empty($shi[$k]['duibi_realvip'])){
                        $shi[$k]['duibi_realvip'] = 0;
                    }
                    if(empty($shi[$k]['duibi_doublecnt'])){
                        $shi[$k]['duibi_doublecnt'] = 0;
                    }
                    if(empty($shi[$k]['duibi_daoqi'])){
                        $shi[$k]['duibi_daoqi'] = 0;
                    }
                    if(empty($shi[$k]['duibi_zhanting'])){
                        $shi[$k]['duibi_zhanting'] = 0;
                    }
                    if(empty($shi[$k]['duibi_tuifei'])){
                        $shi[$k]['duibi_tuifei'] = 0;
                    }
                    if(empty($shi[$k]['duibi_xufei'])){
                        $shi[$k]['duibi_xufei'] = 0;
                    }
                    if(empty($shi[$k]['duibi_tqxufei'])){
                        $shi[$k]['duibi_tqxufei'] = 0;
                    }
                    if(empty($shi[$k]['duibi_zhxufei'])){
                        $shi[$k]['duibi_zhxufei'] = 0;
                    }
                    if(empty($shi[$k]['duibi_xfyue'])){
                        $shi[$k]['duibi_xfyue'] = 0;
                    }
                    if(empty($shi[$k]['duibi_xfjidu'])){
                        $shi[$k]['duibi_xfjidu'] = 0;
                    }
                    if(empty($shi[$k]['duibi_usercount1'])){
                        $shi[$k]['duibi_usercount1'] = 0;
                    }
                    if(empty($shi[$k]['duibi_usercount2'])){
                        $shi[$k]['duibi_usercount2'] = 0;
                    }
                    if(empty($shi[$k]['duibi_usercount3'])){
                        $shi[$k]['duibi_usercount3'] = 0;
                    }
                    if(empty($shi[$k]['duibi_usercount4'])){
                        $shi[$k]['duibi_usercount4'] = 0;
                    }
                    if(empty($shi[$k]['duibi_xufeilv'])){
                        $shi[$k]['duibi_xufeilv'] = "0%";
                    }
                    if(empty($shi[$k]['duibi_userconpersent1'])){
                        $shi[$k]['duibi_userconpersent1'] = "0%";
                    }
                    if(empty($shi[$k]['duibi_userconpersent2'])){
                        $shi[$k]['duibi_userconpersent2'] = "0%";
                    }
                    if(empty($shi[$k]['duibi_userconpersent3'])){
                        $shi[$k]['duibi_userconpersent3'] = "0%";
                    }
                    if(empty($shi[$k]['duibi_userconpersent4'])){
                        $shi[$k]['duibi_userconpersent4'] = "0%";
                    }
                }
            }
            $departmentData = $shi;
        }else{
            //团 level = 2
            $level = 2;
            $tuan = D("User")->getAllDepartments($level,$search);
            $tree = D("User")->getAllDepartments($level);
            foreach ($tuan as $k => $v) {
                foreach ($result['list'] as $key => $value) {
                    if($value['managers'][$v['module']]['tuan'] == $v['name']){
                        $tuan[$k]['city'][] = $value['text'];//城市数组
                        $tuan[$k]['vipcnt'] += $value['vipcnt'];
                        $tuan[$k]['realvip'] += $value['realvip'];
                        $tuan[$k]['doublecnt'] += $value['doublecnt'];
                        $tuan[$k]['manager'] = $value['manager'];
                        //$tuan[$k]['cname'][] = $value['cname'];
                        $tuan[$k]['daoqi'] += $value['daoqi'];
                        $tuan[$k]['zhanting'] += $value['zhanting'];
                        $tuan[$k]['tuifei'] += $value['tuifei'];
                        $tuan[$k]['xufei'] += $value['xufei'];
                        $tuan[$k]['zhxufei'] += $value['zhxufei'];
                        $tuan[$k]['xfyue'] += $value['xfyue'];
                        $tuan[$k]['xfjidu'] += $value['xfjidu'];
                        $tuan[$k]['shizhang'] = $v['info'];
                        $tuan[$k]['usercount1'] += $value['usercount1'];
                        $tuan[$k]['usercount2'] += $value['usercount2'];
                        $tuan[$k]['usercount3'] += $value['usercount3'];
                        $tuan[$k]['usercount4'] += $value['usercount4'];
                        if($tuan[$k]['realvip'] == 0){
                            $tuan[$k]['userconpersent1'] = 0;//少于90天的会员占比
                            $tuan[$k]['userconpersent2'] = 0;//时间大于90天，小于177天的会员占比
                            $tuan[$k]['userconpersent3'] = 0;//时间大于177天，小于396天的会员占比
                            $tuan[$k]['userconpersent4'] = 0;//时间大于396天的会员占比
                            $tuan[$k]['xufeilv'] = "0%";//续费率
                        }else{
                            $tuan[$k]['userconpersent1'] = (round($tuan[$k]['usercount1']/$tuan[$k]['realvip'],2)*100).'%';//少于90天的会员占比
                            $tuan[$k]['userconpersent2'] = (round($tuan[$k]['usercount2']/$tuan[$k]['realvip'],2)*100).'%';//时间大于90天，小于177天的会员占比
                            $tuan[$k]['userconpersent3'] = (round($tuan[$k]['usercount3']/$tuan[$k]['realvip'],2)*100).'%';//时间大于177天，小于396天的会员占比
                            $tuan[$k]['userconpersent4'] = (round($tuan[$k]['usercount4']/$tuan[$k]['realvip'],2)*100).'%';//时间大于396天的会员占比
                            $tuan[$k]['xufeilv'] = (round($tuan[$k]['xufei']/$tuan[$k]['realvip'],2)*100).'%';//续费率
                        }
                        $tuan[$k]['duibi_vipcnt'] += $value['duibi_vipcnt'];//对比综合会员数
                        $tuan[$k]['duibi_realvip'] += $value['duibi_realvip'];//对比真实会员
                        $tuan[$k]['duibi_doublecnt'] += $value['duibi_doublecnt'];//对比多倍会员
                        $tuan[$k]['duibi_daoqi'] += $value['duibi_daoqi'];//到期会员
                        $tuan[$k]['duibi_zhanting'] += $value['duibi_zhanting'];//暂停会员
                        $tuan[$k]['duibi_tuifei'] += $value['duibi_tuifei'];//退费会员
                        $tuan[$k]['duibi_xufei'] += $value['duibi_xufei'];//续费会员
                        $tuan[$k]['duibi_tqxufei'] += $value['duibi_tqxufei'];//提前续费
                        $tuan[$k]['duibi_zhxufei'] += $value['duibi_zhxufei'];//滞后续费
                        $tuan[$k]['duibi_xfyue'] += $value['duibi_xfyue'];//续费月
                        $tuan[$k]['duibi_xfjidu'] += $value['duibi_xfjidu'];//续费季度
                        $tuan[$k]['duibi_usercount1'] += $value['duibi_usercount1'];
                        $tuan[$k]['duibi_usercount2'] += $value['duibi_usercount2'];
                        $tuan[$k]['duibi_usercount3'] += $value['duibi_usercount3'];
                        $tuan[$k]['duibi_usercount4'] += $value['duibi_usercount4'];
                        if($tuan[$k]['duibi_realvip'] == 0){
                            $tuan[$k]['duibi_userconpersent1'] = 0;//少于90天的会员占比
                            $tuan[$k]['duibi_userconpersent2'] = 0;//时间大于90天，小于177天的会员占比
                            $tuan[$k]['duibi_userconpersent3'] = 0;//时间大于177天，小于396天的会员占比
                            $tuan[$k]['duibi_userconpersent4'] = 0;//时间大于396天的会员占比
                            $tuan[$k]['duibi_xufeilv'] = "0%";//续费率
                        }else{
                            $tuan[$k]['duibi_userconpersent1'] = (round($tuan[$k]['duibi_usercount1']/$tuan[$k]['duibi_realvip'],2)*100).'%';//少于90天的会员占比
                            $tuan[$k]['duibi_userconpersent2'] = (round($tuan[$k]['duibi_usercount2']/$tuan[$k]['duibi_realvip'],2)*100).'%';//时间大于90天，小于177天的会员占比
                            $tuan[$k]['duibi_userconpersent3'] = (round($tuan[$k]['duibi_usercount3']/$tuan[$k]['duibi_realvip'],2)*100).'%';//时间大于177天，小于396天的会员占比
                            $tuan[$k]['duibi_userconpersent4'] = (round($tuan[$k]['duibi_usercount4']/$tuan[$k]['duibi_realvip'],2)*100).'%';//时间大于396天的会员占比
                            $tuan[$k]['duibi_duibi_xufeilv'] = (round($tuan[$k]['duibi_xufei']/$tuan[$k]['duibi_realvip'],2)*100).'%';//续费率
                        }
                    }
                    $tuan[$k]['citynum'] = count($tuan[$k]['city']);//城市数量
                    if(empty($tuan[$k]['citynum'])){
                        $tuan[$k]['citynum'] = 0;
                    }
                    if(empty($tuan[$k]['vipcnt'])){
                        $tuan[$k]['vipcnt'] = 0;
                    }
                    if(empty($tuan[$k]['realvip'])){
                        $tuan[$k]['realvip'] = 0;
                    }
                    if(empty($tuan[$k]['doublecnt'])){
                        $tuan[$k]['doublecnt'] = 0;
                    }
                    if(empty($tuan[$k]['daoqi'])){
                        $tuan[$k]['daoqi'] = 0;
                    }
                    if(empty($tuan[$k]['zhanting'])){
                        $tuan[$k]['zhanting'] = 0;
                    }
                    if(empty($tuan[$k]['tuifei'])){
                        $tuan[$k]['tuifei'] = 0;
                    }
                    if(empty($tuan[$k]['xufei'])){
                        $tuan[$k]['xufei'] = 0;
                    }
                    if(empty($tuan[$k]['tqxufei'])){
                        $tuan[$k]['tqxufei'] = 0;
                    }
                    if(empty($tuan[$k]['zhxufei'])){
                        $tuan[$k]['zhxufei'] = 0;
                    }
                    if(empty($tuan[$k]['xfyue'])){
                        $tuan[$k]['xfyue'] = 0;
                    }
                    if(empty($tuan[$k]['xfjidu'])){
                        $tuan[$k]['xfjidu'] = 0;
                    }
                    if(empty($tuan[$k]['usercount1'])){
                        $tuan[$k]['usercount1'] = 0;
                    }
                    if(empty($tuan[$k]['usercount2'])){
                        $tuan[$k]['usercount2'] = 0;
                    }
                    if(empty($tuan[$k]['usercount3'])){
                        $tuan[$k]['usercount3'] = 0;
                    }
                    if(empty($tuan[$k]['usercount4'])){
                        $tuan[$k]['usercount4'] = 0;
                    }
                    if(empty($tuan[$k]['xufeilv'])){
                        $tuan[$k]['xufeilv'] = "0%";
                    }
                    if(empty($tuan[$k]['userconpersent1'])){
                        $tuan[$k]['userconpersent1'] = "0%";
                    }
                    if(empty($tuan[$k]['userconpersent2'])){
                        $tuan[$k]['userconpersent2'] = "0%";
                    }
                    if(empty($tuan[$k]['userconpersent3'])){
                        $tuan[$k]['userconpersent3'] = "0%";
                    }
                    if(empty($tuan[$k]['userconpersent4'])){
                        $tuan[$k]['userconpersent4'] = "0%";
                    }
                    //对比数据
                    if(empty($tuan[$k]['duibi_vipcnt'])){
                        $tuan[$k]['duibi_vipcnt'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_realvip'])){
                        $tuan[$k]['duibi_realvip'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_doublecnt'])){
                        $tuan[$k]['duibi_doublecnt'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_daoqi'])){
                        $tuan[$k]['duibi_daoqi'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_zhanting'])){
                        $tuan[$k]['duibi_zhanting'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_tuifei'])){
                        $tuan[$k]['duibi_tuifei'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_xufei'])){
                        $tuan[$k]['duibi_xufei'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_tqxufei'])){
                        $tuan[$k]['duibi_tqxufei'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_zhxufei'])){
                        $tuan[$k]['duibi_zhxufei'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_xfyue'])){
                        $tuan[$k]['duibi_xfyue'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_xfjidu'])){
                        $tuan[$k]['duibi_xfjidu'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_usercount1'])){
                        $tuan[$k]['duibi_usercount1'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_usercount2'])){
                        $tuan[$k]['duibi_usercount2'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_usercount3'])){
                        $tuan[$k]['duibi_usercount3'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_usercount4'])){
                        $tuan[$k]['duibi_usercount4'] = 0;
                    }
                    if(empty($tuan[$k]['duibi_xufeilv'])){
                        $tuan[$k]['duibi_xufeilv'] = "0%";
                    }
                    if(empty($tuan[$k]['duibi_userconpersent1'])){
                        $tuan[$k]['duibi_userconpersent1'] = "0%";
                    }
                    if(empty($tuan[$k]['duibi_userconpersent2'])){
                        $tuan[$k]['duibi_userconpersent2'] = "0%";
                    }
                    if(empty($tuan[$k]['duibi_userconpersent3'])){
                        $tuan[$k]['duibi_userconpersent3'] = "0%";
                    }
                    if(empty($tuan[$k]['duibi_userconpersent4'])){
                        $tuan[$k]['duibi_userconpersent4'] = "0%";
                    }
                }
            }
            $departmentData = $tuan;
        }
        $datacache = S('Cache:salesCountDepartmentCitys'.session("uc_userinfo.id"),$departmentData,3600);
        $total = count($departmentData);
        //var_dump($departmentData[0]);
        //取职能分类  1职能部门商务 5职能部门外销 6职能部门品牌
        //$cmap['type'] = array('EQ','1');
        //$tree = D('SaleSetting')->getCategory($cmap);
        //$tree = getSaleCategory($tree);
        //$checktree = saleZNBM($tree,'',false);
        //var_dump($tree[0]['children'][0]);
        //获取管辖城市信息
        $cityIds = getAdminCityIds(true, true, true);
        $citys = getCityListByCityIds($cityIds);
        //var_dump($tree);
        $this->assign('citys',$citys);//城市选择
        $this->assign('tree',$tree);
        $this->assign("search",$search);      
        $this->assign('departmentData',$departmentData);
        $this->assign('total',$total);
        $this->assign("type",$type);
        $this->display();
    }

    public function downloadCityUsersInfo(){
        $type = I("get.type");//1是按城市 2是按师团
        if($type == 1){
            $tempResult = S('Cache:salesCountCityUsers'.session("uc_userinfo.id"));
        }else{
            $tempResult = S('Cache:salesCountDepartmentCitys'.session("uc_userinfo.id"));
        }
        
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");

        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );

        $phpExcel = new \PHPExcel();
        //设置表头
        if($type == 1){
            $title = array(
                '城市',
                '会员指标数',
                '综合会员数',
                '单倍会员数',
                '会员完成率',
                '多倍会员数',
                '到期会员数',
                '续费会员数',
                '暂停会员数',
                '退费会员数',
                '提前续费数',
                '滞后续费数',
                '续费率',
                '续费月数',
                '季度折算',
                '90天内会员数',
                '90天内占比',
                '90-177天会员数',
                '90-177天占比',
                '178-396天占比',
                '178-396天会员数',
                '396天以上会员数',
                '396天以上占比',
                '城市QQ群名称',
                '城市QQ群成员数',
                '操作人',
                '部门',
                '拓展师长',
                '拓展团长',
                '城市经理',
                '品牌师长',
                '品牌团长',
                '品牌师',
            );
        }else{
            $title = array(
                '部门',
                '城市',
                '综合会员数',
                '真实会员数',
                '多倍会员数',
                '到期会员数',
                '续费会员数',
                '暂停会员数',
                '退费会员数',
                '提前续费数',
                '滞后续费数',
                '续费率',
                '续费月数',
                '季度折算',
                '90天内会员数',
                '90天内占比',
                '90-177天会员数',
                '90-177天占比',
                '178-396天占比',
                '178-396天会员数',
                '396天以上会员数',
                '396天以上占比',
                '师长/团长'
            );
        }
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        //设置表内容
        $j = 1;
        if($type == 1){
            foreach ($tempResult as $key => $value) {
                //初始化$i
                $i = 0;

                //'城市',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['text']);
                //'会员指标数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['point']);
                //'综合会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['vipcnt']);
                //'单倍会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['realvip']);
                //'会员完成率',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['wanchenglv']);
                //'多倍会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['doublecnt']);
                //'到期会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['daoqi']);
                //'续费会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xufei']);
                //'暂停会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['zhanting']);
                //'退费会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tuifei']);
                //'提前续费数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tqxufei']);
                //'滞后续费数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['zhxufei']);
                //'续费率',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xufeilv']);
                //'续费月数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xfyue']);
                //'季度折算',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xfjidu']);
                //'90天内会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount1']);
                //'90天内占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent1']);
                //'90-177天会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount2']);
                //'90-177天占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent2']);
                //'178-396天占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount3']);
                //'178-396天会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent3']);
                //'396天以上会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount4']);
                //'396天以上占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent4']);
                //'城市QQ群名称',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['qqname']);
                //'城市QQ群成员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['qqnumber']);
                //'操作人',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['qqmanager']);
                //'部门',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['manager']);
                
                 if(!empty($value['managers'][1])){
                    //'拓展师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][1]['shizhang']);
                    //'拓展团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][1]['tuanzhang']);
                    //'城市经理',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][2]['jingli']);
                }else{
                    //'拓展师长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][2]['shizhang']);
                    //'拓展团长',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][2]['tuanzhang']);
                    //'城市经理',
                    $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                    $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][2]['jingli']);
                }
                
                //'品牌师长',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][3]['shizhang']);
                //'品牌团长',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][3]['tuanzhang']);
                //'品牌师',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['managers'][3]['tuanzhang']);

                $j++;
            }
        }else{
            foreach ($tempResult as $key => $value) {
                //初始化$i
                $i = 0;

                //'部门',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['name']);
                //'城市数量',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['citynum']);
                //'综合会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['vipcnt']);
                //'真实会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['realvip']);
                //'多倍会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['doublecnt']);
                //'到期会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['daoqi']);
                //'续费会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xufei']);
                //'暂停会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['zhanting']);
                //'退费会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tuifei']);
                //'提前续费数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tqxufei']);
                //'滞后续费数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['zhxufei']);
                //'续费率',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xufeilv']);
                //'续费月数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xfyue']);
                //'季度折算',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['xfjidu']);
                //'90天内会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount1']);
                //'90天内占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent1']);
                //'90-177天会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount2']);
                //'90-177天占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent2']);
                //'178-396天占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount3']);
                //'178-396天会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent3']);
                //'396天以上会员数',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['usercount4']);
                //'396天以上占比',
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['userconpersent4']);
                //'师长/团长'
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['info']);

                $j++;
            }
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        if($type == 1){
            header('Content-Disposition:attachment;filename="城市会员状态统计(城市).xls"');
        }else{
            header('Content-Disposition:attachment;filename="城市会员状态统计(师团).xls"');
        }
        
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }


}
