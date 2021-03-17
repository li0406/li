<?php

//城市区域管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class CityareaController extends HomeBaseController{

    //构造
    public function _initialize(){
        parent::_initialize();
        $this->assign('title','区域城市管理 - ');
    }

    //首页
    public function index(){
        $result = D('Adminquyu')->getList();
        //dump($result);
        $this->assign("quyuList",$result);
        $this->assign("nav",7);
        $this->display();
    }

    public function quyu(){
        $cid = $_GET['id'];
        $quyuInfo  = D('Adminquyu')->getQuyu(array('cid'=>$cid));
        $areaList = D('Adminquyu')->getArea(array('fatherid'=>$cid),'orders','100');
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
                D('Adminquyu')->addArea($data);
                $map['id'] = $data['qz_areaid'];
                $map['action'] = 'add_area';
                D('Adminquyu')->addLog($map,$data); //打日志
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
        $result = D('Adminquyu')->getProvince();

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
            $isHave  = D('Adminquyu')->getQuyu($map);
            if($isHave){
                $this->error('二级域名或区划代码已存在，增加失败！');
            }

            $temp  = D('Adminquyu')->getQuyu('','xh DESC');
            $data['xh'] = $temp['0']['xh'] + 1;

            $id = D('Adminquyu')->addQuyu($data);
            if (!empty($id)){

                $map['id'] = $data['cid'];
                $map['action'] = 'add_city';
                D('Adminquyu')->addLog($map,$data); //打日志

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
        $quyuInfo  = D('Adminquyu')->getQuyu(array('cid'=>$id));
        if($_POST){
            $data['cname'] = $_POST['cname'];
            $data['bm'] = $_POST['bm'];
            $data['xh'] = $_POST['xh'];
            $data['px'] = $_POST['px'];
            $data['px_abc'] = $_POST['px_abc'];
            $data['mark_red'] = $_POST['mark_red'];
            $data['little'] = $_POST['little'];
            $data['type'] = $_POST['type'];

            $edit = D('Adminquyu')->editQuyu($id,$data);
            if ($edit){
                $map['id'] = $id;
                $map['action'] = 'edit_city';
                $log = D('Adminquyu')->addLog($map,$data); //打日志
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
        $areaInfo = D('Adminquyu')->getArea(array('qz_areaid'=>$id));
        if($_POST){
            $data['qz_area'] = trim($_POST['qz_area']);
            $data['orders'] = trim($_POST['orders']);
            $edit = D('Adminquyu')->editArea($id,$data);
            if ($edit){
                $map['id'] = $id;
                $map['action'] = 'edit_area';
                D('Adminquyu')->addLog($map,$data); //打日志
                $this->success('编辑成功！');
            }else{
                $this->error('编辑失败！');
            }
        }else{

            $cid = $areaInfo['0']['fatherid'];
            $quyuInfo  = D('Adminquyu')->getQuyu(array('cid'=>$cid));
            $areaList = D('Adminquyu')->getArea(array('fatherid'=>$cid),'orders','100');

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

        $quyuInfo  = D('Adminquyu')->getQuyu(array('cid'=>$cityid));

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
            $this->ajaxReturn('','cname',$status);
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
            $this->ajaxReturn('','bm',$status);
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
            $this->ajaxReturn('','cid',$status);
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


}