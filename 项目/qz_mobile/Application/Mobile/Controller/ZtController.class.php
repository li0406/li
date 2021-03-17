<?php

namespace Mobile\Controller;
use Common\Enums\PlatFormActivityCompanyEnum;
use Common\Model\Logic\CooperateActivityLogicModel;
use Common\Model\QuyuModel;
use Mobile\Common\Controller\MobileBaseController;

class ZtController extends MobileBaseController{

    public function _initialize(){
        parent::_initialize();
    }

    public function special(){
        $name = I('get.name');

        $info = D('Zt')->getActivity($name);
        if(empty($info)){
            $this->error();
        }

        $info['lefttime'] = ceil(($info['end_time'] - time()) / 86400);
        $info['lefttime'] = str_split($info['lefttime']);

        //状态：0报名中 1正在进行 2 暂停 3结束 5未知
        $status = $this->getStatus($info);

        if($info['name'] == 'crazyjuly'){

            if($status == '0'){
                $this->error('活动还未开始');
            }
            if($status == '2'){
                $this->error('活动已暂停');
            }
            if($status == '3'){
                $this->error('活动已结束');
            }

            //每5分钟更新活动虚拟报名人数
            $crazyjulybmrs = S('Cache:Zt:crazyjuly:bmrs');
            if(empty($crazyjulybmrs)){
                $crazyjulybmrs = $info['fake_enroll_count'];
                $this->updateEnrollCount($info['id']);
                S('Cache:Zt:crazyjuly:bmrs',$crazyjulybmrs,300);
            }
            $info['splitEnrolls'] = str_split($info['fake_enroll_count']);
            $info['cityid'] = $this->cityInfo['id'];
        }

        $info['cityInfo'] = $this->cityInfo;
        $this->assign("info",$info);
        $this->display($info['name']);
    }

    public function complain(){
        $post = I('post.');

        if(empty($post)){
            $cid = I('get.cid');
            $aid = I('get.id');

            if(empty($cid) || empty($aid)){
                $this->error();
            }

            //查询当前有哪些公司参加活动
            $info['company'] = D('Zt')->getEventCompanys($cid,$aid);

            $basic["head"]["title"] = "在线投诉";
            $this->assign("basic",$basic);
            $this->assign("info",$info);
            $this->display();
            die;
        }

        $data['tel'] = $post['tel'];
        $data['name'] = $post['name'];
        $data['company_id'] = $post['company'];
        $data['type'] = $post['reason'];
        $data['note'] = $post['note'];

        $isOrder = D('Zt')->isPostOrder($data['tel']);

        if(empty($isOrder)){
            $this->ajaxReturn(array("data"=>'1',"info"=>"您还没有参加活动，\n无法进行投诉！","status"=>0));
            die;
        }
        $add = D('Zt')->addComplain($data);
        if($add){
            $this->ajaxReturn(array("data"=>'1',"info"=>"我们将在10分钟内处理您的投诉信息，并在1个工作日内告知您处理结果！\n请您耐心等待！","status"=>1));
            die;
        }
        $this->ajaxReturn(array("data"=>'0',"info"=>"投诉失败，请重新投诉","status"=>0));
    }

    public function baoming(){

        $info['julybmrs'] = S('Cache:Zt:crazyjuly:bmrs');

        $yusuan = D("Jiage")->getJiage(false);
        $info["yusuan"] = $yusuan;

        $get = I('get.');

        if(empty($get['name']) || empty($get['tel'])){
            $this->error('系统错误了');
        }

        $info["name"] = $get['name'];
        $info["tel"] = $get['tel'];


        $basic["head"]["title"] = "在线报名";
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->display();
    }

    //报名成功
    public function baomingok(){
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);

            //seo 标题/描述/关键字
            $basic["head"]["title"] = "报名成功";
            $this->assign("basic",$basic);
            $this->display($tmp);
        } else {
            $referer = $_SERVER["HTTP_REFERER"];
            header("location:".$referer);
        }
    }

    //状态：0报名中 1正在进行 2 暂停 3结束 5未知
    private function getStatus($info){
        $time = time();

        //不是暂停 (状态2)
        if($info['status'] != '2'){
            //设个未知状态
            $status = '5';
            //招募中 当前时间小于报名截止时间，并且小于活动开始时间
            if($time < $info['enroll_time'] && $time < $info['start_time']){
                $status = '0';
            }
            //结束    当前时间大于结束时间
            if($time > $info['end_time']){
                $status = '3';
            }
            //正在进行 当前时间大于开始时间小于结束时间
            if($time > $info['start_time'] && $time < $info['end_time']){
                $status = '1';
            }
        }

        if(!empty($status)){
            $info['status'] = $status;
        }

        return $info['status'];
    }

    //更新活动虚拟报名人数
    public function updateEnrollCount($id){
        //随机生成一个数
        $data = mt_rand(1,8);
        D('Zt')->updateEnrollCount($id,$data);
    }


    //353 P2.19.0 活动专题页及后台管理  列表
    public function hdzt(){

        $logic = new CooperateActivityLogicModel();
        //获取get参数
        $getdata = I('get.');
        //获取所有城市（城市列表选择使用）
        $quyumodel = new QuyuModel();
        $allcity = S('Cache:Mobile:Hdzt:Allcity');
        if(!$allcity){
            $allcity = $quyumodel ->getAllCity();
            S('Cache:Mobile:Hdzt:Allcity',$allcity,86400);
        }
        $this -> assign('allcity',$allcity);
        $cityinfo = [];
        if($getdata['bm']){
            $cityinfo = $quyumodel->getCityInfoByBm($getdata['bm']);
        }
        $cityname = $cityinfo['oldName'] ? $cityinfo['oldName'] : '全国'; //城市名称
        $list = $logic->getCooperateActivity($cityinfo['cid'],$getdata['p']);
//        dump($list);die;

        //获取banner
        $bannerinfo = $logic->getCooperateActivityBanner();
        $this->assign('bannerinfo',$bannerinfo ? $bannerinfo : array());


        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('cityname',$cityname);
        $this->display();
    }


    //353 P2.19.0 活动专题页及后台管理  PC活动详情页
    public function hdztdetail()
    {
        $logic = new CooperateActivityLogicModel();
        $id = I('get.id');
        if(!$id){
            $this->_error();
        }
        $info = $logic->getCooperateActivityInfoById($id);
        if(!$info){
            $this->_error();
        }

        $info['detail'] = $info['detail'] ? htmlspecialchars_decode($info['detail']) : '';
        $this->assign("info",$info);

        $this->display();

    }

    //1097 PC&M&APP新增活动专题页
    public function activity818()
    {
        $cs = $this->cityInfo["id"];


        $tianjinList = PlatFormActivityCompanyEnum::gettianjin();
        $chengduList = PlatFormActivityCompanyEnum::getchengdu();
        $wuhuList = PlatFormActivityCompanyEnum::getwuhu();

        switch ($cs) {
            case  340200 :
                $returnList = array_merge($wuhuList, $tianjinList, $chengduList);
                break;
            case 120001 :
                $returnList = array_merge($tianjinList, $chengduList, $wuhuList);
                break;
            default :
                $returnList = array_merge($chengduList, $wuhuList, $tianjinList);
                break;
        }
        $this->assign("companylist", $returnList);



        $this->display();
    }
    public function activityrule()
    {
        $this->display();
    }
     //1117 移动端平台活动制作新落地页
    public function hd818()
    {
        $cs = $this->cityInfo["id"];

        $tianjinList = PlatFormActivityCompanyEnum::gettianjin();
        $chengduList = PlatFormActivityCompanyEnum::getchengdu();
        $wuhuList = PlatFormActivityCompanyEnum::getwuhu();

        switch ($cs) {
            case  340200 :
                $returnList = array_merge($wuhuList, $tianjinList, $chengduList);
                break;
            case 120001 :
                $returnList = array_merge($tianjinList, $chengduList, $wuhuList);
                break;
            default :
                $returnList = array_merge($chengduList, $wuhuList, $tianjinList);
                break;
        }
        $this->assign("companylist", $returnList);



        $this->display();
    }

    // 1145 复制818活动页面，并添加js代码
    public function tteight()
    {
        $cs = $this->cityInfo["id"];

        $tianjinList = PlatFormActivityCompanyEnum::gettianjin();
        $chengduList = PlatFormActivityCompanyEnum::getchengdu();
        $wuhuList = PlatFormActivityCompanyEnum::getwuhu();

        switch ($cs) {
            case  340200 :
                $returnList = array_merge($wuhuList, $tianjinList, $chengduList);
                break;
            case 120001 :
                $returnList = array_merge($tianjinList, $chengduList, $wuhuList);
                break;
            default :
                $returnList = array_merge($chengduList, $wuhuList, $tianjinList);
                break;
        }
        $this->assign("companylist", $returnList);



        $this->display();
    }

    //1325 全民家装双11活动落地页
    public function activity11()
    {
        $cs = $this->cityInfo["id"];
        $this->display();
    }


}