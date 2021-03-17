<?php

namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class XiongzhanghaoController extends MobileBaseController
{
    private $user = null;
    public function _initialize(){
        parent::_initialize();

        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                $SCHEME_HOST = $this->SCHEME_HOST;
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
            }
        }

        //获取装修公司信息
        if (I("get.id") !== "") {
            $user = $this->getUserInfo(I("get.id"));
            if(count($user) > 0){
                $this->user = $user;
                //获取城市信息
                $this->cityInfo = D("Quyu")->getCityInfoById($user["cs"]);
                $this->cityInfo["name"] = $this->cityInfo["cname"];
                $this->assign("cityInfo",$this->cityInfo);
            }else{
                $this->_error();
            }
        }
    }


    public function index(){

        //通过百度地图获取城市名称，再通过cityname获取cid
        $cityInfo = R("Common/BaiduCityLocation/getLocationByBaidu");

        $bm = $cityInfo['bm'];
        $bmflag = '1';
        if(empty($bm)){
            $bmflag = '0';
        }
        $this->assign("bmflag", $bmflag);

        $cityid = $cityInfo['cid'];

        $content = $cityInfo["cname"];
        if(I("get.keyword") !== ""){
            $keyword = I("get.keyword");
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $info["keyword"] = $keyword;
            $content .= $keyword;
        }


        if(!empty($cityInfo)){
            $prefix = $this->SCHEME_HOST['scheme_full'].C("MOBILE_DONAMES").'/'.$cityInfo['bm'].'/company/list-';
            $result = $this->analyseUrl($_SERVER['REQUEST_URI'], $prefix, array('fu', 'f', 'g', 'bz'));

            if(!$result['checked']){
                $this->_error('页面不存在');
            }

            $navurl = $result['realurl'];
            $param = $result['config'];

            //获取城市信息
            $navbar["area"] = [];//S("Cache:m:navbar:area:".$cityInfo['id']);
            if (!$navbar["area"]) {
                $company["area"] = D("Quyu")->getAreaByFatherId($cityInfo['cid']);
                $navbar['area'] = $this->getNavUrl($company["area"], 'fu', $navurl, $param['fu'], $prefix.'fu0f0g0bz0');
                S("Cache:m:navbar:area:".$cityInfo['cid'],$navbar['area'],3600);
            }

            //获取服务保障列表
            $navbar["baozhang"] = [];//S("Cache:m:navbar:baozhang");
            if (!$navbar["baozhang"]) {
                $company["baozhang"] = D("Common/Leixing")->getbg();
                $navbar['baozhang'] = $this->getNavUrl($company["baozhang"], 'bz', $navurl, $param['bz'], $prefix.'fu0f0g0bz0');
                S("Cache:m:navbar:baozhang",$navbar['baozhang'],3600);
            }

            //获取公司规模选项
            $navbar["guimo"] = [];//S("Cache:m:navbar:guimo");
            if (!$navbar["guimo"]) {
                $company["guimo"] = D("Common/Guimo")->gethGm();
                $navbar['guimo'] = $this->getNavUrl($company["guimo"], 'g', $navurl, $param['g'], $prefix.'fu0f0g0bz0');
                S("Cache:m:navbar:guimo",$navbar['guimo'],3600);
            }
        }


        //获取装修公司列表
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
            $pageContent ="第".$pageIndex."页";
        }

        //根据pc端逻辑获取列表页
        $url = str_replace(array("/" . $bm . '/company/', "/" . $bm . '/company'),'', __SELF__);
        $url = preg_replace('/\&?p=([0-9]*)?.\&?/i','', $url);
        $url = $url == '?' ? '' : $url;
        $urlDepr = strpos($url,'?') === false ? '?' : '&';
        $isSale = I('get.issale');
        $isCert = I('get.iscert');
        $orderby = I('get.orderby');


        //URL替换 -- 此处写的比较随意
        $urlArray = array(
            '0'=> array('name'=> '热门排序','url' => 'hot','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '1'=> array('name'=> '信赖度','url' => 'star','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '2'=> array('name'=> '案例','url' => 'case','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '3'=> array('name'=> '设计师','url' => 'design','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '4'=> array('name'=> '活跃度','url' => 'active','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '5'=> array('name'=> '综合实力','url' => 'shili','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
        );

        foreach ($urlArray as $k => $v) {
            $urlArray[$k]['url'] = $this->replaceUrl($url,$orderby,$v['url']);
        }
        $saleUrl = empty($isSale)?__SELF__.$urlDepr.'issale=1' : str_ireplace(array('?issale=1','&issale=1'),'',__SELF__);
        $companyInfo['isSale'] = !empty($isSale) ? 'checked' : '';
        $companyInfo['isCert'] = !empty($isCert) ? 'checked' : '';
        $companyInfo['saleUrl']  = str_ireplace(array('/&', $urlDepr."p=$pageIndex"),array('/?',''),$saleUrl);


        if(!empty($orderby)){
            switch ($orderby) {
                case 'star'://信赖度 | 口碑
                    $condition['orderby'] = 'comment_score desc,id';
                    $urlArray['1']['active'] = 'xuanzcolor';
                    $urlArray['1']['icon'] = '';
                    break;
                case 'shili'://综合实力
                    $condition['orderby'] = '(COALESCE(case_count,0) + COALESCE(team_count,0)) desc,id';
                    $urlArray['5']['active'] = 'xuanzcolor';
                    $urlArray['5']['icon'] = '';
                    break;
                default:
                    $condition['orderby'] = 'case_count desc,team_count desc,comment_score desc,info_time';
                    $urlArray['0']['active'] = 'class="active"';
                    $urlArray['0']['icon'] = '';
                    break;
            }
        }else{
            $condition['orderby'] = 'case_count desc,team_count desc,comment_score,info_time ';
            $urlArray['0']['active'] = 'class="active"';
            $urlArray['0']['icon'] = '';
        }

        if($isCert == '1'){
            $condition['vip'] = '1';
        }
        if($isSale == '1'){
            $condition['sale'] = '1';
        }


        //设置城市ID
        $condition['cs'] = $cityid;

        //当点击信赖度时获取三天内的评论数最高

        foreach($param as $key => $value){
            if($value != "0"){
                if($key == "g"){
                    $condition["gm"] = $value;
                }elseif($key == 'fu'){
                    $condition['fw'] = $value;
                }elseif($key == "f"){
                    $condition['fg'] = $value;
                }else{
                    $condition[$key] = $value;
                }
            }
        }

        if($orderby == 'star'){

            $week = date("W")-1;
            $result = S('Cache:Company:comment:'.$bm.$week);

            if(empty($result)){

                S('Cache:Company:comment:'.$bm.($week-1), null);
                //获取三天内的评论数

                $threeResult = D("Common/Comment")->getThreeCommentCount($condition);

                foreach($threeResult as $key => $value){
                    $comids[] = $value['comid'];
                }

                if(!empty($comids)){
                    $condition['comids'] = $comids;
                }

                $resultNew = $this->getList($condition,$pageIndex,$pageCount);

                unset($condition['comids']);
                if(!empty($comids)){
                    $condition['comid'] = $comids;
                }

                $resultOld = $this->getList($condition,$pageIndex,$pageCount - count($resultNew['list']));

                $list = array_merge($resultNew['list'], $resultOld['list']);

                $result['list'] = $list;
                $result['page'] = $resultOld['page'];
                $result['num'] = $resultOld['num'];

                S('Cache:Company:comment:'.$bm.$week, $result, 1296000);
            }
        }

        $result = $this->getList($condition,$pageIndex,$pageCount, $keyword);

        if(!empty($result)){
            $companyInfo["companyList"] = $this->getStar($result["list"]);
            $companyInfo["page"] = $result["page"];
            $companyInfo["count"] = $result["num"];
        }

        //$list =  $this->getCompanyList($pageIndex, $pageCount, $keyword, $cityInfo["id"], $param);

        if(empty($keyword)){
            $area = [];
            foreach ($company["area"] as $key => $val) {
                $area[$val['id']] = $val['name'];
            }
            if(empty($area[$param['fu']])){
                $basic["head"]["title"] = $cityInfo["cname"] . "装修公司排名_" . $cityInfo["cname"] . "装修公司大全-" . $cityInfo["cname"] . "齐装网";
                $basic["head"]["keywords"] = $content."装修公司,".$content."装修公司排名,".$content."装修公司大全".$pageContent;
                $basic["head"]["description"] ="齐装网为您提供".$content."装修公司排名以及".$content."装修公司大全查询,并提供免费设计服务，注册即可免费获得4份装修设计与报价！";
            }else{
                $content = $cityInfo["cname"] . $area[$param['fu']];
                $basic["head"]["title"] = $content . '装修公司排名 - '. $cityInfo["cname"] .'齐装网';
                $basic["head"]["keywords"] = $content . '装修公司，'. $content .'装修设计';
                $basic["head"]["description"] = $cityInfo["cname"] . '齐装网汇集了'.$content.'装修公司排名大全，免费为您提供'.$content.'装修公司的装修案例和装修预算方案，让您知道'.$content.'装修公司哪家好，彻底为您解决装修难题。';
            }
        }else{
            //关键字、描述、标题
            $basic["head"]["title"] = $content.",".$content."怎么样".$pageContent;
            $basic["head"]["keywords"] =  $content;
            $basic["head"]["description"] = "齐装网为您提供".$content."的相关信息，".$content."怎么样，找".$content."就上齐装网！";
        }

        if(empty($cityInfo)){
            $info['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/company/';
        }else{
            $info['canonical'] = $cityInfo['bm'].'/company' == trim($_SERVER['REQUEST_URI'],'/') ? $this->SCHEME_HOST['scheme_full'].$cityInfo['bm'].'.'.C('QZ_YUMING').'/company/' : '';

            if(empty($info['canonical'])){
                $pcUrl = explode('?',$_SERVER['REQUEST_URI']);
                $pcUrl = str_replace($cityInfo['bm'].'/','',$pcUrl['0']);
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].$cityInfo['bm'].'.'.C('QZ_YUMING').$pcUrl;
            }
        }

        //根据src获得当前一条记录
        $src = $_GET['src'];
        if(!empty($src)){
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if(empty($weixinResult)){
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $noresult = 0;
        if(empty($companyInfo['companyList'])){
            $noresult = 1;
        }

        $arr_url = explode("?", $_SERVER['REQUEST_URI']);

        $this->assign("url", $arr_url[0]);
        $this->assign("url_2", $arr_url[1]);
        $this->assign("param", $param);
        $this->assign("companyInfo", $companyInfo);
        $this->assign("noresult", $noresult);
        $this->assign("navbar",$navbar);
        $info["list"] = $this->getStar($list["companyList"]);
        $info["page"] = $list["page"];

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info",$info);
        $this->assign("img", $weixinResult['img']);
        $this->assign("basic",$basic);
        $this->display();
    }


    //报价
    public function baojia()
    {
        $this->display();
    }

    //报价结果
    public function baojia_details()
    {
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["huxing"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                $script = $this->getSrcipt();
                $this->assign("script", $script);
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        $this->display();
    }

    private function getSrcipt()
    {
        import('Library.Org.Xiongzhang.Xiongzhang');
        $Xiongzhang = new \Xiongzhang\Xiongzhang();
        $token = $Xiongzhang->getToken();
        $ticket = $Xiongzhang->getTicket($token);
        $host = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER["REQUEST_URI"];
        $time = time();
        $nonce_str = getRandomStr(25);
        $signature = $Xiongzhang->getSignature($nonce_str,$ticket, $host,$time);
        $script =  $Xiongzhang->getSrcipt($time, $nonce_str,$signature,urlencode($host));
        return $script;
    }

            /**
     * 计算价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    private function calculatePrice($mianji,$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积

        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);
        if (empty($price)) {
            $price = 300;
        }

        $total = $price*1.2*$mianji;
        $info["child"]['kt'] = $total*0.25 ;
        $info["child"]['zw'] = $total*0.18;
        $info["child"]['wsj'] = $total*0.16;
        $info["child"]['cf'] = $total*0.08;
        $info["child"]['sd'] = $total*0.25;
        $info["child"]['other'] = $total*0.08;
        $info['total'] = $total;

        return $info;
    }

      /**
     * [analyseUrl /company/list-fu320582f4g27]
     * @param  [type]  $url    [当前访问URL]
     * @param  [type]  $prefix [前缀]
     * @param  [type]  $param  [短参数]
     * @param  boolean $check  [是否检查非法输入]
     * @return [type]          [description]
     */
    private function analyseUrl($url, $prefix, $param, $check = true)
    {
        $realurl = rtrim(strstr($url, '?', true), '/');

        if(empty($realurl)){
            $realurl = rtrim($url,'/');
        }
        //去掉前缀
        $count = 0;
        $result = str_ireplace($prefix, '', $realurl, $count);

        //对非法url输入过滤
        if($check){
            if($count == 0){
                $checked = true;
            }else{
                $str = str_ireplace('/', '\/', $prefix);
                //拼接正则表达式
                $pattern = '/^'.$str.'(';
                foreach ($param as $key => $val) {
                    $pattern = $pattern . $val . '[\d]' . '+';
                }
                $pattern = $pattern . ')$/';
                $i = preg_match($pattern, $realurl);
                $checked = $i == 0 ? false : true;
            }
            $return['checked'] = $checked;
        }

        foreach ($param as $key => $val) {
            $pattern = '/'. $val .'\d+/i';
            $count = preg_match($pattern, $result, $match);
            if($count > 0){
                $k = preg_replace('/\d/s', '', $match[0]);
                $v = preg_replace('/\D/s', '', $match[0]);
                $config[$k] = $v;
            }else{
                //如果没有匹配到设置默认值为0
                $config[$val] = 0;
            }
        }

        //重组url，避免他人乱输入URL造成死链接
        if(array_sum($config) > 0){
            $realurl = $prefix;
            foreach ($config as $key => $val) {
                $realurl = $realurl . $key .$val;
            }
        }

        $return['config'] = $config;
        $return['realurl'] = $realurl;
        return $return;
    }

     /**
     * [getNavUrl 获取导航URL]
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
     * @return [type]        [description]
     */
    public function getNavUrl($datas, $type, $url, $present, $init = '')
    {
        //如果去掉自己之后的分类数后分类ID组合小于等于零,说明是初始化
        if(intval(preg_replace('/\D/s', '', $url)) == 0 && !empty($init)){
            $url = $init;
        }
        $selected = array();

        //去掉当前的
        $reg = '/' . $type . '\d+/i';

        foreach ($datas as $key => $value) {
            $datas[$key]["link"] = preg_replace($reg, $type.$value["id"], $url);
            if($value['id'] == $present){
                $selected = $value;
            }
        }

        $array = array(
           'name' => '不限',
           'link' => preg_replace($reg, $type.'0', $url)
        );
        array_unshift($datas, $array);
        return array('result' => $datas, 'selected' => $selected);
    }

    //排序URL替换
    private function replaceUrl($url,$a='',$b='')
    {
        //dump($url.'|'.$a.'|'.$b);
        $urlDepr = '?';
        if (strpos($url, '&') === true || strpos($url, '?') === true || strpos($url, '?') === 0 || strpos($url, '?')) {
            $urlDepr = '&';
        }
        //存在分页
        if (strpos($url, 'p=') !== false) {
            $page = I('get.p');
            $url = str_replace($urlDepr . 'p=' . $page, '', $url);
        }
        if (empty($url)) {
            return '?orderby=' . $b;
        }
        if (!empty($a)) {
            return strtr('/company/' . $url, array("$a" => "$b"));
        } else {
            return "/company/" . $url . $urlDepr . 'orderby=' . $b;
        }
    }

    private function getList($map,$pageIndex = 1,$pageCount = 10, $keyword){

        import('Library.Org.Page.LitePage');

        $result = D("Common/Company")->getList($map,($pageIndex-1) * $pageCount,$pageCount,$keyword);

        //获取最新
        $ids = [];
        foreach ($result['result'] as $key => $value) {
            $ids[] = $value['id'];
            //装修公司预约人数
            if (!S('Sub:Company:YuyueNum:' . $value['id'])) {
                S('Sub:Company:YuyueNum:' . $value['id'], mt_rand(100, 500), 2592000);
            }
        }

        if(!empty($ids)){
            //获取最新优惠
            $active = D('CompanyActivity')->getCompanyActiveListByIds($ids);
            //获取最新推荐的评论
            $comments = D('Comment')->getCommentByComs($ids);
            foreach ($result['result'] as $key => $value) {
                $result['result'][$key]['active_id'] = $active[$value['id']]['id'];
                $result['result'][$key]['active_title'] = $active[$value['id']]['title'];
                $result['result'][$key]['new_comment'] = $comments[$value['id']]['text'];
                $result['result'][$key]['yuyue_num'] = S('Sub:Company:YuyueNum:' . $value['id']);
            }
        }

        $count = D("Common/Company")->getCompanyCount($map, $keyword);
        import('Library.Org.Page.MobilePage');
        //自定义配置项
        $config  = array("prev","next");
        //移动端分页
        $page = new \MobilePage($pageIndex,$pageCount,$count,$config,"html");
        $pageTmp =  $page->show2();
        //PC端分页
        /*$page = new \LitePage($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();*/
        return array("list"=>$result['result'],"page"=>$pageTmp,"num"=>$count);
    }


    //计算星星
    private function getStar($list){
        $logo = OP('DEFAULT_LOGO');
        foreach ($list as $key => $value) {
            if(empty($value['logo'])){
                $list[$key]["logo"] = $this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN').'/'.$logo;
            }
            if($value["comment_score"] >= 9 ){
                $list[$key]["star"] = 5;
            }elseif($value["comment_score"] >= 8 && $value["comment_score"] < 9){
                $list[$key]["star"] = 4;
            }elseif($value["comment_score"] >= 6 && $value["comment_score"] < 8){
                $list[$key]["star"] = 3;
            }elseif($value["comment_score"] >= 4 && $value["comment_score"] < 6){
                $list[$key]["star"] = 2;
            }else{
                $list[$key]["star"] = 1;
            }
        }
        return $list;
    }


    //装修公司主页
    public function company_home(){
        $id = I("get.id");
        $info["user"] =  $this->user;
        $info['cases'] = D("Cases")->getCasesListByComId(0,3, $info["user"]["cs"]);
        $cityInfo = $this->cityInfo;

        //获取装修公司信息

        //获取装修公司的文化信息图片
        $imgs = D("User")->getCompanyImg($id);
        $info["imgs"] = $imgs;
        //关键字、描述、标题
        $basic["head"]["title"] = $info["user"]["qc"]."-";
        $basic["head"]["keywords"] =  $info["user"]["qc"].",".$info["user"]["qc"]."怎么样";
        $basic["head"]["description"] =  $info["user"]["qc"].$cityInfo["name"]."为您提供"
                                .$cityInfo["name"]."装修设计方案与报价、".$cityInfo["name"]."装修优惠、免费装修咨询预约以及装修案例效果图。";
        $basic["body"]["title"] = $info['user']['jc'];
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("basic",$basic);
        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $this->assign("fi", 321);
        $this->assign("info",$info);
        $this->assign("nav_index",'bh');
        $this->display();
    }

    /**
     * 装修公司案例列表页
     * @return [type] [description]
     */
    public function company_case(){
        $cityInfo = $this->cityInfo;
        $pageIndex = 1;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $pageCount = 10;

        $id = I("get.id");
        //获取装修公司信息
        $info["user"] =  $this->user;
        $result = $this->getCasesListByComId($pageIndex,$pageCount,$id,$info["user"]["cs"]);

        $info["cases"] = $result["cases"];
        $info["page"] = $result["page"];
        //关键字、描述、标题
        $basic["head"]["title"] = $info["user"]["qc"]."装修案例效果图";
        $basic["head"]["keywords"] = $info["user"]["qc"]."装修案例效果图";
        $basic["head"]["description"] = $info["user"]["qc"]."装修案例效果图";
        $basic["body"]["title"] = $info['user']['jc'];
        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING').str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_cases",'bh');
        $this->display();
    }

    /**
     * 设计团队页
     * @return [type] [description]
     */
    public function company_team(){
        $info["title"] = "设计师团队";
        $cityInfo = $this->cityInfo;
        $id = I("get.id");
        $info["user"] =  $this->user;
        //获取设计师团队信息
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $team = $this->getTeamDesignerList($id,"",2,$pageIndex,$pageCount);
        $info["team"] = $team["team"];
        $info["page"] = $team["page"];

        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["user"]["qc"]."设计团队";
        $basic["head"]["keywords"] = $info["user"]["qc"]."设计团队,".$info["user"]["qc"]."设计团师, 装修设计师";
        $basic["head"]["description"] = $info["user"]["qc"]."设计团队";
        $basic["body"]["title"] = $info['user']['jc'];
        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_team",'bh');
        $this->display();
    }

    /**
     * 装修公司评论页面
     * @return [type] [description]
     */
    public function company_comment(){
        $cityInfo = $this->cityInfo;
        $info["title"] = "业主牛评";
        $info["user"] =  $this->user;

        $id = I("get.id");
        //业主评论
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $comments = $this->getComments($id,$cityInfo["id"],$pageIndex,$pageCount,true);
        $info["comments"] = $comments["comments"];
        $info["page"] = $comments["page"];

        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["user"]["qc"]."评价_业主点评";
        $basic["head"]["keywords"] = $info["user"]["qc"]."评价,业主点评";
        $basic["head"]["description"] = $info["user"]["qc"]."评价";
        $basic["body"]["title"] = $info['user']['jc'];
        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_comment",'bh');
        $this->display();
    }




/**
     * 获取用户信息
     * @param  [type] $id [用户编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    private function getUserInfo($id,$cs){
        $result =  D("User")->getUserInfoById($id,$cs);
        $user = array();
        if($result[0]["id"] != 0){
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $contact_q1 = OP('QZ_CONTACT_QQ1');
            $contact_q2 = OP('QZ_CONTACT_QQ2');
            $contact_t400 = OP("QZ_CONTACT_TEL400");
            foreach ($result as $key => $value) {
                if($key == 0){
                    $user["id"] = $value["id"];
                    $user["hengfu"] = $value["hengfu"];
                    $user["img_host"] = $value["img_host"];
                    $user["on"] = $value["on"];
                    $user["qc"] = $value["qc"];
                    $user["jc"] = $value["jc"];
                    $user["kouhao"] = $value["kouhao"];
                    $user["logo"] = $value["logo"];
                    $user["pv"] = $value["pv"];
                    $user["jianjie"] =$filter->fifter_contact($value["jianjie"]);
                    $user["jiawei"] = $value["jiawei"];
                    $user["fake"] = $value["fake"];
                    $user["nickname"] = empty($value["nickname"])== true?"家装咨询顾问":$value["nickname"];
                    $user["nickname1"] = empty($value["nickname1"])== true?"公装咨询顾问":$value["nickname1"];
                    $user["area"] = $value["area"];
                    $user["fw"] = $value["fw"];
                    $user["fg"] = $value["fg"];
                    $user["dcount"] = $value["dcount"];
                    $user["ccount"] = $value["ccount"];
                    $user["avgsj"] = round($value["avgsj"],1);
                    $user["avgfw"] = round($value["avgfw"],1);
                    $user["avgsg"] = round($value["avgsg"],1);
                    $user["avgscore"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,1);
                    $user["avgcount"] = round($value["avgcount"],1) == 0?1:round($value["avgcount"],1);
                    $user["casecount"] = $value["casecount"];
                    $user["video"] = $value["video"];
                    $user["qq"] =  empty($value["qq"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq"];
                    $user["qq1"] = empty($value["qq1"])==true?$contact_q2:($value["on"] != 2 || $value["fake"] !=0)?$contact_q2:$value["qq1"];
                    $user["dz"] = $value["dz"];
                    $user["cal"] = empty($value["cal"])?"":($value["on"] != 2 || $value["fake"] !=0)?"":$value["cal"];
                    $user["cals"] = empty($value["cals"])?$contact_t400:($value["on"] != 2 || $value["fake"] !=0)?$contact_t400:$value["cals"];
                    $user["tel"] = empty($value["tel"])?$contact_t400:($value["on"] != 2 || $value["fake"] !=0)?$contact_t400:$value["tel"];
                    $user["cs"] = $value["cs"];
                    $user["gm"] = $value["gm"];
                    $user["chengli"] = date("Y年m月",strtotime($value["chengli"]));
                    $user["good"] = round(($value["good"]/$value["newcount"])*100,2);
                    $user["oldgood"] =round(($value["oldgood"]/$value["oldcount"])*100,2);
                    $user["evaluation"] = $user["avgcuont"];
                    if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
                        $user["evaluation"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,2);
                    }
                }
                if(!empty($value["hid"])){
                    $sub = array(
                        "name"=>$value["shortname"],
                        "tel" =>$value["htel"],
                        "addr"=>$value["addr"],
                        "qq"=> empty($value["qq3"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq3"],
                        "qq1"=>empty($value["qq4"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq4"],
                        "nickname"=>empty($value["nickname2"])== true?"家装咨询顾问":$value["nickname2"],
                        "nickname1"=>empty($value["nickname3"])== true?"家装咨询顾问":$value["nickname3"]
                             );
                    $user["child"][] = $sub;
                }
            }
        }
        return $user;
    }

     /**
     * 获取案例图片
     * @param  string $comid   [description]
     * @param  string $cs      [description]
     * @param  string $classid [description]
     * @return [type]          [description]
     */
    private function getCasesListByComId($pageIndex,$pageCount,$comid ='',$cs ='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Cases")->getCasesListByComIdCount($comid,$cs,'');

        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list =D("Cases")->getCasesListByComId(($page->pageIndex-1)*$pageCount,$pageCount,$comid,$cs,'');
            return array("cases"=>$list,"page"=>$pageTmp);
       }
       return null;
    }

    /**
     * 获取设计师列表
     * @param  [type] $id        [用户编号]
     * @param  [type] $zw      [职位名称]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    private function getTeamDesignerList($id,$zw,$zt,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("User")->getTeamDesignerListCount($id,$zw,$zt);
        if($count > 0){
           import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config,"html");
            $pageTmp = $page->show();
            //查询设计师资料
            $users = D("User")->getTeamDesignerList($id,$zw,$zt,($page->pageIndex-1)*$pageCount,$pageCount);

            foreach ($users as $key => $value) {
                $ids[] = $value["userid"];
            }

            //获取设计师最新的2个案例数
            $cases = D("Cases")->getDesinerCase($ids,1);
            foreach ($users as $key => $value) {
                foreach ($cases as $k => $val) {
                    if($value["userid"] == $val["userid"]){
                        if($val['img_host'] == 'qiniu' && !empty($val['img_path'])){
                            $users[$key]["caseimg"] = array(
                                                          'url'=>$this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN').'/'.$val['img_path'],
                                                          'alt'=>$users[$key]["name"] . '设计作品'
                                                          );
                        }elseif(!empty($val['img_path'])){
                            $users[$key]["caseimg"] = array(
                                                          'url'=>$this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$val['img_path'].'s_'.$val['img'],
                                                          'alt'=>$users[$key]["name"] . '设计作品'
                                                          );
                        }
                    }
                }
            }
            return array("team"=>$users,"page"=>$pageTmp);
        }
        return null;
    }

     /**
     * 获取业主评论
     * @param  [type] $id    [公司编号]
     * @param  [type] $cs    [所在城市]
     * @param  [type] $limit [显示数量]
     * @param  [type] $reply [是否显示回复]
     * @return [type]        [description]
     */
    private function getComments($id,$cs,$pageIndex,$pageCount,$reply)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Comment")->getCommentByComIdCount($id,$cs);
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config);
            $pageTmp = $page->show();
            $comments = D("Comment")->getCommentByComId($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$reply);
            foreach ($comments as $key => $value) {
                $total = $value["count"];
                if($value["sj"] != 0 && $value["fw"]!= 0 && $value["sg"]!= 0){
                    $total = round((($value["sj"]+$value["fw"]+$value["sg"])/3),1);

                }
                $comments[$key]["totalCount"] = $total;
            }
            return array("comments"=>$comments,"page"=>$pageTmp);
        }
        return null;
    }

}
