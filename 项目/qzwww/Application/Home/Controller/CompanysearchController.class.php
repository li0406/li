<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class CompanySearchController extends HomeBaseController{

    //首页
    public function index(){

        $companyInfo = S('Cache:Company:Home');

        if(!$companyInfo){
            //获取注册公司数量
            $companyInfo["companyCount"] = releaseCount("company");
            //获取最新的业主点评装修公司
            $companyInfo["comments"] = D("Common/Comment")->getNewComment(5);
            //获取最新优惠活动
            $companyInfo["saleInfo"] = D("Common/CompanyActivity")->getCompanyActiveList(5);
            S('Cache:Company:Home',$companyInfo,3600);
        }

        if(I("get.cs") != ""){
            $cs = remove_xss(I("get.cs"));
            $this->assign("cs",$cs);
            $condition['cs'] = $cs;
            $companyInfo['cs'] = $cs;
        }

        if(I("get.keyword") != ""){
            $keyword = I("get.keyword");
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $keyword = remove_xss($keyword);
            $this->assign("keyword",$keyword);
            $condition['keyword'] = $keyword;
        }

        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex =  is_numeric(I("get.p"))?I("get.p"):1;
            $pageContent ="第".$pageIndex."页";
        }

        $isSale = I('get.issale');
        $isCert = I('get.iscert');
        $orderby =I('get.orderby');

        //URL替换 -- 此处写的比较随意
        $urlArray = array(
            '0'=> array('name'=> '热门排序','url' => 'hot','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '1'=> array('name'=> '信赖度','url' => 'star','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '2'=> array('name'=> '案例','url' => 'case','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '3'=> array('name'=> '设计师','url' => 'design','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
            '4'=> array('name'=> '活跃度','url' => 'active','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),
        );

        $url = str_replace(array('/companysearch/','/companysearch'),'', urldecode(__SELF__));
        $url = preg_replace('/&?p=.*&?/i','', $url);
        $url = $url == '?' ? '' : $url;
        $urlDepr = strpos($url,'?') === false ? '?' : '&';

        $orderby = urldecode(htmlspecialchars_decode($orderby));

        foreach ($urlArray as $k => $v) {
            $urlArray[$k]['url'] = $this->replaceUrl($url,$orderby,$v['url']);
        }

        $saleUrl = empty($isSale)?__SELF__.$urlDepr.'issale=1' : str_ireplace(array('?issale=1','&issale=1'),'',__SELF__);
        $companyInfo['isSale'] = !empty($isSale) ? 'checked' : '';
        $certUrl = empty($isCert)?__SELF__.$urlDepr.'iscert=1' : str_ireplace(array('?iscert=1','&iscert=1'),'',__SELF__);
        $companyInfo['isCert'] = !empty($isCert) ? 'checked' : '';
        $companyInfo['saleUrl']  = str_ireplace(array('/&', $urlDepr."p=$pageIndex"),array('/?',''),$saleUrl);
        $companyInfo['certUrl']  = str_ireplace(array('/&', $urlDepr."p=$pageIndex"),array('/?',''),$certUrl);

        /*
        排序方式，相同的，则按照发布文章先后顺序进行排序。

        热门排序      默认按照更新资讯先后顺序进行排序
        信赖度        按照信赖度从高到低进行排序
        案例          从高到低的顺序进行排序
        设计师        从高到低的顺序进行排序
         */


        if(!empty($orderby)){
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
        }else{
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


        $result = $this->getList($condition,$pageIndex,$pageCount);
        if(!empty($result)){
            foreach ($result["list"] as $key => $value) {
                $result["list"][$key]['star'] = $this->getStar($value['comment_score']);
                //$result["list"][$key]['qc'] = highlightWords($value['qc'],$keyword);
            }

            $companyInfo["companyList"] = $result["list"];
            $companyInfo["page"] = $result["page"];

            //$ids = array_map(function($element){return $element['id'];},$companyInfo['companyList']);
            //$ids = implode($ids,',');


            //$companyInfos = D("Common/Company")->getCompanyInfo($ids);
           // dump($companyInfos);
        }

        //dump($result);

        //头部分页处理
        $companyInfo['pageNum'] = ceil($result["num"] / $pageCount);
        $companyInfo['thisPage'] = $pageIndex;
        $companyInfo['prevUrl'] = ($pageIndex - 1) < 1 ? '1' : ($pageIndex - 1);
        $companyInfo['nextUrl'] = ($pageIndex + 1) >= $companyInfo['pageNum'] ? $companyInfo['pageNum'] : $pageIndex + 1;
        $companyInfo['prevUrl'] = $this->getHeadPage($url,$companyInfo['prevUrl']);
        $companyInfo['nextUrl'] = $this->getHeadPage($url,$companyInfo['nextUrl']);

        if(I("get.keyword") == ""){
            $keys["keywords"]=$c["oldName"]."装修公司,".$c["oldName"]."装修公司排名,".$c["oldName"]."装修网";
            $keys["title"]=$c["oldName"]."装修公司_".$c["oldName"]."装修公司排名_".$c["oldName"]."装修公司大全".$pageContent;
            $keys["description"]="找装修公司频道齐装网为您提供:深圳,上海,北京,广州,苏州,郑州,成都,沈阳,武汉,重庆,合肥,西安,厦门,大连等全国各城市装修公司口碑排名信息。";
        }else{
            $keys["title"]='搜索结果页';
        }

        if(!isset($_SERVER['REQUEST_URI'][9])){
            //获取友情链接
            $friendLink = S("C:FL:CL:000001");
            if (!$friendLink) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001",1,'company-list');
                S("C:FL:CL:000001", $friendLink, 900);
            }
            $this->assign("friendLink",$friendLink);
        }

        $this->assign("keys",$keys);
        $this->assign("orderby",$urlArray);
        $this->assign("companyInfo",$companyInfo);

        //获取报价模版
        $this->assign("order_source",25);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));
        //获取底部弹层

        //添加顶部信息
        $this->assign("header_search",0);
        $this->assign("tabIndex",3);
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
        $this->display();
    }

    /**
     * Gets the data list.
     *
     * @param array $map The map
     * @param int $pageIndex The page index
     * @param int $pageCount The page count
     * @return array
     */
    private function getList($map, $pageIndex = 1, $pageCount = 10)
    {
        $count = D("Common/Company")->getCompanyCount($map);
        if (empty($count)) {
            return ['list' => [], 'page' => '', 'num' => 0];
        }
        import('Library.Org.Page.LitePage');
        $page = new \LitePage($pageIndex, $pageCount, $count, ['prev', 'next', 'last']);
        $result = D('CompanySearch')->getList($map, ($page->pageIndex - 1) * $page->pageCount, $page->pageCount);
        return ['list' => $result['result'], 'page' => $page->show(), 'num' => $count];
    }

    /**
     * Gets the head page.
     *
     * @param      string  $url    The url
     * @param      <type>  $page   The page
     *
     * @return     string  The head page.
     */
    private function getHeadPage($url,$page){
        $urlDepr = strpos($url,'?') === false ? '?' : '&';
        //去除分页
        if(strpos($url,'p=') !== false){
            $url = str_replace($urlDepr.'p='.$page,'',$url);
        }
        return $url.$urlDepr.'p='.$page;
    }

    /**
     * Replace The Url
     *
     * @param      string  $url    The url
     * @param      string  $a      parameter A
     * @param      string  $b      parameter B
     *
     * @return     string  The return value
     */
    private function replaceUrl($url,$a='',$b='') {
        if(empty($url)){
            return '?orderby='.$b;
        }
        if(!empty($a)){
            return strtr($url,array("$a" => "$b"));
        }else{
            return $url.'&orderby='.$b;
        }
    }

    /**
     * Count the star number.
     *
     * @param      integer  $score  The score
     *
     * @return     integer  The star.
     */
    private function getStar($score){
        if($score >= 9 ){
            return 5;
        }elseif($score >= 8 && $score < 9){
            return 4;
        }elseif($score >= 6 && $score < 8){
            return 3;
        }elseif($score >= 4 && $score < 6){
            return 2;
        }else{
            return 1;
        }
    }


}