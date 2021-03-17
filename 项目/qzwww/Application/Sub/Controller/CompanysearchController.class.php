<?php

namespace Sub\Controller;

use Common\Model;
use Sub\Common\Controller\SubBaseController;


class CompanySearchController extends SubBaseController
{

    //首页
    public function index(){
        $bm = $this->cityInfo['bm'];

        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ". $this->SCHEME_HOST['scheme_full'].  C('MOBILE_DONAMES').'/'.$bm.'/company/');
            exit();
        }


        $cityid = $this->cityInfo['id'];
        $companyInfo = [];
        S('Cache:companysearch:Home:'.$bm);
        if(!$companyInfo){
            $top = array("id"=>"","name" =>"不限");

            //获取公司规模选项
            $guimo = D("Common/Guimo")->gethGm();
            array_unshift($guimo,$top);
            $companyInfo["guimo"] = $guimo;

            //获取服务保障列表
            $baozhang = D("Common/Leixing")->getbg();
            array_unshift($baozhang,$top);
            $companyInfo["baozhang"] = $baozhang;

            //获取城市信息
            $result = D("Common/Quyu")->getAreaByFatherId($cityid);
            foreach ($result as $key => $value) {
                $city[] = array(
                    "qz_areaid"=>$value["id"],
                    "oldName"=>$value["name"],
                    "cname"=>$value["name"]
                );
            }
            unset($city[count($city)-1]);
            foreach ($city as $k => $v) {
                $tempCity[] = array("id"=>$v["qz_areaid"],"name"=>$v["oldName"]);
            }

            array_unshift($tempCity,$top);
            $companyInfo["city"] = $tempCity;

            S('Cache:companysearch:Home:'.$bm,$companyInfo,900);
        }

        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = is_numeric(I("get.p"))?I("get.p"):1;
            $pageContent ="第".$pageIndex."页";
        }

        $companyInfo['cs'] = $cityid;
        $url = str_replace(array('/companysearch/','/companysearch'),'', urldecode(__SELF__));
        $url = preg_replace('/&?p=.*&?/i','', $url);
        $url = $url == '?' ? '' : $url;
        $urlDepr = strpos($url,'?') === false ? '?' : '&';
        $isSale = I('get.issale');
        $isCert = I('get.iscert');
        $orderby = I('get.orderby');
        $orderby = urldecode(htmlspecialchars_decode($orderby));
        //301
        if(strpos(__SELF__,'?') == false && $url&&substr($url,-1)!=="/"){
            $redirect = $this->SCHEME_HOST['scheme_full'].$bm.'.'.C('QZ_YUMING') .'/companysearch/' .$url.'/';
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$redirect);
            die();
        }
        //如果url最后字符串存在？和/
        if(substr($url,-1)=="?"){
            $url = substr($url,0,strlen($url)-1);
        }
        if(substr($url,-1)=="/"){
            $url = substr($url,0,strlen($url)-1);
        }

        $urlParams = $this->getUrlParams($url);

        if(!empty($urlParams['fu'])){
            $condition['fw'] = $urlParams['fu'];
        }
        if(!empty($urlParams['f'])){
            $condition['fg'] = $urlParams['f'];
        }
        if(!empty($urlParams['g'])){
            $condition['gm'] = $urlParams['g'];
            $companyInfo['gmstyle'] = $urlParams['g'];
        }
        if(!empty($urlParams['bz'])){
            $condition['bz'] = $urlParams['bz'];
        }
        if(empty($_GET['p'])){
            if(!empty($urlParams['p'])){
                $pageIndex = $urlParams['p'];
            }
        }
        $keyword = I("get.keyword");
        if($keyword){
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $keyword = remove_xss($keyword);
            $this->assign("keyword",$keyword);
            $condition['keyword'] = $keyword;
        }

        //替换城市URL
        $companyInfo["city"] = $this->replaceCateUrl($url,$urlParams['fu'],$companyInfo["city"],'fu',$keyword);
        $companyInfo["guimo"] = $this->replaceCateUrl($url,$urlParams['g'],$companyInfo["guimo"],'g',$keyword);
        $companyInfo["baozhang"] = $this->replaceCateUrl($url,$urlParams['bz'],$companyInfo["baozhang"],'bz',$keyword);
        //获取选择的风格,规模
        $select = array();

        if (!empty($condition['gm'])) {
            foreach ($companyInfo["guimo"] as $key => $value) {
                if ($value['id'] == $condition['gm']) {
                    $select['gm'] = $value;
                }
            }
        }
        if (!empty($condition['bz'])) {
            foreach ($companyInfo["baozhang"] as $key => $value) {
                if ($value['id'] == $condition['bz']) {
                    $select['bz'] = $value;
                }
            }
        }

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

        if (!empty($orderby)) {
            switch ($orderby) {
                case 'case'://案例
                    $condition['orderby'] = 'case_count desc,id';
                    $urlArray['2']['active'] = 'class="active"';
                    $urlArray['2']['icon'] = '';
                    break;
                case 'design'://设计师
                    $condition['orderby'] = 'team_count desc,id';
                    $urlArray['3']['active'] = 'class="active"';
                    $urlArray['3']['icon'] = '';
                    break;
                case 'star'://信赖度
                    $condition['orderby'] = 'comment_score desc,id';
                    $urlArray['1']['active'] = 'class="active"';
                    $urlArray['1']['icon'] = '';
                    break;
                case 'active'://活跃度
                    $condition['orderby'] = 'activte_score desc,id';
                    $urlArray['4']['active'] = 'class="active"';
                    $urlArray['4']['icon'] = '';
                    break;
                default:
                    $condition['orderby'] = 'case_count desc,team_count desc,comment_score desc,info_time';
                    $urlArray['0']['active'] = 'class="active"';
                    $urlArray['0']['icon'] = '';
                    break;
            }
        } else {
            $condition['orderby'] = 'case_count desc,team_count desc,comment_score ';
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
        $result = $this->getList($condition,$pageIndex,$pageCount);
        if(!empty($result)){
            foreach ($result["list"] as $key => $value) {
                $result["list"][$key]['star'] = $this->getStar($value['comment_score']);
            }
            $companyInfo["companyList"] = $result["list"];
            $companyInfo["page"] = $result["page"];
            $companyInfo["count"] = $result["num"];
        }

        //头部分页处理
        $companyInfo['pageNum'] = ceil($result["num"] / $pageCount);
        $companyInfo['thisPage'] = $pageIndex;
        $companyInfo['prevUrl'] = ($pageIndex - 1) < 1 ? '1' : ($pageIndex - 1);
        $companyInfo['nextUrl'] = ($pageIndex + 1) >= $companyInfo['pageNum'] ? $companyInfo['pageNum'] : $pageIndex + 1;
        $companyInfo['prevUrl'] = "/company/".$this->getHeadPage($url,$companyInfo['prevUrl']);
        $companyInfo['nextUrl'] = "/company/".$this->getHeadPage($url,$companyInfo['nextUrl']);

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            $this->assign("isOpen",true);
        }

        $tkd['city'] = $this->cityInfo["name"];
        foreach ($this->cityInfo["child"] as $key => $value) {
            if ($value['qz_areaid'] == $condition['fw']) {
                $tkd['area'] = $value['oldName'];
            }
        }

        //a.17.05.06 20170510
        $keys["title"] = $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '装修公司排名_' . $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '十大装修公司大全-' . $tkd['city'] . '齐装网';
        $keys["keywords"] = $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '装修公司, ' . $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '装修公司排名,' . $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '十大装修公司,' . $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '装修公司大全';
        $keys["description"] = $tkd['city'] . '齐装网汇集了' . $tkd['city'] . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '装修公司排名大全，免费提供' . $tkd['area'] . $select['fg']['name'] . $select['gm']['name'] . '装修公司设计与报价，以及装修知识。免费拨打电话将直接转接到装修公司，无额外收费！';


        $this->assign("keys",$keys);

        $friendLink = S("C:FL:CL:ONE:".$cityid);
        if (empty($friendLink)) {
            $friendLink['hotCity'] = D("Quyu")->getHotCity();
            $friendLink['provinceCity'] = D("Quyu")->getProvinceCityLinkByCid($cityid);
            S("C:FL:CL:ONE:".$cityid, $friendLink, 900);
        }
        if(!isset($_SERVER['REQUEST_URI'][9])){
            //获取友情链接
            $friendLink['link'] = S("C:FL:CL:TWO:".$cityid);
            if (empty($friendLink['link'])) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList($cityid,1,'company-list');
                S("C:FL:CL:TWO:".$cityid, $friendLink['link'], 900);
            }
        }

        //获取本地优质装修公司推荐
        $map = array(
            'orderby' => "case_count desc,team_count desc,comment_score,info_time",
            'cs' => $cityid
        );
        $result = A("Sub/Company")->getCompanyList($map);
        foreach ($result as $key => $value) {
            $result[$key]['star'] = $this->getStar($value['comment_score']);
        }
        $result = $this->getSpecialCard($result);
        $this->assign("recommendList", $result);

        $seoRequest = explode('?',$_SERVER['REQUEST_URI']);

        $info['mobileagent'] = $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$this->cityInfo['bm'].$seoRequest['0'];
        $info['head']['canonical'] = $this->SCHEME_HOST['scheme_full']. $this->cityInfo['bm'].'.'.C('QZ_YUMING').$seoRequest['0'];

        //添加通栏C逻辑
        //获取通栏A
        $info["bigbanner_c"] = S('Cache:Sub:Index:bigbanner_c:'.$bm);
        if(!$info["bigbanner_c"]){
            $info["bigbanner_c"] = D("Advbanner")->getAdvList("home_bigbanner_c",$cityid,3);
            S('Cache:Sub:Index:bigbanner_c:'.$bm,$info["bigbanner_c"],900);
        }

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');

        //顶部预约人数
        if (!S('Sbu:Company:TopNum')) {
            S('Sbu:Company:TopNum', mt_rand(100, 200), 2592000);
        }

        //获取优惠券（专用券/通用券）
        $companyInfo['companyList'] = $this->getSpecialCard($companyInfo['companyList']);

        $this->assign("friendLink",$friendLink);
        $this->assign("cityinfo",$this->cityInfo);
        $this->assign("orderby",$urlArray);
        //header搜索框搜索条件绑定
        $this->assign("header_search",0);
        $this->assign("companyInfo",$companyInfo);
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //获取报价模版
        $this->assign("order_source",170);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));
        $this->assign("info",$info);
        $this->display();
    }


    //获取URL参数
    private function getUrlParams($url){

        if(stripos($url,'list') === false){
            $url = "fu0f0g0bz0p1";
        }else{
            $url = str_replace('list-','',$url);
        }

        //如果URL包含 ? 号
        if(stripos($url,'?') !== true){
            $url = explode('?',$url);
            $url = $url['0'];
        }
        if(stripos($url,'/') !== true){
            $url = explode('/',$url);
            $url = $url['0'];
        }


        $params = explode('|',strtr($url,array('fu'=>'|fu:','f'=>'|f:','g'=>'|g:','bz'=>'|bz:','p'=>'|p:')));
        foreach($params as $k => $v ){
            if(!empty($v)){
                $val = explode(':',$v);
                $sKey[$val['0']] = $val['1'];
            }
        }
        return $sKey;
    }
    /**
     * 分类URL替换
     * @param  [type] $url    [完整的URL]
     * @param  [type] $params [当前参数]
     * @param  [type] $data   [当前数组]
     * @param  [type] $str    [替换前缀]
     * @return [type]         [带URL的数组]
     */
    private function replaceCateUrl($url,$params,$data,$str,$keyword=''){
        if(strpos($url,'list') === false){
            $url = "list-fu0f0g0bz0";
        }

        foreach ($data as $k => $v) {
            $v['id'] = empty($v['id']) ? '0' : $v['id'];
            $data[$k]["checked"] = 0;
            if($v['id'] == $params){
                $data[$k]["checked"] = 1;
            }
            $bm = $this->cityInfo['bm'];

            $data[$k]['url'] ='/companysearch/'.str_replace($str.$params,$str.$v['id'],$url);
            if($keyword){
                $data[$k]['url'] .=  '?keyword='.$keyword;
            }
        }
        return $data;
    }

    /**
     * Gets the data list.
     *
     * @param      array $map The map
     * @param      integer $pageIndex The page index
     * @param      integer $pageCount The page count
     *
     * @return     array    The return data list.
     */
    private function getList($map, $pageIndex = 1, $pageCount = 10)
    {
        import('Library.Org.Page.LitePage');
        $result = D("Home/CompanySearch")->getList($map, ($pageIndex - 1) * $pageCount, $pageCount);
        foreach ($result['result'] as $key => $value) {
            //装修公司预约人数
            $num = S('Sub:Company:YuyueNum:' . $value['id']);
            if (!$num) {
                $num = mt_rand(100, 500);
                S('Sub:Company:YuyueNum:' . $value['id'], $num, 2592000);
            }
            $result['result'][$key]['yuyue_num'] = $num;
        }
        $count = D("Common/Company")->getCompanyCount($map);
        $config = array("prev", "next", 'last');
        $page = new \LitePage($pageIndex, $pageCount, $count, $config);
        $pageTmp = $page->show();
        return array("list" => $result['result'], "page" => $pageTmp, "num" => $count);
    }

    /**
     * Gets the head page.
     *
     * @param      string $url The url
     * @param      <type>  $page   The page
     *
     * @return     string  The head page.
     */
    private function getHeadPage($url, $page)
    {
        $urlDepr = strpos($url, '?') === false ? '?' : '&';
        //去除分页
        if (strpos($url, 'p=') !== false) {
            $url = str_replace($urlDepr . 'p=' . I('get.p'), '', $url);
        }
        return $url . $urlDepr . 'p=' . $page;
    }

    //排序URL替换
    private function replaceUrl($url,$a='',$b='') {
        //dump($url.'|'.$a.'|'.$b);
        $urlDepr = '?';
        if (strpos($url, '&') === true || strpos($url, '?') === true || strpos($url, '?') === 0 || strpos($url, '?')) {
            $urlDepr = '&';
        }
        //存在分页
        if(strpos($url,'p=') !== false){
            $page = I('get.p');
            $url = str_replace($urlDepr.'p='.$page,'',$url);
        }
        if(empty($url)){
            return '?orderby='.$b;
        }
        if(!empty($a)){
            return strtr('/companysearch/'.$url,array("$a" => "$b"));
        }else{
            return "/companysearch/".$url.$urlDepr.'orderby='.$b;
        }
    }


    /**
     * Count the star number.
     *
     * @param      integer $score The score
     *
     * @return     integer  The star.
     */
    private function getStar($score)
    {
        if ($score >= 9) {
            return 5;
        } elseif ($score >= 8 && $score < 9) {
            return 4;
        } elseif ($score >= 6 && $score < 8) {
            return 3;
        } elseif ($score >= 4 && $score < 6) {
            return 2;
        } else {
            return 1;
        }
    }


    //获取专用券/通用券
    private function getSpecialCard($companyinfo){
        $limit = 2;
        //$companyInfo['companyList']
        foreach ($companyinfo as $key => $val){
            //公司id  = $val['id'];
            $companyinfo[$key]['cardlist'] = Model\Db\CardModel::getSpecialCardById($val['id'],$limit);
        }
        return $companyinfo;
    }


}