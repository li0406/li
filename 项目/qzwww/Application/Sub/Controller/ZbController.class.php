<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class ZbController extends SubBaseController {
    public function _initialize(){
        parent::_initialize();
        //导航栏标识
        $this->assign("tabIndex", 2);

    }

    public function index(){
        //判断是否是手机访问
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $bm =  $this->bm;
        $zbInfo = S('Cache:Zhaobiao');
        $cityInfo = $this->cityInfo;
        if(!$zbInfo){
            //获取申请装修服务列表
            $zbInfo["orders"] = $this->getOrderList();
            //获取最新业主点评
            $zbInfo["comments"] = $this->getComment();
            S("Cache:Zhaobiao",$zbInfo,3600);
        }
        foreach ($zbInfo["orders"] as $k=>$v){
            $zbInfo["orders"][$k]['date'] = date("Y-m-d",time());
        }

        $info["orders"] = $zbInfo["orders"];
        //随机取6条评论信息
        $info["comment"] = array_slice($zbInfo["comments"], mt_rand(1, count($zbInfo["comments"]) - 6), 6);


        //seo 标题/描述/关键字
        $this->assign('cityname',$cityInfo['name']);
        $citys = json_decode($zbInfo["citys"],true);
        $keys["title"] = $cityInfo["name"]."装修招标_免费装修设计与报价-".$cityInfo["name"]."齐装网";
        $keys["keywords"] = $cityInfo["name"]."装修设计,".$cityInfo["name"]."室内装修设计,".$cityInfo["name"]."装修报价,".$cityInfo["name"]."装修报价单";
        $keys["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台，业主可以在齐装网免费发布装修招标，提供".$cityInfo["name"]."装修招标、".$cityInfo["name"]."免费装修设计与报价，免费为业主提供4份室内装修设计方案与报价，并免费获得多套装修设计与报价方案，让您装修省钱省力更省心！。";
        $this->assign("keys",$keys);
        //session_id
        $this->assign("ssid",authcode(session_id(),""));

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');


        //显示头部导航栏效果
        $this->assign("cityinfo",$cityInfo);
        $this->assign("nav_show",true);
        $this->assign("zbInfo",$info);

        //导航栏标识
        $this->assign("tabIndex", 1);
        $this->display("index_p220");
    }

    public function sheji()
    {
        //判断是否是手机访问
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        //session_id
        $this->assign("ssid",authcode(session_id(),""));

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');

        //显示头部导航栏效果
        $this->assign("cityinfo",$this->cityInfo);
        $this->assign("nav_show",true);
        $this->assign("zbInfo",$info);
        //添加默认选中效果
        $this->assign('choose_menu', 'sheji');
        $this->display();
    }

    private function getOrderList(){
        // 获取风格
        $fengge = D("Fengge")->getfg();

        import('Library.Org.Util.App');
        $app = new \App();
        $halfCount = 0;
        for ($i = 0; $i < 50 ; $i++) {
            $xing = $app->getRandXing();
            $sex_array = array("先生","女士");
            $sex = $sex_array[rand(0,1)];
            $sub["name"] = $xing.$sex;
            $mianji = rand(80,120);
            $sub["mianji"] = $mianji;
            $leixing_array = array("半包","全包");
            $seed = rand(0,1);
            $halfCount =  $seed == 0? $halfCount+1:$halfCount;
            if($halfCount > 10){
                $seed = 1;
            }
            $jiage = $seed == 0?$mianji *368:$mianji*688;
            $sub["leixing"] = $leixing_array[$seed];
            $sub["jiage"] = round(($jiage/10000),1)."万元";
            $sub["fengge"] = $fengge[rand(0,count($fengge)-1)]["name"];
            $show_time = mt_rand(10, 600);
            if($show_time >= 60){
                $sub["time"] = floor($show_time/60).'分钟前';
            }else{
                $sub["time"] = $show_time.'秒前';
            }
            $data[] = $sub;
        }
        return $data;
    }

    private function getComment(){
        $comment =D("Comment")->getNewComment(50);
        foreach ($comment as $key => $value) {
            $rand = rand(1,10);
            $comment[$key]["uptime"] = $rand."分钟前";
        }
        shuffle($comment);
        return $comment;
    }


    /**
     * 通过城市名称获取cid
     * @return [type] [description]
     */
    public function getCidByCname(){
        $cname = I('get.city');
        if(!empty($cname)){
            $citys = D("Common/Quyu")->getCityIdByCityName(array($cname));
            if(!empty($citys)){
                cookie('iplookup',$citys['0']['cid'],86400 * 7);
                $this->ajaxReturn(array("data"=>"","info"=>$citys['0']['cid'],"status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }

        /**
     * 根据IP获取城市定位(可替换 getCidByCname )
     * @return [type] [description]
     */
    public function getCityInfoByIp()
    {
        import('Library.Org.Util.App');
        $ip = \App::get_client_ip();
        if (C('APP_ENV') == 'dev') {
            $ip = '223.112.69.58';
        }

        $iptocity = iptocity($ip);
        $cityName = $iptocity[2];

        header("Content-type:text/html;charset=utf-8");
        if (!empty($cityName)) {
            $citys = D("Common/Quyu")->getCityIdByCityName($cityName);
            if(!empty($citys)){
                cookie('iplookup',$citys['0']['cid'],86400 * 7);
                //将城市信息保存到cooke中
                cookie('QZ_CITY',json_encode($citys['0']),array('expire'=>86400 * 7,'domain' => '.'.APP_HTTP_DOMAIN));
                $this->ajaxReturn(array("data"=>$citys['0'],"info"=>"","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }
}