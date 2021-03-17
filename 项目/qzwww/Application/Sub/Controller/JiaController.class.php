<?php

namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;

class JiaController extends SubBaseController{

    public function _initialize(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die();
        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        //header搜索框搜索条件绑定
        $this->assign("header_search",5);

        $headerTmp = "";
        //添加顶部搜索栏信息
        $this->assign('serch_uri','gonglue/search');
        $this->assign('serch_type','装修攻略');
        $this->assign('holdercontent','了解相关的装修资讯知识');

        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
             //导航栏标识
            $this->assign("tabIndex",6);
        }else{
            //显示头部导航栏效果
            $this->assign("nav_show",true);
            //导航栏标识
            $this->assign("tabIndex",6);
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
        }
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);


        //获取报价模版
        $this->assign("order_source",25);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));

        $headerTmp = $this->fetch($t);
        $this->assign('choose_menu', 'jia');
        $this->assign("headerTmp",$headerTmp);
    }

    public function index(){
        $cityName = array();
        //根据城市获取区域
        if(!empty($_SESSION['cityId'])){
            $cityId = $_SESSION['cityId'];
            $bm = $this->bm;//获取bm
            $city = D("Common/jia")->getAreaByCityId($cityId);//获取到所属城市的信息和所有区域

            foreach ($city[0] as $key => $value) {
                $cityName['oldName'] = $city[0]['cname'];
                $cityName['cid'] = $city[0]['cid'];
            }
            $qy = I("get.qy") == "" ? "0" : I("get.qy");
            $qy = intval($qy);
            foreach ($city as $k => $v) {
            //如果有传值获取当前传值，否则默认第一个
               if($qy == $v['aid']){
                    $cityName['qid'] = $v['aid'];
                    $cityName['quName'] = $v['aname'];
                }
            }
        }
        //分页
        $pageIndex = 1;
        $pageCount = 10;
        $tempPage = intval(I('get.p'));
        if(!empty($tempPage)){
            $pageIndex = $tempPage;
            $pageContent ="第".$pageIndex."页";
        }

        $search = I('get.keyword');//获取搜索内容，这里需要过滤
        if(empty($search)){
            //根据区域获取小区的数据
            $xiaoqu = $this->getXiaoqu($cityId,$qy,$pageIndex,$pageCount);
        }else{
            //根据小区名查找小区
            $xiaoqu = $this->getSearchXiaoqu($cityId,$search,$pageIndex,$pageCount);
        }
        //获取对应小区的案例
        foreach ($xiaoqu['XiaoquList'] as $key => $value) {
            $quid = $value['id'];//获取小区的id
            $case = D("Common/jia")->getCaseByXiaoqu($quid);//利用小区的id 通过关联表查找到 caseid
            $xiaoqu['XiaoquList'][$key]['caseNum'] = count($case);//获取查询出的案例数，用于显示
            $coordinates[$key]['mapx'] = $value['mapx'];
            $coordinates[$key]['mapy'] = $value['mapy'];
            $coordinates[$key]['name'] = $value['name'];
            $coordinates[$key]['id'] = $value['id'];

            //此处可优化！
            $uid = array();
            $diffCase = array();
            $i = 0;
            foreach ($case as $k => $v) {
                $uid[$k] = $case[$k]['uid'];//获取公司id，统计发布案例的公司数量
                if($case[$k]['classid'] == '1'){//通过classid区分显示案例
                    $diffCase['classid1'][$i] = $case[$i];
                }else{
                    $diffCase['classid2'][$i] = $case[$i];
                }
                $i++;
            }
            $xiaoqu['XiaoquList'][$key]['case'] = $diffCase;
            $xiaoqu['XiaoquList'][$key]['qcNum'] = count(array_count_values($uid));
            //dump($xiaoqu['XiaoquList'][$key]['case']);
        }

        $info['head']['canonical'] = $this->SCHEME_HOST['scheme_full']. $this->cityInfo["bm"].'.'.C('QZ_YUMING').$_SERVER['REQUEST_URI'];


        //区域导航

        if($qy == '0' && $search == ''){
            $area .= "<dd class='active'><a rel='nofollow' class='areaAction' href=' ".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/jia/?qy=0'>所有</a></dd>";
        }else{
            $area .= "<dd><a class='areaAction' rel='nofollow' href='".$this->SCHEME_HOST['scheme_full']."".$bm.".".C("QZ_YUMING")."/jia/?qy=0'>所有</a></dd>";
        }
        foreach ($city as $ck => $cv) {

            if($qy == $cv["aid"]){
                $area .= "<dd class='active'><a rel='nofollow' class='areaAction' href=' ".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/jia/?qy=".$cv["aid"]." ' data-id='".$cv["aid"]."'>".$cv["aname"]."</a></dd>";
            }else{
                $area .= "<dd><a rel='nofollow' class='areaAction' href=' ".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/jia/?qy=".$cv["aid"]." ' data-id='".$cv["aid"]."'>".$cv["aname"]."</a></dd>";
            }
        }

        $url_str = $qy != '' ? '?qy='.$qy : '?qy = 0';
        $url_str .= $tempPage != '' ? '&p='.$tempPage : '';
        //获取当前页面的参数--str形式
        /*$url_str = $_SERVER["QUERY_STRING"];
        $url_str = $url_str == '' ? '?qy=0' : '?'.$url_str;
        dump($url_str);*/
        $cityName['timeY'] = date('Y',time());
        //获取对应小区的案例
        $this->assign("xiaoqu",$xiaoqu['XiaoquList']);//小区数据
        $this->assign("coordinates",json_encode($coordinates));
        $this->assign("cityName",$cityName);//当前城市、区县的名字和id
        $this->assign("url_str",$url_str);
        $this->assign("area",$area);

        $this->assign("search",$search);
        $this->assign("bm",$bm);

        $reg = "&{2,}";
        $xiaoqu['page'] = preg_replace($reg,"&",$xiaoqu['page']);
        $this->assign("page",$xiaoqu['page']);
        $this->assign("count",$xiaoqu['count']);
        $this->assign("info",$info);
        $this->display();
    }

    //小区详情页
    public function jiainfo(){
        $bm = $this->bm;//获取bm，用于装修案例的超链接
        $x = I('get.id');
        $info = D("Common/jia")->getXiaoquById($x);

        if($bm != $info['0']['bm']){
            $bm = $info['0']['bm'];
            $url = $this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/jia/".I("get.id").".html";
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }

        $pageIndex = 1;     //设置 家装案例的 分页
        $BpageIndex = 1;    //设置 在建案例的 分页
        $pageCount = 15;     //每页显示的案例

        //获得分页
        $tempPage = intval(I('get.p')); //获取 家装案例的 分页 p
        $tempBPage = intval(I('get.b'));//获取 在建案例的 分页 b
        //赋值 家装案例 的p
        if(!empty($tempPage)){
            $pageIndex = $tempPage;
            //$pageContent ="第".$pageIndex."页";
        }
        //赋值 在建案例 的b
        if(!empty($tempBPage)){
            $BpageIndex = $tempBPage;
            //$BpageContent ="第".$pageIndex."页";
        }

        //查询详情页的案例的类型获得案例的数据
        //1、最新装修案例   2、在建案例  3、小区相册
        /*$type = I('get.type') == "" ? "1" : I('get.type');
        if($type == '3'){
            //获取小区的相册
        }else{
            $caseOkResult = $this->getXqCase($x, '1', $pageIndex, $pageCount);
            $caseNoResult = $this->getXqCase($x, '2', $pageIndex, $pageCount);
        }*/
        $caseOkResult = $this->getXqCase($x, '1', $pageIndex, $pageCount);//为详情页加载 家装的案例 --1
        $caseNoResult = $this->getXqCase($x, '2', $BpageIndex, $pageCount);//为详情页加载 在建的案例 --2
        //利用正则处理双分页产生的 & 符号
        $reg = "&{2,}";
        $caseOkResult['page'] = preg_replace($reg,"&",$caseOkResult['page']);
        $caseNoResult['page'] = preg_replace($reg,"&",$caseNoResult['page']);
        //装修案例 和相关分页
        $this->assign("page1",$caseOkResult['page']);
        $this->assign("count1",$caseOkResult['count']);
        $this->assign("case1",$caseOkResult['case']);
        //在建案例 和相关分页
        $this->assign("page2",$caseNoResult['page']);
        $this->assign("count2",$caseNoResult['count']);
        $this->assign("case2",$caseNoResult['case']);
        //小区相册的图片
        $this->assign("img",$info['0']['img']);

        $this->assign("bm",$bm);
        $this->assign("info",$info['0']);
        $this->display();
    }

    //小区地图
    public function map(){
        //小区地图页单独设置header
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $headerTmp = "";
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Meitu:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Case:header");
            }else{
                $t = T("Home@Meitu:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
        }
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);

        //获取小区的id，并查询相关的小区数据
        /*$id=I('get.id');
        if(empty($id)){
            $bm = $this->bm;
            $cid = D('Quyu')->getCityIdByBm($bm);

            $quyuName = D("Common/jia")->getAreaByCityId($cid);
            $randInfo = $this->getXiaoquByRand($quyuName['0']['aid']);
            //根据跳转的小区id，获取城市信息，查询该对应的区县
            $array = array("name"=>"","city"=>$quyuName['0']['cname']);
            //dump($quyuName);
        }else{
            $info = D("Common/jia")->getXiaoquById($id);
            $info['0']['caseCount'] = D("Common/jia")->getCaseByIdCount($id);
            foreach ($info as $key => $value) {
                //单独传入城市+小区名，用于百度地图显示坐标
                $array = array("name"=>$value['name'],"city"=>$value['city']);
            }

            //传入区域id，随机生成4个小区列表
            $randInfo = $this->getXiaoquByRand($info['0']['qid']);
            //根据跳转的小区id，获取城市信息，查询该对应的区县
            $quyuName = D("Common/jia")->getAreaByCityId($info['0']['cid']);
            //获取bm，锁定bm
            $bm = $this->bm;
            if($bm != $info['0']['bm']){
                $bm = $info['0']['bm'];
                $url = "http://".$bm.".".C("QZ_YUMING")."/jia/map/?id=".I("get.id");
                header( "HTTP/1.1 301 Moved Permanently" );
                header( "Location:".$url);
                die();
            }
        }*/
        $bm = $this->bm;
        $cid = D('Common/Quyu')->getCityIdByBm($bm);
        $quyuName = D("Common/jia")->getAreaByCityId($cid);
        //分页
        $pageIndex = 1;
        $pageCount = 10;
        $tempPage = intval(I('get.p'));
        if(!empty($tempPage)){
            $pageIndex = $tempPage;
            //$pageContent ="第".$pageIndex."页";
        }
        $qid = I('get.qy');
        $qid = intval($qid);

        $xiaoqu = $this->getXiaoqu($cid,$qid,$pageIndex,$pageCount);
        foreach ($xiaoqu['XiaoquList'] as $key => $value) {
            $xiaoqu['XiaoquList'][$key]['caseCount'] = D("Common/jia")->getCaseByIdCount($value['id']);
            $coordinates[$key]['mapx'] = $value['mapx'];
            $coordinates[$key]['mapy'] = $value['mapy'];
            $coordinates[$key]['name'] = $value['name'];
            $coordinates[$key]['id'] = $value['id'];
        }
        $reg2 = "&{2,}";
        $xiaoqu['page'] = preg_replace($reg2,"&",$xiaoqu['page']);
        $this->assign('xiaoqu',$xiaoqu['XiaoquList']);//小区信息
        $this->assign('page',$xiaoqu['page']);
        $this->assign('quyu',$quyuName);//区域信息
        $this->assign("coordinates",json_encode($coordinates));
        $this->assign("qid",$qid);
        $this->assign('info',$xiaoqu['XiaoquList']['0']);//
        $this->display();
    }

    /**
     * 获取当前的小区列表
     * $cid,当前城市id
     * $qid,当前的区域id
     */
    //获取小区列表
    private function getXiaoqu($cid,$qid,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Common/jia")->getXiaoquByCityAreaCount($cid,$qid);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","first","last","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $result = D("Common/jia")->getXiaoquByCityArea($cid,$qid,$pageIndex,$pageCount);
            foreach ($result as $key => $value) {
                $xqIdList[$key] = $value['id'];
            }
            return array("XiaoquList"=>$result,"page"=>$pageTmp,"count"=>$count,"xqIdList"=>$xqIdList);
        }
        return null;
    }

    /**
     * 查询对应小区的案例,按照需要的显示个数，
     * id，小区的id
     */
    private function getXqCase($id,$type,$pageIndex = 1,$pageCount = 15)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Common/jia")->getCaseByIdCount($id,$type);

        if($count > 0){
            import('Library.Org.Page.Page');
            import('Library.Org.Page.BPage');
            //自定义配置项
            $config  = array("prev","first","last","next");
            //根据$type   区分 --家装-- --在建--
            //分别调用一个分页模块
            if($type == '1'){
                $page = new \Page($pageIndex,$pageCount,$count,$config);
            }else{
                $page = new \BPage($pageIndex,$pageCount,$count,$config);
            }
            $pageTmp =  $page->show();

            $result = D("Common/jia")->getCaseById($id,$type,$pageIndex,$pageCount);
            return array("case"=>$result,"page"=>$pageTmp,"count"=>$count);
        }
        return null;
    }

    /**
     * 通过给定的小区名，搜索查找小区
     * $cid,当前城市id
     * $name,小区的名字
     */
    public function getSearchXiaoqu($cid,$name,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Common/jia")->getXiaoquByNameCount($cid,$name);
        if( $count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","first","last","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $result = D("Common/jia")->getXiaoquByName($cid,$name,$pageIndex,$pageCount);
            foreach ($result as $key => $value) {
                $xqIdList[$key] = $value['id'];
            }
            return array("XiaoquList"=>$result,"page"=>$pageTmp,"count"=>$count,"xqIdList"=>$xqIdList);
        }
        return null;
    }

    /**
     * 根据map的区域id，随机生成小区,和对应的小区案例数
     */
    public function getXiaoquByRand($qid){
        $result = D("Common/jia")->getXiaoquByRand($qid);
        if (!empty($result)) {
            foreach ($result as $k=> $v) {
                $result[$k]['caseCount'] = D("Common/jia")->getCaseByIdCount($v['id']);

            }
            return $result;
        }
        return null;
    }
    /**
     * [jiaListAjax description] map页面点击下拉菜单更换小区列表
     * @return [type] [description]
     */
    public function jiaListAjax(){
        $qid = intval(I('get.qid'));
        $cid = intval(I('get.cid'));
        dump($cid);
        dump($qid);
        if(!empty($qid)){
            $caseByJs = $this->getXiaoqu($cid,$qid,$pageIndex,$pageCount);
            foreach ($caseByJs['XiaoquList'] as $key => $value) {
                $caseByJs['XiaoquList'][$key]['caseCount'] = D("Common/jia")->getCaseByIdCount($value['id']);
            }
        }

        $rt = '<ul class="house-list">';
        if(!empty($caseByJs)){
            //dump($caseByJs);
            foreach ($caseByJs as $key => $value) {
                $rt .= '<li>
                            <h1><a href="javascript:">'.$value['name'].'</a></h1>
                            <p class="dizhi" title="'.$value['dizhi'].'">'.$value['dizhi'].'</p>
                            <p><span>共'.$value['caseCount'].'套案例</span></p>';
                if($value['logo_path'] != ''){
                    $rt .= '<img src="'.$this->SCHEME_HOST['scheme_full'].OP('QINIU_DOMAIN_JIA').'/'.$value['logo_path'].'-200150.jpg" alt=" '.$value['logo_title'].' ">';
                }else{
                    $rt .= '<img src="/assets/common/pic/logo.jpg" >';
                }
            }
        }
        $rt .= '</li></ul>';
        echo $rt;
    }
}