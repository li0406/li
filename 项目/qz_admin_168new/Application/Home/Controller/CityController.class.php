<?php

//城市区域管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\AreaModel;

class CityController extends HomeBaseController
{

    public function _initialize(){
        parent::_initialize();
    }

    public function opencity(){
        //查询
        $info = D("quyu")->getOpenCityInfo();
        foreach ($info as $key => $value) {
            $info[$key]['web'] = 'http://'.$value['bm'].'.'.C("QZ_YUMING");
            $info[$key]['zhaobiao'] = 'http://'.C("MOBILE_DONAMES").'/zhaobiao/?bm='.$value['bm'];
            $info[$key]['baojia'] = 'http://'.C("MOBILE_DONAMES").'/baojia/?bm='.$value['bm'];
            $info[$key]['sheji'] = 'http://'.C("MOBILE_DONAMES").'/sheji/?bm='.$value['bm'];
            $info[$key]['mobile'] = 'http://'.C("MOBILE_DONAMES").'/'.$value['bm'] . '/';
            if ($value['time_add'] == '') {
                $info[$key]['time_add'] = "无";
            }
        }
        $this->assign("info",$info);
        $this->display();
    }

    //首页
    public function index(){
        $result = D('Quyu')->getList();
        //dump($result);
        $this->assign("quyuList",$result);
        $this->assign("nav",7);
        $this->display();
    }

    public function quyu(){
        $cid = $_GET['id'];
        $quyuInfo  = D('Quyu')->getQuyu(array('cid'=>$cid));
        $areaList = D('Quyu')->getArea(array('fatherid'=>$cid),'orders','100');
        if($_POST){
            //取上级邮编
            $cid = trim($quyuInfo['0']['cid']);
            //取已有区域数
            $areaCount = count($areaList);
            $areaCount = $areaCount >= '1' ? $areaCount : '1';
            $qz_area = $_POST['qz_area'];

            if(!empty($_POST['qz_area_all'])){
                $areas = str_replace(array(' ','，',',','、'),',',$_POST['qz_area_all']);
                $areas = array_unique(array_filter(explode(",",$areas))); //数组形式
                foreach ($areas as $key => $value) {
                    if(!empty($value)){
                        $qz_area[] = $value;
                    }
                }
            }

            $areaNumber = count($qz_area);
            for ($i=0; $i <= $areaNumber; $i++) {
                if(empty($qz_area[$i])){
                    continue;
                }
                $data = array();

                $theNumber = str_pad($areaCount,2,"0",STR_PAD_LEFT);
                $data['qz_areaid'] = $cid.$theNumber;
                $data['qz_area'] = $qz_area[$i];
                $data['fatherid'] = $cid;
                $data['orders'] = intval($areaCount);
                D('Quyu')->addArea($data);
                $map['id'] = $data['qz_areaid'];
                $map['action'] = 'add_area';
                D('Quyu')->addLog($map,$data); //打日志
                $areaCount++;
            }
            $this->success('操作成功！');
        }else{
            $quyuInfo['0']['count'] = count($areaList);
            $this->assign("quyuInfo",$quyuInfo['0']);
            $this->assign("areaList",$areaList);
            $this->assign('title',$quyuInfo['0']['cname'].'区域管理 - ');
            $this->display();
        }
    }

    public function addquyu(){
        $result = D('Quyu')->getProvince();

        if($_POST){

            $data['cid'] = trim($_POST['cid']);
            $data['uid'] = trim($_POST['provinceid']);
            $data['cname'] = trim($_POST['cname']);
            $data['bm'] = trim($_POST['bm']);
            $data['little'] = trim($_POST['little']);
            $data['time_add'] = time();
            $data['px'] = substr($data['uid'], 0, 2).'99';
            $data['px_abc'] = substr($data['bm'],0,1).'099';
            $data['type'] = '1';

            //判断这个区划代码或BM是否存在
            $map['bm'] = $data['bm'];
            $map['cid'] = $data['cid'];
            $map['_logic'] = 'or';
            $isHave  = D('Quyu')->getQuyu($map);
            if($isHave){
                $this->error('二级域名或区划代码已存在，增加失败！');
            }

            $temp  = D('Quyu')->getQuyu('','xh DESC');
            $data['xh'] = $temp['0']['xh'] + 1;

            $id = D('Quyu')->addQuyu($data);
            if (!empty($id)){

                $map['id'] = $data['cid'];
                $map['action'] = 'add_city';
                D('Quyu')->addLog($map,$data); //打日志

                $this->success('增加成功！');
            }else{
                $this->error('增加失败！');
            }

        }else{
            $this->assign("provinceList",$result);
            $this->display();
        }
    }

    public function editQuyu(){
        $id = $_GET['id'];
        $quyuInfo  = D('Quyu')->getQuyu(array('cid'=>$id));
        if($_POST){
            $data['cname'] = $_POST['cname'];
            $data['bm'] = $_POST['bm'];
            $data['xh'] = $_POST['xh'];
            $data['px'] = $_POST['px'];
            $data['px_abc'] = $_POST['px_abc'];
            $data['mark_red'] = $_POST['mark_red'];
            $data['little'] = $_POST['little'];
            $data['type'] = $_POST['type'];

            $edit = D('Quyu')->editQuyu($id,$data);
            if ($edit){
                $map['id'] = $id;
                $map['action'] = 'edit_city';
                $log = D('Quyu')->addLog($map,$data); //打日志
                $this->success('编辑成功！');
            }else{
                $this->error('编辑失败！');
            }
        }else{
            $this->assign("quyuInfo",$quyuInfo['0']);
            $this->display();
        }
    }

    public function area(){
        $id = $_GET['id'];
        $areaInfo = D('Quyu')->getArea(array('qz_areaid'=>$id));
        if($_POST){
            $data['qz_area'] = trim($_POST['qz_area']);
            $data['orders'] = trim($_POST['orders']);
            $edit = D('Quyu')->editArea($id,$data);
            if ($edit){
                $map['id'] = $id;
                $map['action'] = 'edit_area';
                D('Quyu')->addLog($map,$data); //打日志
                $this->success('编辑成功！');
            }else{
                $this->error('编辑失败！');
            }
        }else{

            $cid = $areaInfo['0']['fatherid'];
            $quyuInfo  = D('Quyu')->getQuyu(array('cid'=>$cid));
            $areaList = D('Quyu')->getArea(array('fatherid'=>$cid),'orders','100');

            $quyuInfo['0']['count'] = count($areaList);
            $this->assign("quyuInfo",$quyuInfo['0']);
            $this->assign("areaList",$areaList);
            $this->assign("areaInfo",$areaInfo['0']);
            $this->display();
        }
    }

    public function parent(){
        $cs = D("Area")->getOpenCtiys();
        foreach ($cs as $key => &$value) {
            if ('000001' == $value['cid']) {
                unset($cs[$key]);
            }
        }
        $list = array();

        $cityid = trim($_GET['id']);

        $quyuInfo  = D('Quyu')->getQuyu(array('cid'=>$cityid));

        if (empty($cityid)) {
            $list['status'] = 1;
            $list['info']   =  '由于数据较多,请先选择城市后设置';
        }else{
            $list['cityslist'] = array(); //输出的列表
            foreach ($cs as $keys => $values) { //查询的城市 本条记录
                if ($cityid == $values['cid']) {
                    $list['cityslist'][] = $values; //先存起来
                    $xlSortArr   = array();
                    $xlSortArr[] = $cityid; //自己
                    $xlSortArr[] = $values['parent_city']; //相邻1
                    $xlSortArr[] = $values['parent_city1']; //
                    $xlSortArr[] = $values['parent_city2']; //
                    $xlSortArr[] = $values['parent_city3']; //
                    $xlSortArr[] = $values['parent_city4']; //
                    $xlSortArr[] = $values['other_city']; //相邻6
                    $xlSortArr   = array_filter($xlSortArr); //过滤空
                    $xlSortArrvk = array_flip($xlSortArr); //kv 调转的
                    $values['sort_1'] = 0;//固定排序

                    //相邻城市 也加入列表
                    foreach ($cs as $keyxl => $valuexl) {
                        switch ($valuexl['cid']) {
                            case $values["parent_city"]:
                                $valuexl['sort_1']   = 1;//固定排序
                                $list['cityslist'][] = $valuexl;
                                break;
                            case $values["parent_city1"]:
                                $valuexl['sort_1']   = 2;//固定排序
                                $list['cityslist'][] = $valuexl;
                                break;
                            case $values["parent_city2"]:
                                $valuexl['sort_1']   = 3;//固定排序
                                $list['cityslist'][] = $valuexl;
                                break;
                            case $values["parent_city3"]:
                                $valuexl['sort_1']   = 4;//固定排序
                                $list['cityslist'][] = $valuexl;
                                break;
                            case $values["parent_city4"]:
                                $valuexl['sort_1']   = 5;//固定排序
                                $list['cityslist'][] = $valuexl;
                                break;
                            case $values["other_city"]:
                                $valuexl['sort_1']   = 6;//固定排序
                                $list['cityslist'][] = $valuexl;
                                break;
                            default:
                                # code...
                                break;
                        }
                    }
                }
            }
            usort($list['cityslist'],
                function($a, $b){
                    //通过字段sort_1自定义的排序
                    return $a['sort_1'] < $b['sort_1'] ? false : true;
                }
            );
        }

        $this->assign("quyuInfo",$quyuInfo['0']);
        $this->assign("cs",$cs);
        $this->assign("list",$list);
        $this->display();
    }

    public function updateCache(){
        /*
        删除缓存 POST
        http://168.qizuang.com/admincache/command?chkid=4

        group:nosql1
        method:redis_del
        command:Cache:Home:SwitchCity

        开城市一定要清理的缓存key为 
        Cache:Area 区域缓存
        Cache:Quyu:<bm> 新版本v0
        Cache:HardUnknowWord 生僻字缓存
        Cache:Home:SwitchCity 切换城市页
        Cache:SubHome:<bm> 分站首页


        反馈开站结果

        2015年12月18日 14:45:41
        开站 1
        玉溪 yuxi
        网址: http://yuxi.qizuang.com
        移动: http://m.qizuang.com/zhaobiao/?bm=yuxi
        权限: 商务经理 客服主管
        复制: 已经复制南昌
        相邻城市: 如果有需求(默认空)
        切换城市排序: 如果有需求(默认在按照省的最后一个,按照拼音的最后一个)
        切换城市页标红:默认不标红

        反馈到QQ,QQ群

        ①提出开站的地方,一般为商务技术对接群
        ②客服技术对接群
        ③技术部群
        ④陈总
        ①②③④都要反馈到位


        */


    }

    public function getCid(){
        $keyword = $_GET['text'];
        if(!empty($keyword)){
            $map = array(
                "name"         =>  array('like','%'.$keyword.'%')
            );
            $result = M('admin_china_city')->field('cityid,name')->where($map)
                    ->order('name')
                    ->limit('0,10')
                    ->where($map)
                    ->select();
            if(!empty($result)){
                $status = '1';
                $msg    = '查询成功!';
                $data   = '';
                $lists = array();
                foreach ($result as $key => $value) {
                    $value['cityid']          = $value['cityid'];
                    $value['cname'] = $value['name'];
                    $lists[]=$value;
                }
                $data = $lists;
            }
        }
        if(empty($data)){
            $status = '0';
            $msg    = '没有找到,请重新查询!';
            $data   = '';
        }
        $this->ajaxReturn($data,$msg,$status);
        die();
    }

    public function isHaveCname(){
        $cname = trim($_GET['cname']);
        if(!empty($cname)){
            $map['cname'] = $cname;
            $quyu  = M('quyu')->field('*')->where($map)->find();
            if($quyu){
                $status = '1';
            }else{
                $status = '0';
            }
            $this->ajaxReturn(array("data"=>"","info"=>"cname","status"=>$status));
        }
    }

    public function isHaveBm(){
        $bm = trim($_GET['bm']);
        if(!empty($bm)){
            //判断这个BM是否存在
            $map['bm'] = $bm;
            $quyu  = M('quyu')->field('*')->where($map)->find();
            if($quyu){
                $status = '1';
            }else{
                $status = '0';
            }
            $this->ajaxReturn(array("data"=>"","info"=>"bm","status"=>$status));
        }
    }

    public function isHaveCid(){
        $cid = trim($_GET['cid']);
        if(!empty($cid)){
            $map['cid'] = $cid;
            $quyu  = M('quyu')->field('*')->where($map)->find();
            if($quyu){
                $status = '1';
            }else{
                $status = '0';
            }
            $this->ajaxReturn(array("data"=>"","info"=>"cid","status"=>$status));
        }
    }

    //更新前台城市缓存 生成 City JSON 文件
    public function updateCityCache(){

        //强制更新前台 City Array 数据
        $url = 'http://'.C('QZ_YUMINGWWW').'/special/updatecitydata';
        $httpContent = getHttpContent($url);
        $tempFilename = str_replace('common/js/allcity','',$httpContent);
        $tempFilename = str_replace('.js','',$tempFilename);

        if(!is_numeric($tempFilename)){
            echo $httpContent;
            $this->error('未能更新前台City Array数据，操作失败！');
        }

        $filename = $httpContent;
        $filename = str_replace('common/js/','',$filename);
        //dump($filename);

        //把七牛文件名保存到数据库中的 option 表中
        $optionName = 'ALL_CITY_JSON';
        $opId = M('options')->field('*')->where(array('option_name'=>$optionName))->find();
        $data['option_name'] = $optionName;
        //不出意外只有第一次才会出现
        if(empty($opId)){
            $data['option_value'] = $filename;
            $data['option_group'] = 'common';
            $data['autoload'] = 'yes';
            $data['option_remark'] = '前台城市JSON数据的七牛文件名';
            $result = M("options")->add($data);
        }else{
            $data['option_value'] = $filename;
            $result = M("options")->where(array('option_name'=>$optionName))->save($data);
        }

        if ($result){
            $this->success('更新操作成功！新文件：'.$filename);
        }else{
            $this->error('更新操作失败！原因：同一分钟生成相同文件名，数据库更新失败~');
        }
        die();
    }

    public function basecityview(){
        //查询
        $info = D("quyu")->getAllBaseCity();
        //存储城市cid和name的对应关系

        $cityArray = S("C:AdminCityCidName");
        if(!$cityArray){
            $cityArray = array();
            foreach ($info as $key => $value) {
                $cityArray[$value['cid']] = $value['cname'];
            }
            S("C:AdminCityCidName",$cityArray,3600*24);
        }
        //相邻城市数组
        foreach ($info as $key => $value) {
            $neighbor[$value['cid']] = array($value['parent_city'],$value['parent_city1'],$value['parent_city2'],$value['parent_city3'],$value['parent_city4'],$value['other_city4']);
            $neighbor[$value['cid']] = array_filter($neighbor[$value['cid']]);
            $info[$key] = array_filter($info[$key]);//去除空的相邻城市
            $sort[$key] = count($info[$key]);//将数组长度写入
        }
        //根据数组长度排序，相邻城市多的排在前面
        array_multisort($sort,SORT_DESC,$info);

        /**
         * 根据需求进行数组处理
         * 要求：互为相邻城市的只显示地级城市，不互为相邻城市的单独列出来
         * 排除互为相邻城市的，剩下的都为非互为相邻的
         */
        $sArray = array(); //互为相邻城市的，符合条件的
        $eArray = array();
        foreach ($info as $k => $v) {
            //检测当前主体城市是否存在于以确定互为相邻城市的数组中，存在则unset该字段，结束循环
            if (in_array($v['cid'], $sArray)) {
                unset($info[$k]);
                continue;
            }
            //如果相邻城市为空，跳过循环
            if ($v['parent_city'] == '' && $v['parent_city1'] == '' && $v['parent_city2'] == '' && $v['parent_city3'] == '' && $v['parent_city4'] == '' && $v['other_city'] == '') {
                continue;
            }

            //包括主体城市，以及相邻城市的集合
            $nArray = array($v['cid'],$v['parent_city'],$v['parent_city1'],$v['parent_city2'],$v['parent_city3'],$v['parent_city4'],$v['other_city4']);
            $nArray = array_filter($nArray);

            //当前城市的相邻城市
            $cityNeigh = $neighbor[$v['cid']];
            for ($i=0; $i < count($neighbor[$v['cid']]); $i++) {
                $neighbor[$cityNeigh[$i]] = array_filter($neighbor[$cityNeigh[$i]]);
                //判断$nArray（主体在内的相邻城市）和$neighbor（主体的相邻城市的相邻城市）的数组长度,比较差集
                if(count($nArray) >= count($neighbor[$cityNeigh[$i]])){
                    $result = array_diff($nArray,$neighbor[$cityNeigh[$i]]);
                }else{
                    $result = array_diff($neighbor[$cityNeigh[$i]],$nArray);
                }
                //差集数组长度为1时，表示主体城市和相邻城市互为相邻；
                //差集等于$nArray数组长度时，表示相邻城市为空，或者相邻城市的相邻城市与主体城市的相邻城市不一致
                if (count($result) == 1) {
                    array_push($sArray,$cityNeigh[$i]);
                }
                elseif (count($result) == count($nArray)) {
                    array_push($eArray,$cityNeigh[$i]);
                }
                //dump($nArray);
                //dump($neighbor[$cityNeigh[$i]]);
            }
            //unset 包括主体城市，以及相邻城市的集合
            unset($nArray);
        }
        //获取后台订单关联表
        $orderRel = D("quyu")->getOrderRelation();
        //循环处理 以cid为键名，relation为键值，并且去除空键值对
        foreach ($orderRel as $ko => $vo) {
            $order[$vo['cid']] = explode(",",$vo['relation']);
            if ($order[$vo['cid']][0] == "") {
                unset($order[$vo['cid']]);
            }
        }
        //$order = array_filter($order);
        //dump($order);
        //循环将城市cid换成相应的name
        foreach ($info as $key => $value) {
            if ($value['parent_city'] == '' && $value['parent_city1'] == '' && $value['parent_city2'] == '' && $value['parent_city3'] == '' && $value['parent_city4'] == '' && $value['other_city'] == '') {
                if (!in_array($value['cid'],$eArray)) {
                    unset($info[$key]);
                }
                continue;
            }
            //dump(array_key_exists($value['cid'],$order));
            if (array_key_exists($value['cid'],$order)) {
                if(in_array($value['parent_city'],$order[$value['cid']])){
                     $info[$key]['is_parent_city'] = 1;
                }
                if(in_array($value['parent_city1'],$order[$value['cid']])){
                     $info[$key]['is_parent_city1'] = 1;
                }
                if(in_array($value['parent_city2'],$order[$value['cid']])){
                     $info[$key]['is_parent_city2'] = 1;
                }
                if(in_array($value['parent_city3'],$order[$value['cid']])){
                     $info[$key]['is_parent_city3'] = 1;
                }
                if(in_array($value['parent_city4'],$order[$value['cid']])){
                     $info[$key]['is_parent_city4'] = 1;
                }
                if(in_array($value['other_city'],$order[$value['cid']])){
                     $info[$key]['is_other_city'] = 1;
                }
            }

            //将城市id转换成城市name
            if (!empty($value['parent_city'])) {
                $info[$key]['parent_city'] = $cityArray[$value['parent_city']];
            }
            if (!empty($value['parent_city1'])) {
                $info[$key]['parent_city1'] = $cityArray[$value['parent_city1']];
            }
            if (!empty($value['parent_city2'])) {
                $info[$key]['parent_city2'] = $cityArray[$value['parent_city2']];
            }
            if (!empty($value['parent_city3'])) {
                $info[$key]['parent_city3'] = $cityArray[$value['parent_city3']];
            }
            if (!empty($value['parent_city4'])) {
                $info[$key]['parent_city4'] = $cityArray[$value['parent_city4']];
            }
            if (!empty($value['other_city'])) {
                $info[$key]['other_city'] = $cityArray[$value['other_city']];
            }
        }
        //dump($info);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * [orderAdjoinCity 订单相邻城市列表]
     * @return [type] [description]
     */
    public function orderAdjoinCity()
    {
        $info['info'] = D('Quyu')->getQuyuListOnly();
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * [upOrderAdjoinCity 编辑订单相邻城市]
     * @return [type] [description]
     */
    public function upOrderAdjoinCity()
    {

        //处理保存数据
        if(!empty($_POST)){
            $info = I('post.');
            if(empty($info['cid'])){
                $this->_error();
            }
            $relation = array_filter(array_flip(array_flip($info['ids'])));
            $data['relation'] = implode(',', $relation);
            $result =D('OrderCityRelation')->saveRelation($info['cid'], $data);
            if($result){
                $log = array(
                                'remark' => '操作订单关联城市',
                                'logtype' => 'ordercityrelation',
                                'action_id' => $result,
                                'info' => $data
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>'','info'=>'操作订单关联城市成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作订单关联城市失败！请联系技术部门','status'=>0));
        }
        //这里的城市直接取城市
        $citys = D("Quyu")->getAllQuyuOnly();
        $array = array();
        //添加未选择状态
        $array[0] = array(
                          'id' => '0',
                          'text' => '未选择'
                          );
        foreach ($citys as $key => $value) {
            $array[$key+1]  = array(
                                  'id' => $value['cid'],
                                  'text' => strtoupper($value['bm']['0']).' '.$value['cname']
                                  );
        }
        $info['citys'] = json_encode($array);

        //获取某个城市订单相邻城市
        if(!empty($_GET['cid'])){
            $cid = intval($_GET['cid']);
            $result = D('OrderCityRelation')->getRelationByCid($cid);

            //从区域表取出该城市数据
            $emptyquyu = M('quyu')->where(array('cid' => $cid))->field('cid,cname,bm')->select();
            if(empty($emptyquyu)){
                $this->_error('无此城市！');
            }

            if(empty($result)){
                $result = $emptyquyu;
                $result['0']['relation'] = '';
            }else{
                //判断关联表是否有该城市，没有则追加进去
                $flag = true;
                foreach ($result as $key => $value) {
                    if($value['cid'] == $cid){
                        $flag = false;
                    }
                }
                if($flag){
                    $emptyquyu['0']['relation'] = '';
                    array_push($result,$emptyquyu['0']);
                }
            }
            foreach ($result as $key => &$value) {
                if($cid == $value['cid']){
                    //取出当前选择的城市
                    $info['city'] = $value;
                }
                $value['cname'] = strtoupper($value['bm']['0']).' '.$value['cname'];
                $value['relation'] = array_filter(explode(',',$value['relation']));
            }
            $info['info'] = $result;
        }

        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [hotCitySets 编辑热门城市]0.
     * @return [type] [description]
     */
    public function hotCitySets(){
        //查询热门城市
        $hotcitys = D('quyu')->getNewHotCitys();
        foreach ($hotcitys as $k => $v) {
            $show[$k] = $v['isshow'];
            $hotids[$k] = $v['cid'];
        }
        //查询所有城市
        $citys = D("Quyu")->getAllQuyuOnly();
        foreach ($citys as $key => $value) {
            $citys[$key]['cname'] = strtoupper($value['bm']['0']).' '.$value['cname'];
        }
        $info['citys'] = $citys;
        $this->assign('hotids',$hotids);
        $this->assign('show',$show);
        $this->assign('hotcitys',$hotcitys);
        $this->assign("info",$info['citys']);
        $this->display();

    }

    /**
     * [hotCityWriteIn ajax写入热门城市]
     * @return [type] [description]
     */
    public function hotCityWriteIn(){
        if(!empty($_POST['city'])){
            //记录选中项ID，前台显示时使用
            if(!empty($_POST['idstr'])){
                $checkids = explode(',', $_POST['idstr']);
            }

            $hotcitys = explode(',', $_POST['city']);
            //记录热门城市和排序 ,并记录日志
            $arr = [];
            $order = 1;
            foreach ($hotcitys as $k => $v) {
                $arr[$k]['cid'] = $v;
                $arr[$k]['ishotcity'] = 1;
                $arr[$k]['hotorder'] = $order;
                if(!empty($_POST['idstr'])){
                    $arr[$k]['isshow'] = substr($checkids[$k], 0, 10);
                }
                $order ++;
            }
            $result = D('quyu')->setNewHotCitys($arr);
            $this->ajaxReturn(array('data'=>$arr,'info'=>'操作成功','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>$arr,'info'=>'操作失败，请重试','status'=>0));
        }
    }

    /**
     * [getCityForCxSelect 获取CxSelect插件所需数据]
     * @return [type] [description]
     */
    public function getCityForCxSelect(){
        //获取区域
        $result = M('area')->field('qz_areaid AS id,qz_area AS text,fatherid AS parent')->order('fatherid,orders')->select();
        $area = [];
        foreach ($result as $key => $value) {
            $area[$value['parent']][] = array(
                'id' => $value['id'],
                'text' => $value['text']
            );
        }

        //获取城市
        $map = array(
            'cid' => array('NEQ', '000001')
        );
        $result = M('quyu')->field('cid AS id,cname AS text,uid AS parent')->where($map)->order('px')->select();
        $city = [];
        foreach ($result as $key => $value) {
            $city[$value['parent']][] = array(
                'id' => $value['id'],
                'text' => $value['text'],
                'children' => $area[$value['id']]
            );
        }

        //获取省份，并生成需要的数据格式
        $result = M('province')->field('qz_provinceid AS id,qz_province AS text')->select();
        $province = [];
        foreach ($result as $key => $value) {
            $province[] = array(
                'id' => $value['id'],
                'text' => $value['text'],
                'children' => $city[$value['id']]
            );
        }
        $this->ajaxReturn(array('data'=>json_encode($province),'info'=>'操作成功','status'=>1));
    }

    public function getAreaByCity(){
        $get = I('get.');
        $retuenData = [];
        if (!empty($get['cid'])) {
            $areaDb = new AreaModel();
            $area = $areaDb->getChildByCity($get['cid']);
            if (!empty($area)) {
                $this->ajaxReturn(['status' => 1, 'info' => $area]);
            }
        }
        $this->ajaxReturn(['status'=>1,'info'=>$retuenData]);
    }
}