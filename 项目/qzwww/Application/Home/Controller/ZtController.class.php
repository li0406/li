<?php

namespace Home\Controller;
use Common\Enums\PlatFormActivityCompanyEnum;
use Common\Model\Logic\CooperateActivityLogicModel;
use Home\Common\Controller\HomeBaseController;
use Library\Qizuang\JWTAuth\Src\Facade\Auth;

class ZtController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        if (IS_GET) {
            $uri = $_SERVER['REQUEST_URI'];
            preg_match('/html$/', $uri, $m);
            if (count($m) == 0) {
                preg_match('/\/$/', $uri, $m);
                $parse = parse_url($uri);
                if (count($m) == 0 && empty($parse["query"])) {
                    header("HTTP/1.1 301 Moved Permanently");
                    if (isSsl()) {
                        $http = "https://";
                    } else {
                        $http = "http://";
                    }
                    header("Location: " . $http . $_SERVER["HTTP_HOST"] . $uri . "/");
                    die();
                }
            }
        }
        $headerTmp = "";
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
             //导航栏标识
            $this->assign("tabIndex",4);
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
             //导航栏标识
            $this->assign("tabIndex",6);
        }
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }


    //活动主页
    public function index(){
        $info['allViews'] = $this->getViews();
        $this->assign("diary",$diary);
        $this->assign("info",$info);
        $this->display();
    }

    public function show(){
        $id = I('get.id');

        //增加参与人数
        $this->getViews(true);

        if($id == '1'){
            $companyList = S('Cache:Zt:NewVipCompany');
            if(empty($companyList)){
                $companyList = D('Zt')->getNewVipCompany(4);
                S('Cache:Zt:NewVipCompany',$companyList,900);
            }
            $this->assign("companyList",$companyList);
            $this->display('spring');
        }else{
            $this->_empty();
        }
    }


    public function special(){

        $name = I('get.name');

        $info = D('Zt')->getActivity($name);
        if(empty($info)){
            $this->error();
        }

        $info['lefttime'] = ceil(($info['end_time'] - time()) / 86400);
        $info['lefttime'] = str_split($info['lefttime']);

        $status = $this->getStatus($info);


        if($info['name'] == 'crazyjuly'){

            //每5分钟更新活动虚拟报名人数
            $crazyjulybmrs = S('Cache:Zt:crazyjuly:bmrs');
            if(empty($crazyjulybmrs)){
                $crazyjulybmrs = $info['fake_enroll_count'];
                $this->updateEnrollCount($info['id']);
                S('Cache:Zt:crazyjuly:bmrs',$crazyjulybmrs,300);
            }

            $info['splitEnrolls'] = str_split($info['fake_enroll_count']);

        }



        $this->assign("info",$info);
        $this->display($info['name']);
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



    //旧活动增加访问量
    public function getViews($update = false){
        $Data = D("Common/Ask");
        //读取配置文件
        $ztViews = $Data->getOption('zt_all_views',true);
        //如果没有总数，那么重新定义总数，一般在第一次运行时出现
        if(empty($ztViews['allViews'])){
            $ztViews['allViews'] = 27030;
        }
        if($update == true){
            $ztViews['allViews'] = $ztViews['allViews'] + 1;
            //把总数写入数据库
            $Data->updateOption('zt_all_views',serialize($ztViews));
        }
        return $ztViews['allViews'];
    }




    //353 P2.19.0 活动专题页及后台管理  PC活动列表页
    public function hdzt()
    {
        // 站点多域名访问支持 动态化httphost
        $schemeAndHost = getSchemeAndHost();
        $SCHEME_HOST   = $schemeAndHost;
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: " . $SCHEME_HOST['scheme_full'] . 'm.' . C("QZ_YUMING")."/hdzt");
            die();
        }

        $logic = new CooperateActivityLogicModel();
        //获取get参数
        $getdata = I('get.');
        //获取所有城市（城市列表选择使用）
        $citys = S('Cache:Home:Hdzt:Allcity');
        if(!$citys){
            $citys = D("Common/Quyu")->getAllProvinceAndCitys(false);
            unset($citys['']);
            S('Cache:Home:Hdzt:Allcity',$citys,86400);
        }
        $this->assign('citylist',$citys);
        if($getdata['bm']){
            $cityid= D("Common/Quyu")->getCityIdByBm($getdata['bm']);
            $cityinfo = D('Common/Quyu')->getCityById($cityid);
            $getdata['cname'] = $cityinfo['cname'];
            $cityname = $getdata['cname'];
        }else{
//            if($this->cityInfo['cname']){
//                $cityname = $this->cityInfo['cname'];
//            }else{
                $cityname = '全国';
//            }
        }

        //获取banner
        $bannerinfo = $logic->getCooperateActivityBanner();
        $this->assign('bannerinfo',$bannerinfo ? $bannerinfo : array());

        $list = $logic->getCooperateActivity($getdata['bm'],$getdata['p']);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('getdata',$getdata);
        $this->assign("cityname",$cityname);
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

        // 站点多域名访问支持 动态化httphost
        $schemeAndHost = getSchemeAndHost();
        $SCHEME_HOST   = $schemeAndHost;
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: " . $SCHEME_HOST['scheme_full'] . 'm.' . C("QZ_YUMING")."/hdzt/".$id.'.html');
            die();
        }

        $info = $logic->getCooperateActivityInfoById($id);
        if(!$info){
            $this->_error();
        }

        $info['detail'] = $info['detail'] ? htmlspecialchars_decode($info['detail']) : '';
        $this->assign("info",$info);

        $this->display();

    }

    //594 佰怡家专题页制作
    public function subject_byj(){
        $this->display();
    }
    //598 制作欧派落地页
    public function subject_oupai(){
        $this->display();
    }

     //
    public function zxfw(){
        $this->display();
    }


    // 齐装保
    public function qzbao()
    {

        $nowtime = time();
        /**
         * start_time: 开始时间，当前时间小于该时间将无法操作
         * expiration_time: 过期时间，当前时间大于该时间将无法操作
         */
        $info["start_time"] = $nowtime + 5;
        $info["expiration_time"] = $nowtime + 300;
        $info["token_type"] = "apifb";

        $jwt_token = Auth::getToken($info);     // 生成jwt

        $this->assign("token", $jwt_token);
        $this->display();
    }


    // 818 家装节活动专题页
    public function platformactivity()
    {

        $cs = $this->cityInfo["id"];
        if (empty($cs)) {
            $cityInfo = json_decode(getCityInfoByIp());
            $cs = $cityInfo->data->cid;
        }

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
        $this->assign("is_top","0");
        $this->display();
    }

}
