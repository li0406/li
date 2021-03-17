<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  销售系统设置
*/

class SalesetController extends HomeBaseController {

    /**
     * 岗位城市权限
     *
     */
    public function gwcsqx(){
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Citypoint')->getBrandInfo();
        //获取所有的拓展师长、团长、品牌师
        $dev = D('Citypoint')->getDevInfo();
        $this->assign('brand',$brand);
        $this->assign('dev',$dev);
        //var_dump($dev);
        //搜索功能
        $req = I('get.');
        $requestMap = array(
            "city","dept","dev_division","dev_regiment","dev_manage","open_status",
            "level","corps","brand_division","brand_regiment","brand_manage",'p','user','dl'
        );

        foreach ($req as $key => $value) {
            if(!in_array($key,$requestMap)){
                $this->error('Bad Request!');
            }
            if(!empty($value)){
               $condition[$key] = array('EQ',$value);
               if($key == 'open_status'){
                    if($value == 2){
                        $condition[$key] = array('EQ',0);
                    }
               }

               $info['selected'] .= '$("#searchBox").find("select[name='.$key.']").val("'.$value.'");';
           }
        }
        unset($condition['dl']);

        

        //取所有销售帐号
        $saleUsers = D('SalesSetting')->getSaleUsers();

        //分页
        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
        //下载Excel
        if(I('get.dl') == '1'){
            $pageCount = 1000;
        }
        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($condition['city']) && in_array($condition['city'][1], $ids)){
                $condition['id'] = $condition['city'];
                unset($condition['city']);
            }else{
                $idstr = implode(',', $ids);
                $condition['id'] = array('IN',$idstr); 
                unset($condition['city']);
            }
            $result = $this->getCityManageList($condition,$pageIndex,$pageCount);
        }
        


        //下载Excel
        if(I('get.dl') == '1'){

            //设置表头
            $title = array(
                '城市','开站状态','城市级别','部门','重点系数',
                '军长','拓师长','拓团长','城市经理','品师长','品团长','品牌师',
                '操作人','操作时间',
            );

            //设置表列
            $column = array(
                'city','open_status','level','dept','ratio',
                'corps','dev_division','dev_regiment','dev_manage','brand_division','brand_regiment','brand_manage',
                'act_uid','act_time',
            );
            //写入操作日志  opid optime opip optype opdes content
            $logData['opid']        = $_SESSION['uc_userinfo']['id'];
            $logData['optime']      = time();
            $logData['opip']        = get_client_ip();
            $logData['optype']      = 1;
            $logData['opdes']       = '批量导出岗位城市权限';
            $logData['content']     = '批量导出岗位城市权限数据';
            $log = D('Citypoint')->addLog($logData);
            $this->downExcel($title,$column,$result['list'],'岗位城市权限');
            die;
        }


        $user = session('uc_userinfo');

        $info['pageCount'] = $result['pageCount'];
        $info['today'] = date('Y-m-d',time());
        $info['username'] = $user['name'];
        $this->assign('keyword',$req);
        $this->assign('info',$info);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('cityList',$citys);
        $this->assign('saleUsers',$saleUsers);
        $this->display();
    }

    /**
     * 岗位城市权限添加
     *
     */
    public function gwcsqxAct(){
        if(IS_POST){
            $post = I('post.data');
            $id = I('post.id');

            $user = session('uc_userinfo');

            $data['city'] = $post['0'];             //城市
            $data['open_status'] = '1';             //开站状态
            $data['level'] = $post['2'];            //城市级别
            $data['dept'] = $post['3'];             //部门
            $data['ratio'] = $post['4'];            //重点系数
            $data['corps'] = $post['5'];            //军长
            $data['dev_division'] = $post['6'];     //拓师长
            $data['dev_regiment'] = $post['7'];     //拓团长
            $data['dev_manage'] = $post['8'];       //城市经理
            $data['brand_division'] = $post['9'];   //品师长
            $data['brand_regiment'] = $post['10'];  //品团长
            $data['brand_manage'] = $post['11'];    //品牌师

            $data['act_uid'] = $user['id'];         //操作人
            $data['act_time'] = time();             //操作时间

            if($id == '0'){
                //检测城市是否已存在
                $isCity = D('SalesSetting')->checkCitys($post['0']);
                if(!empty($isCity)){
                    $this->ajaxReturn(array('data'=>'','info'=>'城市已存在','status'=>0));
                }

                if (M('sales_city_manage')->add($data)){
                    //写入操作日志  opid optime opip optype opdes content
                    $logData['opid']        = $_SESSION['uc_userinfo']['id'];
                    $logData['optime']      = time();
                    $logData['opip']        = get_client_ip();
                    $logData['optype']      = 1;
                    $logData['opdes']       = '添加岗位城市权限';
                    $logData['content']     = json_encode($data);
                    $log = D('Citypoint')->addLog($logData);
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
                }
            }else{
                if (M("sales_city_manage")->where(array('id'=>$id))->save($data)){
                    //写入操作日志  opid optime opip optype opdes content
                    $logData['opid']        = $_SESSION['uc_userinfo']['id'];
                    $logData['optime']      = time();
                    $logData['opip']        = get_client_ip();
                    $logData['optype']      = 1;
                    $logData['opdes']       = '修改岗位城市权限';
                    $logData['content']     = json_encode($data);
                    $log = D('Citypoint')->addLog($logData);
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
                }
            }

            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * 计划月分单量
     */
    public function jhyfds(){

        //分页
        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        $condition['s.module'] = array('EQ',4);

        //搜索处理
        $dept = I('get.dept');
        if(!empty($dept)){
            $condition['c.dept'] = array('EQ',$dept);
            $info['deptinfo'] = '$("#searchBox").find("select[name=dept]").val("'.$dept.'");';
        }

        $date = I('get.date');
        if(!empty($date)){
            $condition['s.start'] = array('EQ',$date.'-01');
            $info['dateinfo'] = '$("#searchBox").find("input[name=date]").val("'.$date.'");';
        }

        $cityId = I('get.city');
        /*if(!empty($cityId)){
            $condition['c.id'] = array('EQ',$cityId);
            $info['cityinfo'] = '$("#searchBox").find("select[name=city]").val("'.$cityId.'");';
        }*/
        //$cityList = D('SalesSetting')->getManageCitys();
        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($cityId) && in_array($cityId, $ids)){
                $condition['c.id'] = array('EQ',$cityId);
                $info['cityinfo'] = '$("#searchBox").find("select[name=city]").val("'.$cityId.'");';
                //unset($condition['city']);
            }else{
                $idstr = implode(',', $ids);
                $condition['c.id'] = array('IN',$idstr); 
                //unset($condition['city']);
            }

            //下载Excel
            if(I('get.dl') == '1'){
                $pageCount = 1000;

                //设置表头
                $title = array(
                    '城市','城市级别','部门','计划月分单数','生效月份','操作时间','操作人',
                );

                //设置表列
                $column = array(
                    'city','level','dept','point','start','lasttime','name',
                );

                $result = $this->getSettingValueList($condition,$pageIndex,$pageCount);
                $this->downExcel($title,$column,$result['list'],'计划月分单量');
                die;
            }elseif(I('get.dltpl') == '1'){
                $pageCount = 1000;

                //设置表头
                $title = array(
                    '城市','计划月分单数','生效月份',
                );

                //设置表列
                $column = array(
                    'city'
                );

                $result = $this->getSettingValueList($condition,$pageIndex,$pageCount);
                $this->downExcel($title,$column,$cityList,'计划月分单量模板');
                die;

            }else{
                $result = $this->getSettingValueList($condition,$pageIndex,$pageCount);
            }
        }
        
        $this->assign('info',$info);
        $this->assign('totalnum',$result['pageCount']);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('cityList',$cityList);
        $this->display();
    }

    /**
     * ajax修改单条计划月分单量
     */
    public function editjhyfds()
    {
        $id         = I('post.id');
        $data       = I("post.arr");
        $result     = D('Citypoint')->editjhyfds($id,$data);

        if($result == 1){
            //写入操作日志  opid optime opip optype opdes content
            $logData['opid']        = $_SESSION['uc_userinfo']['id'];
            $logData['optime']      = time();
            $logData['opip']        = get_client_ip();
            $logData['optype']      = 2;
            $logData['opdes']       = '修改计划月分单量';
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

    /**
     * 计划月分单量 上传excel
     */
    public function jhyfdsUploadExcel(){

        //分析Excel内容
        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.'/'.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = importExcel($filename);

        if(count($excel['0'])  != 3 ){
            $this->ajaxReturn(array('data' => '','info' => '数据格式不正确','status' => 'error'));
        }
        unset($excel['0']);

        //逐行导入数据
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }

            //判断城市是否存在
            $city = D('SalesSetting')->getCityManageByName(trim($v['0']));
            if(empty($city)){
                continue;
            }

            $date = explode('-',$v['2']);
            $start_time = date('Y-m-d',mktime(0,0,0,$date['1'],1,$date['0']));
            
            $map['module'] = array('EQ','4');
            $map['manage_id'] = array('EQ',$city['id']);
            $map['start'] = array('EQ',$start_time);

            $user = session('uc_userinfo');

            //构造数据
            //$data['typeid'] = '';
            $data['module'] = '4';
            $data['manage_id'] = $city['id'];
            $data['point'] = $v['1'];
            $data['start'] = $start_time;
            $data['uid'] = $user['id'];
            $data['lasttime'] = date('Y-m-d');
            $data['status'] = '1';

            $isExist = M('sales_setting_value')->field('*')->where($map)->find();
            
            if(!empty($isExist)){
                D('SalesSetting')->editSettingCityValues($isExist['id'],$data);
            }else{//不存在
                M('sales_setting_value')->add($data);
            }
        }
        //写入操作日志  opid optime opip optype opdes content
        $logData['opid']        = $_SESSION['uc_userinfo']['id'];
        $logData['optime']      = time();
        $logData['opip']        = get_client_ip();
        $logData['optype']      = 10;
        $logData['opdes']       = '批量导入计划月分单量';
        $num                    = count($excel);
        $logData['content']     = '批量导入计划月分单量'.$num.'条';
        $log = D('Citypoint')->addLog($logData);
        $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
    }

    /**
     * 会员新签续费互转
     */
    public function hyxqxfhz(){
        //搜索功能
        $req = I('get.');
        $requestMap = array(
            "city","dept","dev_division","dev_regiment","dev_manage","open_status",
            "level","corps","brand_division","brand_regiment","brand_manage",'p','user'
        );
        foreach ($req as $key => $value) {
            if(!in_array($key,$requestMap)){
                $this->error('Bad Request!');
            }
            if(!empty($value) && $key != 'p'){

                $temp = '$("#searchBox").find("select[name='.$key.']").val("'.$value.'");';

                if($key == 'user'){
                    $condition['s.manage_id'] = array('EQ',$value);
                    $temp = '$("#searchBox").find("input[name='.$key.']").val("'.$value.'");';
                }elseif($key == 'city'){
                    $condition['m.id'] = array('EQ',$value);
                }else{
                    $condition['m.'.$key] = array('EQ',$value);
                }
                $info['searchSelect'] .= $temp;
            }
        }

        //取所有销售帐号
        $saleUsers = D('SalesSetting')->getSaleUsers();

        //分页
        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
        //查询管理的城市
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($condition['m.id']) && in_array($condition['m.id'][1], $ids)){
                $condition['m.id'] = $condition['m.id'];
                //unset($condition['city']);
            }else{
                $idstr = implode(',', $ids);
                $condition['m.id'] = array('IN',$idstr); 
                //unset($condition['city']);
            }

            $result = $this->getHyxqxfhzList($condition,$pageIndex,$pageCount);
        }

        
        $list = $result['list'];
        //dump($list);

        foreach ($list as $key => $value) {
            $list[$key]['brand_division'] = getSaleUserName($value['brand_division']);
            $list[$key]['brand_manage'] = getSaleUserName($value['brand_manage']);
            $list[$key]['brand_regiment'] = getSaleUserName($value['brand_regiment']);
            $list[$key]['dev_division'] = getSaleUserName($value['dev_division']);
            $list[$key]['dev_manage'] = getSaleUserName($value['dev_manage']);
            $list[$key]['dev_regiment'] = getSaleUserName($value['dev_regiment']);
            $list[$key]['dept'] = $value['dept'] == '1' ? '商务' : '外销';
            $list[$key]['act_uid'] = getSaleUserName($value['act_uid']);
        }
        //dump($list);

        //$cityList = D('SalesSetting')->getManageCitys();

        $user = session('uc_userinfo');
        $info['pageCount'] = $result['pageCount'];
        $info['today'] = date('Y-m-d',time());
        $info['username'] = $user['name'];

        $this->assign('info',$info);
        $this->assign("list",$list);
        $this->assign('page',$result['page']);
        $this->assign('cityList',$cityList);
        $this->assign('saleUsers',$saleUsers);
        $this->display();
    }

     /**
     * 新签续费互转 增加修改
     *
     */
    public function hyxqxfhzAct(){
        if(IS_POST){
            $post = I('post.');

            $user = session('uc_userinfo');

            $id = $post['id'];
            $data['module'] = '5';
            $data['manage_id'] = $post['companyid'];
            $data['point'] = $post['xqxf'];
            $data['remark'] = $post['remark'];

            $data['status'] = '1';
            $data['uid'] = $user['id'];
            $data['lasttime'] = date('Y-m-d');

            //如果不为空为新增
            if(empty($id)){
                //dump('add');
                //dump($data);
                //die;
                if (M('sales_setting_value')->add($data)){
                    //写入操作日志  opid optime opip optype opdes content
                    $logData['opid']        = $_SESSION['uc_userinfo']['id'];
                    $logData['optime']      = time();
                    $logData['opip']        = get_client_ip();
                    $logData['optype']      = 11;
                    $logData['opdes']       = '添加会员新签续费互转';
                    $logData['content']     = json_encode($data);
                    $log = D('Citypoint')->addLog($logData);

                    $this->ajaxReturn(array('data'=>'','info'=>'增加成功','status'=>1));
                }
            }else{
                if (M("sales_setting_value")->where(array('id'=>$id))->save($data)){
                    //写入操作日志  opid optime opip optype opdes content
                    $logData['opid']        = $_SESSION['uc_userinfo']['id'];
                    $logData['optime']      = time();
                    $logData['opip']        = get_client_ip();
                    $logData['optype']      = 11;
                    $logData['opdes']       = '修改会员新签续费互转';
                    $logData['content']     = json_encode($data);
                    $log = D('Citypoint')->addLog($logData);

                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
                }
            }

            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }


    /**
     * 新签续费互转 Ajax 获取会员信息
     */
    public function getcompanybyuid(){
        $keyword = $_GET['text'];

        if(!empty($keyword)){
            $map = array(
                "id"        =>  array('like',$keyword.'%'),
                "classid"   =>  array('EQ','3'),
            );

            $buildSql = M("user")
                        ->field("id as uid,user,qc,jc,cs")
                        ->order('`on`,uid desc')
                        ->where($map)
                        ->limit('0,10')
                        ->buildSql();

            $result = M("user")->table($buildSql)->alias("u")
                        ->field("u.*,FROM_UNIXTIME(b.contract_start,'%Y-%m-%d') as allstart,FROM_UNIXTIME(b.contract_end,'%Y-%m-%d') as allend,q.cname,m.*,v.type")
                        ->join('LEFT JOIN qz_user_company b on u.uid = b.userid')
                        ->join('LEFT JOIN qz_quyu q on q.cid = u.cs')
                        ->join('LEFT JOIN qz_sales_city_manage m on m.city = q.cname')
                        ->join('LEFT JOIN qz_user_vip v on v.company_id = u.uid')
                        ->order('uid')
                        ->select();

            /*dump(M()->getLastSql());
            die;*/

            foreach ($result as $key => $value) {
                $result[$key]['brand_division'] = getSaleUserName($value['brand_division']);
                $result[$key]['brand_manage'] = getSaleUserName($value['brand_manage']);
                $result[$key]['brand_regiment'] = getSaleUserName($value['brand_regiment']);
                $result[$key]['dev_division'] = getSaleUserName($value['dev_division']);
                $result[$key]['dev_manage'] = getSaleUserName($value['dev_manage']);
                $result[$key]['dev_regiment'] = getSaleUserName($value['dev_regiment']);
                $result[$key]['dept'] = $value['dept'] == '1' ? '商务' : '外销';
            }

            if(!empty($result)){
                $status = '1';
                $msg    = '查询成功!';
                $data = $result;
            }
        }
        if(empty($data)){
            $status = '0'; //状态
            $msg    = '没有找到,请重新查询!';  //消息
            $data   = '';  //数据
        }
        $this->ajaxReturn(array('data'=>$data,'info'=>$msg,'status'=>$status));
        die();
    }

    /**
     * 城市分单量满足率
     */
    public function csfdlmzl(){

        $cityList = D('SalesSetting')->getManageCitys();

        $user = session('uc_userinfo');
        $info['pageCount'] = $result['pageCount'];
        $info['today'] = date('Y-m-d',time());
        $info['username'] = $user['name'];

        $this->assign('info',$info);
        $this->assign("list",$list);
        $this->assign('page',$result['page']);
        $this->assign('cityList',$cityList);
        $this->assign('saleUsers',$saleUsers);
        $this->display();
    }


    /**
     * 下载Excel
     *
     * @param      array  $title     The title
     * @param      array  $column    The column
     * @param      array  $list      The list
     * @param      string  $filename  The filename
     *
     * @return     mixed
     */
    private function downExcel($title,$column,$list,$filename){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        $level = array('1'=>'地级市','2'=>'区','3'=>'县城','4'=>'县级市','0'=>'-');
        $dept = array('1'=>'商务','2'=>'外销');
        $open_status = array('1'=>'已开站','0'=>'未开站');
        $manage = array('corps','dev_division','dev_regiment','dev_manage','brand_division','brand_regiment','brand_manage');

        //设置表内容
        $j = 1;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            //if(!empty($v['level'])){
            $v['level'] = $level[$v['level']];
            //}
            if(!empty($v['dept'])){
                $v['dept'] = $dept[$v['dept']];
            }
            if(!empty($v['open_status']) || $v['open_status'] == 0){
                $v['open_status'] = $open_status[$v['open_status']];
            }
            if(!empty($v['act_uid'])){
                $v['act_uid'] = getSaleUserName($v['act_uid']);
            }
            if(!empty($v['act_time'])){
                $v['act_time'] = date('Y-m-d',$v['act_time']);
            }
            foreach ($column as $key => $value) {
                if(in_array($value,$manage)){
                    $v[$value] = getSaleUserName($v[$value]);
                }
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValueExplicit($num,(string)$v[$value]);
            }
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
        header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    /**
     * 获取城市职能管辖列表并分页
     *
     * @param      array   $condition  The condition
     * @param      integer  $pageIndex  The page index
     * @param      integer  $pageCount  The page count
     *
     * @return     array   城市列表.
     */
    private function getCityManageList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("SalesSetting")->getCityManageList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"pageCount"=>$count);
    }

    /**
     * 获取 销售系统设置值 列表并分页
     *
     * @param      array   $condition  The condition
     * @param      integer  $pageIndex  The page index
     * @param      integer  $pageCount  The page count
     *
     * @return     array   列表.
     */
    private function getSettingValueList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("SalesSetting")->getSettingValueList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        foreach ($list as $k => $v) {
            if(empty($v['point']) || $v['point'] == '无'){
                $list[$k]['point'] = 0;
            }
        }
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"pageCount"=>$count);
    }

     /**
     * 获取 会员新签续费互转 列表并分页
     *
     * @param      array   $condition  The condition
     * @param      integer  $pageIndex  The page index
     * @param      integer  $pageCount  The page count
     *
     * @return     array   城市列表.
     */
    private function getHyxqxfhzList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("SalesSetting")->getHyxqxfhzList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"pageCount"=>$count);
    }




}