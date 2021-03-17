<?php
/**
 * 移动版装修公司列表页
 */

namespace Mobile\Controller;

use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\QuyuLogicModel;
use Common\Model\Logic\UserLogicModel;
use Mobile\Common\Controller\MobileBaseController;
use Common\Enums\ApiConfig;

class CompanyController extends MobileBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/', $uri, $m);
        if (count($m) == 0) {
            preg_match('/\/$/', $uri, $m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                $SCHEME_HOST = $this->SCHEME_HOST;
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $SCHEME_HOST['scheme_full'] . $SCHEME_HOST['host'] . $uri . "/");
            }
        }
    }

    public function index()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm'];

        if (empty($bm)) {
            $bm = session("m_cityInfo")["bm"];
            if (empty($bm)) {
                $bm = "sz";
            }
        }

        $SCHEME_HOST = $this->SCHEME_HOST;
        $url = $SCHEME_HOST['scheme_full'] . $SCHEME_HOST['host'] . '/' . $bm . $uri;
        preg_match('/\/$/', $url, $m);
        if (count($m) == 0) {
            $url .= "/";
        }
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $url );
        exit();


        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm'];

        $SCHEME_HOST = $this->SCHEME_HOST;

        $bmflag = '1';
        if (empty($bm)) {
            $bmflag = '0';
        }
        $this->assign("bmflag", $bmflag);
        $this->assign('showlocation', 1);//显示页面name为‘location’的meta标签

        $cityid = $cityInfo['id'];

        $keyword = I('get.keyword', '', 'trim');
        if (!empty($keyword)) {
            if (!checkKeyword($keyword)) {
                $this->_error();
            }
            $info["keyword"] = $keyword;
        }

        if (!empty($cityInfo)) {
            $prefix = $SCHEME_HOST['scheme_full'] . $SCHEME_HOST['host'] . '/' . $cityInfo['bm'] . '/company/list-';
            $result = $this->analyseUrl($_SERVER['REQUEST_URI'], $prefix, array('fu', 'f', 'g', 'bz'));

            if (!$result['checked']) {
                $this->_error('页面不存在');
            }

            $navurl = $result['realurl'];
            $param = $result['config'];

            //获取城市信息
            $navbar["area"] = [];//S("Cache:m:navbar:area:".$cityInfo['id']);
            if (!$navbar["area"]) {
                $company["area"] = D("Quyu")->getAreaByFatherId($cityInfo['id']);
                $navbar['area'] = $this->getNavUrl($company["area"], 'fu', $navurl, $param['fu'], $prefix . 'fu0f0g0bz0');
                S("Cache:m:navbar:area:" . $cityInfo['id'], $navbar['area'], 3600);
            }

            //获取服务保障列表
            $navbar["baozhang"] = [];//S("Cache:m:navbar:baozhang");
            if (!$navbar["baozhang"]) {
                $company["baozhang"] = D("Common/Leixing")->getbg();
                $navbar['baozhang'] = $this->getNavUrl($company["baozhang"], 'bz', $navurl, $param['bz'], $prefix . 'fu0f0g0bz0');
                S("Cache:m:navbar:baozhang", $navbar['baozhang'], 3600);
            }

            //获取公司规模选项
            $navbar["guimo"] = [];//S("Cache:m:navbar:guimo");
            if (!$navbar["guimo"]) {
                $company["guimo"] = D("Common/Guimo")->gethGm();
                $navbar['guimo'] = $this->getNavUrl($company["guimo"], 'g', $navurl, $param['g'], $prefix . 'fu0f0g0bz0');
                S("Cache:m:navbar:guimo", $navbar['guimo'], 3600);
            }
        }


        //获取装修公司列表
        $pageIndex = 1;
        $pageCount = 10;
        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
            $pageContent = "第" . $pageIndex . "页";
        }

        //根据pc端逻辑获取列表页
        $url = str_replace(array("/" . $bm . '/company/', "/" . $bm . '/company'), '', __SELF__);
        $url = preg_replace('/\&?p=([0-9]*)?.\&?/i', '', $url);
        $url = $url == '?' ? '' : $url;
        $urlDepr = strpos($url, '?') === false ? '?' : '&';
        $isSale = I('get.issale');
        $isCert = I('get.iscert');
        $orderby = I('get.orderby');


        //URL替换 -- 此处写的比较随意
        $urlArray = array(
            '0' => array('name' => '热门排序', 'url' => 'hot', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '1' => array('name' => '信赖度', 'url' => 'star', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '2' => array('name' => '案例', 'url' => 'case', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '3' => array('name' => '设计师', 'url' => 'design', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '4' => array('name' => '活跃度', 'url' => 'active', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '5' => array('name' => '综合实力', 'url' => 'shili', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
        );

        foreach ($urlArray as $k => $v) {
            $urlArray[$k]['url'] = $this->replaceUrl($url, $orderby, $v['url']);
        }
        $saleUrl = empty($isSale) ? __SELF__ . $urlDepr . 'issale=1' : str_ireplace(array('?issale=1', '&issale=1'), '', __SELF__);
        $companyInfo['isSale'] = !empty($isSale) ? 'checked' : '';
        $companyInfo['isCert'] = !empty($isCert) ? 'checked' : '';
        $companyInfo['saleUrl'] = str_ireplace(array('/&', $urlDepr . "p=$pageIndex"), array('/?', ''), $saleUrl);


        if (!empty($orderby)) {
            switch ($orderby) {
                case 'star'://信赖度 | 口碑
                    $condition['orderby'] = 'comment_score desc,img_count desc,(COALESCE(case_count,0) + COALESCE(team_count,0)) desc,sale_count desc,id';
                    $urlArray['1']['active'] = 'xuanzcolor';
                    $urlArray['1']['icon'] = '';
                    break;
                case 'shili'://综合实力
                    $condition['orderby'] = '(COALESCE(case_count,0) + COALESCE(team_count,0)) desc,img_count desc,comment_score desc,sale_count desc,id';
                    $urlArray['2']['active'] = 'xuanzcolor';
                    $urlArray['2']['icon'] = '';
                    break;
                case 'youhui'://综合实力
                    $condition['orderby'] = 'sale_count desc,(COALESCE(case_count,0) + COALESCE(team_count,0)) desc,img_count desc,comment_score desc,id';
                    $urlArray['3']['active'] = 'xuanzcolor';
                    $urlArray['3']['icon'] = '';
                    break;
                default:
                    $condition['orderby'] = 'img_count desc,comment_score desc,(COALESCE(case_count,0) + COALESCE(team_count,0)) desc,sale_count desc,id';
                    $urlArray['0']['active'] = 'class="active"';
                    $urlArray['0']['icon'] = '';
                    break;
            }
        } else {
            $condition['orderby'] = 'img_count desc,comment_score desc,(COALESCE(case_count,0) + COALESCE(team_count,0)) desc,sale_count desc,id';
            $urlArray['0']['active'] = 'class="active"';
            $urlArray['0']['icon'] = '';
        }

        if ($isCert == '1') {
            $condition['vip'] = '1';
        }
        if ($isSale == '1') {
            $condition['sale'] = '1';
        }


        //设置城市ID
        $condition['cs'] = $cityid;

        //当点击信赖度时获取三天内的评论数最高

        foreach ($param as $key => $value) {
            if ($value != "0") {
                if ($key == "g") {
                    $condition["gm"] = $value;
                } elseif ($key == 'fu') {
                    $condition['fw'] = $value;
                } elseif ($key == "f") {
                    $condition['fg'] = $value;
                } else {
                    $condition[$key] = $value;
                }
            }
        }

        if ($orderby == 'star') {

            $week = date("W") - 1;
            $result = S('Cache:Company:comment:' . $bm . $week);

            if (empty($result)) {

                S('Cache:Company:comment:' . $bm . ($week - 1), null);
                //获取三天内的评论数

                $threeResult = D("Common/Comment")->getThreeCommentCount($condition);

                foreach ($threeResult as $key => $value) {
                    $comids[] = $value['comid'];
                }

                if (!empty($comids)) {
                    $condition['comids'] = $comids;
                }

                $resultNew = $this->getList($condition, $pageIndex, $pageCount);

                unset($condition['comids']);
                if (!empty($comids)) {
                    $condition['comid'] = $comids;
                }

                $resultOld = $this->getList($condition, $pageIndex, $pageCount - count($resultNew['list']));

                $list = array_merge($resultNew['list'], $resultOld['list']);

                $result['list'] = $list;
                $result['page'] = $resultOld['page'];
                $result['num'] = $resultOld['num'];

                S('Cache:Company:comment:' . $bm . $week, $result, 1296000);
            }
        }

        $result = $this->getList($condition, $pageIndex, $pageCount, $keyword);

        //推荐公司

        if (!empty($resultRe)) {
            $companyInfo["companyReList"] = $this->getStar($resultRe["list"]);
        }

        //$list =  $this->getCompanyList($pageIndex, $pageCount, $keyword, $cityInfo["id"], $param);
        if (empty($keyword)) {
            $area = [];
            foreach ($company["area"] as $key => $val) {
                $area[$val['id']] = $val['name'];
            }
            $fullCity = trim($cityInfo['name'] . $area[$param['fu']]);
            $basic['head']['title'] = $fullCity.'装修公司排名_'.$fullCity.'装修公司哪家好_'.$fullCity.'十大装修公司-'.$cityInfo['name'].'齐装网';
            $basic['head']['keywords'] = $fullCity.'装修公司, '.$fullCity.'装修公司排名,'.$fullCity.'装修公司哪家好,'.$fullCity.'十大装修公司';
            $basic['head']['description'] = $cityInfo['name'].'齐装网汇集了'.$fullCity.'装修公司排名大全，帮助您了解'.$fullCity.'装修公司哪家好，免费提供'.$area[$param['fu']].'装修公司设计与报价，以及装修知识。免费拨打电话将直接转接到装修公司，无额外收费！';
        } else {
            $content = $cityInfo['name'].$keyword;
            //关键字、描述、标题
            $basic["head"]["title"] = $content . "," . $content . "怎么样" . $pageContent;
            $basic["head"]["keywords"] = $content;
            $basic["head"]["description"] = "齐装网为您提供" . $content . "的相关信息，" . $content . "怎么样，找" . $content . "就上齐装网！";
        }

        //获取后台配置的TDK
        $config = [
            'cs' => $this->cityInfo['id'], //城市id
            'area' => $tkd['area'], //区域
            'model' => 2, //模块 1.首页 2.装修公司 3.装修资讯
            'category' => '', //装修资讯子频道栏目
            'location' => 2, //位置 1.pc端 2.移动端
            'page' => I('get.p'), //分页
        ];
        $basic["head"] = getCommonManageTdk($basic["head"], $config);

        if (empty($cityInfo)) {
            $info['canonical'] = $SCHEME_HOST['scheme_full'] . 'www.' . $SCHEME_HOST['domain'] . '/company/';
        } else {
            $info['canonical'] = $cityInfo['bm'] . '/company' == trim($_SERVER['REQUEST_URI'], '/') ? $SCHEME_HOST['scheme_full'] . $cityInfo['bm'] . '.' . $SCHEME_HOST['domain'] . '/company/' : '';

            if (empty($info['canonical'])) {
                $query = str_replace('/' . $bm, '', $_SERVER['REQUEST_URI']);
                $locate = strpos($query, 'p=');
                if ($locate !== false) {
                    $replace = substr($query, $locate - 1, 3 + strlen(I('get.p')));
                    $query = str_replace($replace, '', $query);
                }

                $info['canonical'] = $SCHEME_HOST['scheme_full'] . $cityInfo['bm'] . '.' . $SCHEME_HOST['domain'] . $query;
            }
        }

        //根据src获得当前一条记录
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $noresult = 0;
        if (empty($companyInfo['companyList'])) {
            $noresult = 1;
        }

        $arr_url = explode("?", $_SERVER['REQUEST_URI']);
        //获取优惠券（专用券/通用券）
        $companyInfo['companyList'] = $this->getSpecialCard($companyInfo['companyList']);
        $this->assign("url", $arr_url[0]);
        $this->assign("url_2", $arr_url[1]);
        $this->assign("param", $param);
        $this->assign("companyInfo", $companyInfo);
        $this->assign("noresult", $noresult);
        $this->assign("navbar", $navbar);

        $info["list"] = $this->getStar($list["companyList"]);
        $info["page"] = $list["page"];
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $info);
        $this->assign("img", $weixinResult['img']);
        $this->assign("basic", $basic);
        $this->display("index_2815");
    }

    public function indextwo()
    {
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm'];

        $SCHEME_HOST = $this->SCHEME_HOST;

        $url = str_replace(array($bm.'/company/',$bm.'/company'),'', __SELF__);
        $url = preg_replace('/\&?p=([0-9]*)?.\&?/i','', $url);

        $url = $url == '?' ? '' : $url;
        //如果url最后字符串存在？和/
        if(substr($url,-1)=="?"){
            $url = substr($url,0,strlen($url)-1);
        }
        if(substr($url,-1)=="/"){
            $url = substr($url,0,strlen($url)-1);
        }

        $urlParams = $this->getUrlParams($url);

        if ($urlParams['fu'] == 0 && $urlParams['f'] == 0 && $urlParams['g'] == 0 && $urlParams['bz'] == 0 && $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] != C('MOBILE_DONAMES') .'/'.$bm .  '/company' && $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] != C('MOBILE_DONAMES') .'/'.$bm .  '/company/' && empty($_GET['orderby'])) {
            if (empty($url)) {
                $url = '/list-fu0f0g0bz0';
            }

            if(strpos($url,'list-fu') !== false || strpos($url,'?orderby=') !== false){

            }else{
                $this->_empty();die();
            }
        }

        // 城市的区域信息
        $areainfo = S("Cache:mobile:city:areainfo:".$cityInfo['id']);
        if(empty($areainfo)){
            $areainfo = D("Quyu")->getAreaByFatherId($cityInfo['id']);
            if(count($areainfo) > 0){
                unset($areainfo[count($areainfo)-1]);
            }
            S("Cache:mobile:city:areainfo:".$cityInfo['id'],$areainfo,600);
        }

        //404判断
        if (!empty($this->cityInfo['id'])&&!empty($urlParams['fu'])&&!in_array($urlParams['fu'],array_column($areainfo,'id'))){
            $this->_empty();die();
        };
        if (!empty($urlParams['f']) || !empty($urlParams['g']) || !empty($urlParams['bz'])) {
            $this->_empty();die();
        }



        $bmflag = '1';
        if (empty($bm)) {
            $bmflag = '0';
        }
        $this->assign("bmflag", $bmflag);
        $this->assign('showlocation', 1);//显示页面name为‘location’的meta标签

        $cityid = $cityInfo['id'];

        $content = $cityInfo["name"];
        if (I("get.keyword") !== "") {
            $keyword = I("get.keyword");
            if (!checkKeyword($keyword)) {
                $this->_error();
            }
            $info["keyword"] = $keyword;
            $content .= $keyword;
        }


        if (!empty($cityInfo)) {
            $prefix = $SCHEME_HOST['scheme_full'] . $SCHEME_HOST['host'] . '/' . $cityInfo['bm'] . '/company/list-';
            $result = $this->analyseUrl($_SERVER['REQUEST_URI'], $prefix, array('fu', 'f', 'g', 'bz'));

            if (!$result['checked']) {
                $this->_error('页面不存在');
            }

            $navurl = $result['realurl'];
            $param = $result['config'];

            //获取城市信息
            $navbar["area"] = S("Cache:m:navbar:area:".$cityInfo['id']);
            if (!$navbar["area"]) {
                $company["area"] = D("Quyu")->getAreaByFatherId($cityInfo['id']);
                unset($company["area"][count($company["area"])-1]);     // 与PC一致，去掉最后一个， 线上数据好像最后一个是【其他】
                $navbar['area'] = $this->getNavUrl($company["area"], 'fu', $navurl, $param['fu'], $prefix . 'fu0f0g0bz0', $bm);
                S("Cache:m:navbar:area:" . $cityInfo['id'], $navbar['area'], 3600);
            }
        }

        //获取装修公司列表
        $pageIndex = 1;
        $pageCount = 10;
        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
            $pageContent = "第" . $pageIndex . "页";
        }

        //根据pc端逻辑获取列表页
        $url = str_replace(array("/" . $bm . '/company/', "/" . $bm . '/company'), '', __SELF__);
        $url = preg_replace('/\&?p=([0-9]*)?.\&?/i', '', $url);
        $url = $url == '?' ? '' : $url;
        $urlDepr = strpos($url, '?') === false ? '?' : '&';
        $isSale = I('get.issale');
        $isCert = I('get.iscert');
        $orderby = I('get.orderby');


        //URL替换 -- 此处写的比较随意
        $urlArray = array(
            '0' => array('name' => '热门排序', 'url' => 'hot', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '1' => array('name' => '信赖度', 'url' => 'star', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '2' => array('name' => '案例', 'url' => 'case', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '3' => array('name' => '设计师', 'url' => 'design', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '4' => array('name' => '活跃度', 'url' => 'active', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '5' => array('name' => '综合实力', 'url' => 'shili', 'active' => '', 'icon' => '<i class="fa fa-long-arrow-down"></i>'),
            '6' => array('name'=> '量房','url' => 'liangfang','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'), //20200106新增
            '7' => array('name'=> '签单','url' => 'qiandan','active' => '','icon'=>'<i class="fa fa-long-arrow-down"></i>'),//20200106新增
        );

        foreach ($urlArray as $k => $v) {
            $urlArray[$k]['url'] = $this->replaceUrl($url, $orderby, $v['url']);
        }
        $saleUrl = empty($isSale) ? __SELF__ . $urlDepr . 'issale=1' : str_ireplace(array('?issale=1', '&issale=1'), '', __SELF__);
        $companyInfo['isSale'] = !empty($isSale) ? 'checked' : '';
        $companyInfo['isCert'] = !empty($isCert) ? 'checked' : '';
        $companyInfo['saleUrl'] = str_ireplace(array('/&', $urlDepr . "p=$pageIndex"), array('/?', ''), $saleUrl);


        if (!empty($orderby)) {
            switch ($orderby) {
                case 'star'://信赖度 | 口碑
                    $urlArray['1']['active'] = 'xuanzcolor';
                    $urlArray['1']['icon'] = '';
                    break;
                case 'liangfang':  //最新量房
                    $urlArray['6']['active'] = 'xuanzcolor';
                    $urlArray['6']['icon'] = '';
                    break;
                case 'qiandan'://最新签单
                    $urlArray['7']['active'] = 'xuanzcolor';
                    $urlArray['7']['icon'] = '';
                    break;
                default:    // 综合实力
                    $urlArray['5']['active'] = 'class="active"';
                    $urlArray['5']['icon'] = '';
                    break;
            }
        } else {
            $urlArray['5']['active'] = 'class="active"';
            $urlArray['5']['icon'] = '';
        }
        $condition['orderby'] = 't1.comment_score desc';

        if ($isCert == '1') {
            $condition['vip'] = '1';
        }
        if ($isSale == '1') {
            $condition['sale'] = '1';
        }

        $condition['cond'] = $orderby;
        //设置城市ID
        $condition['cs'] = $cityid;

        //当点击信赖度时获取三天内的评论数最高

        foreach ($param as $key => $value) {
            if ($value != "0") {
                if ($key == "g") {
                    $condition["gm"] = $value;
                } elseif ($key == 'fu') {
                    $condition['fw'] = $value;
                } elseif ($key == "f") {
                    $condition['fg'] = $value;
                } else {
                    $condition[$key] = $value;
                }
            }
        }


        //1009 会员（40165）不在合肥分站首页和装修公司列表展示
        if ($cityid == 340100) {
            $condition['hiddenid'] = array('40165');
        }

        // 获取装修公司列表
        $result = $this->getNewCacheList($orderby, $condition, $pageIndex, $pageCount);

        //
        $countmap = [];
        $countmap['cs'] = $cityid;
        $companylogic = new CompanyLogicModel();
        $cityCompanycount = $companylogic->getCompanyCount($countmap);
        $this->assign("cityCompanycount", $cityCompanycount);

        if (!empty($result)) {
            $companyInfo["companyList"] = $this->getStar($result["list"]);
            $companyInfo["page"] = $result["page"];
            $companyInfo["count"] = $result["num"];
        }

        //$list =  $this->getCompanyList($pageIndex, $pageCount, $keyword, $cityInfo["id"], $param);

        if (empty($keyword)) {
            $area = [];
            foreach ($navbar['area']['result'] as $key => $val) {
                $area[$val['id']] = $val['name'];
            }
            $fullCity = trim($cityInfo['name'] . $area[$param['fu']]);
            $basic['head']['title'] = $fullCity.'装修公司排名_'.$fullCity.'装修公司哪家好_'.$fullCity.'十大装修公司-'.$cityInfo['name'].'齐装网';
            $basic['head']['keywords'] = $fullCity.'装修公司, '.$fullCity.'装修公司排名,'.$fullCity.'装修公司哪家好,'.$fullCity.'十大装修公司';
            $basic['head']['description'] = $cityInfo['name'].'齐装网汇集了'.$fullCity.'装修公司排名大全，帮助您了解'.$fullCity.'装修公司哪家好，免费提供'.$area[$param['fu']].'装修公司设计与报价，以及装修知识。免费拨打电话将直接转接到装修公司，无额外收费！';
        } else {
            //关键字、描述、标题
            $basic["head"]["title"] = $content . "," . $content . "怎么样" . $pageContent;
            $basic["head"]["keywords"] = $content;
            $basic["head"]["description"] = "齐装网为您提供" . $content . "的相关信息，" . $content . "怎么样，找" . $content . "就上齐装网！";
        }

        //获取后台配置的TDK
        $config = [
            'cs' => $this->cityInfo['id'], //城市id
            'area' => $area[$param['fu']],  //区域
            'model' => 2, //模块 1.首页 2.装修公司 3.装修资讯
            'category' => '', //装修资讯子频道栏目
            'location' => 2, //位置 1.pc端 2.移动端
            'page' => I('get.p'), //分页
        ];
        $basic["head"] = getCommonManageTdk($basic["head"], $config);

        if (empty($cityInfo)) {
            $info['canonical'] = $SCHEME_HOST['scheme_full'] . 'www.' . $SCHEME_HOST['domain'] . '/company/';
        } else {
            $info['canonical'] = $cityInfo['bm'] . '/company' == trim($_SERVER['REQUEST_URI'], '/') ? $SCHEME_HOST['scheme_full'] . $cityInfo['bm'] . '.' . $SCHEME_HOST['domain'] . '/company/' : '';

            if (empty($info['canonical'])) {
                $query = str_replace('/' . $bm, '', $_SERVER['REQUEST_URI']);
                $locate = strpos($query, 'p=');
                if ($locate !== false) {
                    $replace = substr($query, $locate - 1, 3 + strlen(I('get.p')));
                    $query = str_replace($replace, '', $query);
                }

                $info['canonical'] = $SCHEME_HOST['scheme_full'] . $cityInfo['bm'] . '.' . $SCHEME_HOST['domain'] . $query;
            }
        }

        //推荐公司
        $resultRe = $result['recommend'];
        $companyInfo["companyReList"] = $this->getStar($resultRe);

        //根据src获得当前一条记录
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $noresult = 0;
        if (empty($companyInfo['companyList'])) {
            $noresult = 1;
        }
        $arr_url = explode("?", $_SERVER['REQUEST_URI']);
        //获取活动/优惠券（专用券/通用券）
        $companyInfo['companyList'] = $this->getSpecialCard($companyInfo['companyList']);

        // 1299 公司活动跳转地址
        if (!empty($companyInfo['companyList'])) {
            foreach ($companyInfo['companyList'] as $k => $v) {
                if (!empty($v['activity'])) {
                    foreach ($v['activity'] as $kk => $vv) {
                        $companyInfo['companyList'][$k]['activity'][$kk]['url']
                            = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/event_view/'. $vv['id'].'.html';
                    }
                }
            }
        }

        // 642 m端装修公司筛选页及url优化        // 1.1.3.Url规范
        if(strpos($arr_url[0],'list-fu') !== false){

        }else{
            $arr_url[0] = $arr_url[0] . "list-fu0f0g0bz0";
        }

        $this->assign("url", $arr_url[0]);
        $this->assign("url_2", $arr_url[1]);
        $this->assign("orderby", $orderby);
        $this->assign("param", $param);
        $this->assign("companyInfo", $companyInfo);
        $this->assign("noresult", $noresult);
        $this->assign("navbar", $navbar);

        $info["list"] = $this->getStar($list["companyList"]);
        $info["page"] = $list["page"];
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $info);
        $this->assign("img", $weixinResult['img']);
        $this->assign("basic", $basic);
        $this->display("index_two");
    }

    //计算星星
    private function getStar($list)
    {
        $logo = OP('DEFAULT_LOGO');
        foreach ($list as $key => $value) {
            if (empty($value['logo'])) {
                $list[$key]["logo"] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . '/' . $logo;
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

            //超过10个字符以“...”做结尾
            if(mb_strlen($value['jc'])>8){
                $list[$key]["jc"] = mb_substr($value['jc'],0,8).'...';
            }
        }
        return $list;
    }

    /**
     * [getNavUrl 获取导航URL]
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
     * @return [type]        [description]
     */
    public function getNavUrl($datas, $type, $url, $present, $init = '',$bm = "")
    {
        //如果去掉自己之后的分类数后分类ID组合小于等于零,说明是初始化
        if (intval(preg_replace('/\D/s', '', $url)) == 0 && !empty($init)) {
            $url = $init;
        }
        $selected = array();

        //去掉当前的
        $reg = '/' . $type . '\d+/i';

        foreach ($datas as $key => $value) {
            $datas[$key]["link"] = preg_replace($reg, $type . $value["id"], $url);
            if ($value['id'] == $present) {
                $selected = $value;
            }
        }

        // URL 去除 list-fu0f0g0bz0
        $firsturl = str_replace("/list-fu0f0g0bz0","",$url);
        $array = array(
            'name' => '不限',
            'link' => preg_replace($reg, $type . '0', $firsturl)
        );
        if(!empty($bm)){
            $array['link'] = $this->SCHEME_HOST['scheme_full'].C("MOBILE_DONAMES")."/".$bm."/company";
        }

        array_unshift($datas, $array);
        return array('result' => $datas, 'selected' => $selected);
    }

    //新增装修公司落地页
    public function companylandpage()
    {

        //城市信息
        $cityInfo = session('m_cityInfo');

        //装修公司
        $info['company'] = S('C:Mobile:Company:companylandpage:company:' . md5($cityInfo['id']));
        if (empty($info['company'])) {
            $number = 4;
            if (empty($cityInfo['id'])) {
                $company = D("Advbanner")->getBrandList('000001', $number);
            } else {
                $company = D("Advbanner")->getBrandList($cityInfo['id'], $number);
                //数量不足四个则获取主站的
                if (count($company) < $number) {
                    //获取主站的四个
                    $commons = D("Advbanner")->getBrandList('000001', $number);
                    $company = array_merge($company, array_slice($commons, 0, $number - count($company)));
                }
            }

            $companyIds = array();
            foreach ($company as $key => $value) {
                $companyIds[] = $value['company_id'];
            }

            $caseCount = $caseImage = $commentScore = array();
            //获取各个公司案例数量
            $temp = D('Cases')->getCaseCountByCompanyIds($companyIds);
            foreach ($temp as $key => $value) {
                $caseCount[$value['uid']] = $value['number'];
            }

            //获取各个公司最新案例
            $temp = D('Cases')->getLastCaseByCompanyIds($companyIds);
            foreach ($temp as $key => $value) {
                $caseImage[$value['uid']] = $value;
            }

            //获取各个公司评论数量
            $temp = D('User')->getCommentCountByCompanyIds($companyIds);
            foreach ($temp as $key => $value) {
                $commentCount[$value['uid']] = $value['comment_count'];
            }

            foreach ($company as $key => $value) {
                $company[$key] = array(
                    'jc' => $value['jc'],
                    'img_url' => $value['img_url'],
                    'caseCount' => $caseCount[$value['company_id']],
                    'caseImage' => $caseImage[$value['company_id']],
                    'commentCount' => $commentCount[$value['company_id']]
                );
            }
            $info['company'] = $company;
            S('C:Mobile:Company:companylandpage:company:' . md5($cityInfo['id']), $info['company'], 900);
        }

        //装修案例
        $cid = empty($cityInfo['id']) ? '000001' : $cityInfo['id'];
        $info['case'] = S('C:Mobile:Company:companylandpage:case:v1:' . md5($cid));
        if (empty($info['case'])) {
            $temp = D("Advbanner")->getCaseList($cid, 5);
            if (count($temp) < 5) {
                //数量不足5个则补充全站的
                $common = D("Advbanner")->getCaseList('', 5 - count($temp));
                $temp = array_merge($temp, $common);
            }
            foreach ($temp as $key => $value) {
                $result = array(
                    'url' => $value['url'],
                    'img_url' => $value['img_url'],
                    'title' => $value['title'],
                    'jc' => $value['jc'],
                    'url_mobile' => $value['url_mobile'],
                );
                if (!empty($value['img_url_mobile'])) {
                    $result['img_url'] = $value['img_url_mobile'];
                }
                if (!empty($value['url_mobile'])) {
                    $result['url'] = $value['url_mobile'];
                } else {
                    $parse = parse_url($value['url']);
                    $result['url'] = $this->SCHEME_HOST['scheme_full'] . C("MOBILE_DONAMES") . '/' . explode('.', $parse['host'])[0] . $parse['path'];
                }
                $info['case'][] = $result;
            }
            S('C:Mobile:Company:companylandpage:case:v1:' . md5($cid), $info['case'], 900);
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->assign('showTmp', '1');
        $this->display();
    }

    //空间招标落地页
    public function spacelandpage()
    {
        $info = S('Cache:mobile:spacelandpage');
        if (!$info) {
            //户型
            $huxing = D("Huxing")->gethx(false);
            $info["huxing"] = $huxing;
            S("Cache:mobile:spacelandpage", $info, 3600);
        }
        //获取该城市第一个区，用于显示默认城市
        $cityarea['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $cityarea);
        $this->assign("zbInfo", $info);
        $this->assign("huxing", json_encode($info['huxing']));
        $this->display();
    }

    // 平台装修落地页
    public function plateformlandpage()
    {
        //根据src获得当前一条记录
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $head = [
            "title" => "好的装修公司推荐_家装公司排名_装修公司哪家好-齐装网",
            "keywords" => "装修哪家公司好,家装公司排名,好的装修公司推荐",
            "description" => "齐装网汇集了6万多靠谱的家装公司排名,让您更快捷更全面的了解装修哪家公司好,帮助业主根据自身需求推荐好的装修公司,体验贴心的一站式服务,享受靠谱的六大保障,轻松装修乐无忧,就上齐装网吧!",
        ];

        $this->assign("img", $weixinResult['img']);
        $this->assign("head", $head);
        $this->display();
    }


    //品牌落地页
    public function pinpai()
    {

        //随机生成获取设计与报价的人数并且生成缓存数据
        $randPeople = S("m:company:pinpai");
        if (empty($randPeople)) {
            $randPeople = rand(200, 1000);
        }
        if (!empty($_GET['key'])) {
            //ajax传入的数据写入缓存
            S("m:company:pinpai", $_GET['key'], 900);
            $this->ajaxReturn(array("status" => "1", "info" => "success", "data" => $_GET['key']));
        }

        //获取该城市第一个区，用于显示默认城市
        $cityarea['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $cityarea);

        //根据src获得当前一条记录
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $head = [
            "title" => "专业靠谱的互联网装修平台_找满意的装修公司就上齐装网",
            "keywords" => "齐装网,专业装修平台,找装修公司",
            "description" => "齐装网，中国知名大型装修平台，专业靠谱的互联网装修平台。入驻全国6万家装修公司与设计师,为全国500多个城市业主提供专业的装修服务:五大审核标准，为您找到满意的装修公司。六大装修服务，为您整个装修过程省钱。2018年我们追求的是优质的用户口碑，找满意的装修公司就上齐装网。",
        ];

        $this->assign("randPeople", $randPeople);
        $this->assign("img", $weixinResult['img']);
        $this->assign("head", $head);
        $this->display();
    }


    /**
     * [analyseUrl /company/list-fu320582f4g27]
     * @param  [type]  $url    [当前访问URL]
     * @param  [type]  $prefix [前缀]
     * @param  [type]  $param  [短参数]
     * @param boolean $check [是否检查非法输入]
     * @return [type]          [description]
     */
    private function analyseUrl($url, $prefix, $param, $check = true)
    {
        $realurl = rtrim(strstr($url, '?', true), '/');

        if (empty($realurl)) {
            $realurl = rtrim($url, '/');
        }
        //去掉前缀
        $count = 0;
        $result = str_ireplace($prefix, '', $realurl, $count);

        //对非法url输入过滤
        if ($check) {
            if ($count == 0) {
                $checked = true;
            } else {
                $str = str_ireplace('/', '\/', $prefix);
                //拼接正则表达式
                $pattern = '/^' . $str . '(';
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
            $pattern = '/' . $val . '\d+/i';
            $count = preg_match($pattern, $result, $match);
            if ($count > 0) {
                $k = preg_replace('/\d/s', '', $match[0]);
                $v = preg_replace('/\D/s', '', $match[0]);
                $config[$k] = $v;
            } else {
                //如果没有匹配到设置默认值为0
                $config[$val] = 0;
            }
        }

        //重组url，避免他人乱输入URL造成死链接
        if (array_sum($config) > 0) {
            $realurl = $prefix;
            foreach ($config as $key => $val) {
                $realurl = $realurl . $key . $val;
            }
        }

        $return['config'] = $config;
        $return['realurl'] = $realurl;
        return $return;
    }

    /**
     * 获取装修公司列表
     * @return [type] [description]
     */
    private function getCompanyList($pageIndex, $pageCount, $keyword, $cs, $param)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Common/User")->getUserInfoListCount(3, $keyword, $cs, $param['fu'], $param['f'], $param['g']);

        if ($count >= $pageCount * 100) {
            $count = $pageCount * 100;
        }

        if ($pageIndex > 100) {
            $pageIndex = 100;
        }

        if ($count > 0) {
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config = array("prev", "next");
            $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
            $pageTmp = $page->show2();
            $result = D("Common/User")->getUserInfoList(($page->pageIndex - 1) * $pageCount, $pageCount, 3, $keyword, $cs, $param['fu'], $param['f'], $param['g']);
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            foreach ($result as $key => $value) {
                $result[$key]["qc"] = $filter->filter_title($value["qc"]);
                $result[$key]["uv"] = ceil($value["uv"]) < 1 ? 1 : ceil($value["uv"]);
                $result[$key]["qd"] = $value["qdcount"] + $value["zzqd"];
            }
            return array("companyList" => $result, "page" => $pageTmp);
        }
        return null;
    }


    //排序URL替换
    private function replaceUrl($url, $a = '', $b = '')
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

    //ajax获取落地页信息
    public function getLandList()
    {
        if (IS_AJAX) {
            $type = I("get.type");
            $info = I('get.info');
            $param = explode('|', $info);
            $location = intval($param[0]);
            $fengge = intval($param[1]);
            $huxing = intval($param[2]);
            $color = intval($param[3]);


            $html = S('Cache:Company:jxxgt:mobile:ajax' . ":" . $location . ":" . $fengge . ":" . $huxing . ":" . $color);
            if (!$html) {
                $list = D('Meitu')->getLandPageMeiTuList($location, $fengge, $huxing, $color, 12, $type);
                if (!empty($list)) {
                    $html = '';
                    foreach ($list as $key => $val) {
                        $html .= '<li class="box' . ++$key . '"><a href="/meitu/p' . $val["id"] . '.html" target="_blank"><div class="img"><img  src="'. $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . '/' . $val["img_path"] . '-w300.jpg" ><p> ' . $val["title"] . '</p></div></a></li>';
                    }
                    S('Cache:Company:jxxgt:mobile:ajax' . ":" . $location . ":" . $fengge . ":" . $huxing . ":" . $color, $html, 900);
                    $this->ajaxReturn(array("data" => $html, "info" => "", "status" => 1));
                } else {
                    $this->ajaxReturn(array("status" => 0));
                }
            } else {
                $this->ajaxReturn(array("data" => $html, "info" => "", "status" => 1));
            }


        }


    }

    public function addNum()
    {
        if (IS_AJAX) {
            $num = S('Cache:Company:jxxgt');
            S('Cache:Company:jxxgt', $num + 1, strtotime(date("Y-m-d" . "23:59:59")) - time());
            $this->ajaxReturn(array("data" => $num + 1, "info" => "", "status" => 1));
        }
    }


    private function getList($map, $pageIndex = 1, $pageCount = 10, $keyword)
    {

        import('Library.Org.Page.LitePage');

        $result = D("Common/Company")->getList($map, ($pageIndex - 1) * $pageCount, $pageCount, $keyword);

        //获取最新
        $ids = [];
        foreach ($result['result'] as $key => $value) {
            $ids[] = $value['id'];
            //装修公司预约人数
            if (!S('Sub:Company:YuyueNum:' . $value['id'])) {
                S('Sub:Company:YuyueNum:' . $value['id'], mt_rand(100, 500), 2592000);
            }
        }
        $caseLogic = new CasesLogicModel();
        if (!empty($ids)) {
            //获取最新优惠
            $active = D('CompanyActivity')->getCompanyActiveListByIds($ids);
            //获取最新推荐的评论
            $comments = D('Comment')->getCommentByComs($ids);
            //获取案例的最新图片
            $lastCases = $caseLogic->getLastCaseImg($ids);
            foreach ($result['result'] as $key => $value) {
                $result['result'][$key]['active_id'] = $active[$value['id']]['id'];
                $result['result'][$key]['active_title'] = $active[$value['id']]['title'];
                $result['result'][$key]['new_comment'] = $comments[$value['id']]['text'];
                $result['result'][$key]['yuyue_num'] = S('Sub:Company:YuyueNum:' . $value['id']);

                //封面图片
                if ($value['img_host'] == 'qiniu' && !empty($value['img'])) {
                    $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $value['img'];
                } else {
                    //最新案例封面
                    if ($lastCases[$value['id']]['img_host'] == 'qiniu') {
                        $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $lastCases[$value['id']]["img_path"] . "-w640.jpg";
                    }elseif ($lastCases[$value['id']]["img_path"]&&$lastCases[$value['id']]["img"]) {
                        $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . $lastCases[$value['id']]["img_path"] . "m_" . $lastCases[$value['id']]["img"];
                    }else {
                        $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1')."/file/20180123/FsmhNCcgLnXA8gVgmUMbK2uwqlSd.jpg-w300.jpg";
                    }
                }
            }
        }

        $count = D("Common/Company")->getCompanyCount($map, $keyword);
        //推荐公司
        $recommend = [];
        if ($count < 10) {
            $map['comid'] = $ids;
            $recommend = D("Common/Company")->getRecommendList($map, 10 - $count);

            //获取最新
            $recommendIds = [];
            foreach ($recommend as $key => $value) {
                $recommendIds[] = $value['id'];
            }
            if(!empty($recommendIds)){
                //获取案例的最新图片
                $lastCases = $caseLogic->getLastCaseImg($recommendIds);
                foreach ($recommend as $key => $value) {
                    //封面图片
                    if ($value['img_host'] == 'qiniu' && !empty($value['img'])) {
                        $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $value['img'];
                    } else {
                        //最新案例封面
                        if ($lastCases[$value['id']]['img_host'] == 'qiniu') {
                            $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $lastCases[$value['id']]["img_path"] . "-w640.jpg";
                        } elseif ($lastCases[$value['id']]["img_path"]&&$lastCases[$value['id']]["img"]) {
                            $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . $lastCases[$value['id']]["img_path"] . "m_" . $lastCases[$value['id']]["img"];
                        }else {
                            $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1')."/file/20180123/FsmhNCcgLnXA8gVgmUMbK2uwqlSd.jpg-w300.jpg";
                        }
                    }
                    //都空，使用logo
                }
            }
        }
        import('Library.Org.Page.MobilePage');
        //自定义配置项
        $config = array("prev", "next");
        //移动端分页
        $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
        $pageTmp = $page->show2();
        //PC端分页
        /*$page = new \LitePage($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();*/
        return array("list" => $result['result'], "page" => $pageTmp, "num" => $count, 'recommend' => $recommend);
    }


    //装修效果图落地页
    public function jxxgtlandpage()
    {
        //发单人数
        //获取缓存中的数据
        $num = S('Cache:Company:jxxgt');
        if (!$num) {
            //不存在 则缓存中没有数据 当天第一次申请
            $num = rand(200, 2000);
            S('Cache:Company:jxxgt', $num, strtotime(date("Y-m-d" . "23:59:59")) - time());
        }

        //底部户型
        $zbInfo["huxing"] = D("Huxing")->gethx(false);

        //推荐
        $reFirst = D('Meitu')->getLandPageMeiTuList(0, 0, 10, 0, 6, 1);
        $category["recommand"] = array('xh', 'yj', 'bo', 'ds', 'ty', 'et', 'bj', 'pm', 'cf', 'jg', 'dh');

        //获取户型
        $fix = '4,5,6,10,11,12,14,15,7,8,9,16,17,18,19';//指定户型id
        $category['hx'] = D('Meitu')->getHuxing('', false, $fix);


        //获取风格
        $fix = '4,5,12,31,6,7,8,9,13,15,24,10,11,16,17';//指定风格
        $category['fg'] = D('Meitu')->getFengge('', false, $fix);

//        $category['fg']['fgFirst']  = D('Meitu')->getLandPageMeiTuList(0,4,0,0,12);
        //获取颜色
        $fix = '12,11,6,8,9,10,15,14,5,4,7,13';//指定风格
        $category['cl'] = D('Meitu')->getColor('', false, $fix);

        //获取局部
        $fix = '4,5,9,6,7,8,10,11,12，22,25，13,14，15,16';//指定风格
        $category['lt'] = D('Meitu')->getLocation('', false, $fix);


        //获取首页 3D
        $threeDList = S('Cache:Company:jxxgt:mobile:3d');
        if (!$threeDList) {
            $threeDList = D('Pubmeitu')->getLandPubMeiTuList(1, 5, 1);
            S('Cache:Company:jxxgt:mobile:3d', $threeDList, 3600);
        }

        //获取案例
        $huxing = array(10, 11, 12, 14, 15);
        $caseList = S('Cache:Company:jxxgt:mobile:cases');
        if (!$caseList) {
            foreach ($huxing as $val) {
                $caseList[$val] = D("Common/Cases")->getLandCaseImagesList(0, 10, 1, $val, 0, 0, $ys = "", $sm = "", $keyword = '', $city = '', $leixing = '', 5);//普通
            }
            S('Cache:Company:jxxgt:mobile:cases', $caseList, 3600);
        }


        //微信二维码
        $src = I('get.src');
        //根据渠道来源 来选择二维码
        $source = D("OrderSource")->getOne($src);
        $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }
        if (!empty($weixinResult)) {
            $this->assign("wx_img", $weixinResult['img']);
        }
        $keys["title"] = "2018精选装修效果图_家居装修效果图欣赏_3D实景装修案例效果图-齐装网装修效果图";
        $keys["keywords"] = "装修效果图,2018家居装修效果图,装修效果图案例,小户型装修效果图";
        $keys["description"] = "齐装网为您提供精选2018年家居装修效果图及全新款家居装修搭配图片欣赏,精选大小户型装修效果图，田园北欧等风格3D实景装 修效果图，还有时尚的客厅、卧室、卫生间等各类2018装修效果图。";
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $info);
        $this->assign("keys", $keys);
        $this->assign("zbInfo", $zbInfo);
        $this->assign("num", $num);
        $this->assign("reFirst", $reFirst);
        $this->assign("category", $category);
        $this->assign("threeDList", $threeDList);
        $this->assign("caseList", $caseList);
        $this->assign("source", 18032349);
        $this->display('jssdk');
    }

    function huanYiHuan()
    {
        $page_count = 5;
        $p = I('post.p') ? I('post.p') : 1;
        $skip = $page_count * ($p - 1);
        $response = [
            'status' => 0,
            'info' => '请求失败'
        ];
        $cityInfo = $this->cityInfo;
        $condition = [
            'orderby' => 'case_count desc,team_count desc,comment_score,info_time ',
        ];
        if ($cityInfo) {
            $condition['cs'] = $cityInfo['id'];
        }
        $this->assign('cityInfo', $cityInfo);
        $data = D("Common/Company")->getList($condition, $skip, $page_count);
        $company = $this->getStar($data['result']);
        $response['status'] = 1;
        $response['info'] = '成功';
        $response['result'] = $company;
        $this->ajaxReturn($response);
    }


    //装修类型落地页
    public function quanbao()
    {
        $head['title'] = '装修公司全包还是半包好_装修公司选择标准_齐装一站式装修服务解决方案平台-齐装网';
        $head['keywords'] = '装修公司全包还是半包好,装修公司选择标准,装修服务平台';
        $head['description'] = '齐装网装修服务平台为广大业主提供齐装一站式装修服务解决方案，帮助业主合理选择全包、半包和清包装修方式，严格按照标准筛选装修公司，注重高品质的装修服务体验，解决业主普遍面临的各种装修问题。上齐装平台，只需七步，装修无忧！';
        $this->assign('head', $head);

        //根据src获得当前一条记录
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $info);

        $this->assign("img", $weixinResult['img']);
        $this->display();
    }

    public function liangfang()
    {
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $info);
        $this->display();
    }

    //获取专用券/通用券/优惠活动
    private function getSpecialCard($companyinfo)
    {
        $logic = new CompanyLogicModel();
        foreach ($companyinfo as $key => $val) {
            //公司id  = $val['id'];
            $companyinfo[$key]['cardlist'] = $cardlist = D('Company')->getSpecialCardById($val['id'], 4);
            $companyinfo[$key]['cardcount'] = D('Company')->getSpecialCardCountById($val['id']);

            //优惠活动
            $companyinfo[$key]['actlist'] = $actlist = $logic->getCompanyActivityList($val['id']);

            $companyinfo[$key]['activity'] = $this->fillMergeArr($cardlist, $actlist);
        }
        return $companyinfo;
    }

    private function fillMergeArr($arr1, $arr2, $size=4){
        $mark = 1;
        $new1 = [];
        $new2 = [];
        for ($i=0; $i < 4; $i++) { 
            if($mark > 4) break;
            if(isset($arr1[$i])){
                $new1[$i] = $arr1[$i];
                $mark++;
            }
            if($mark > 4) break;
            if(isset($arr2[$i])){
                $new2[$i] = $arr2[$i];
                $mark++;
            }
        }

        return array_merge($new1, $new2);
    }

    /**
     * getCardInfoById   根据id获取优惠券信息
     */
    public function getCardInfoById()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id = I('get.cardid');
            if (empty($id)) {
                $this->ajaxReturn(['error_code' => ApiConfig::LACK_CARDID, 'error_msg' => '缺少优惠券id']);
            }
            $map = [];
            $map['a.id'] = $id;
            $info = D('Company')->getCardInfoById($map);
            if ($info) {
                $info['start'] = date('Y-m-d H:i:s', $info['start']);
                $info['end'] = date('Y-m-d H:i:s', $info['end']);
                $info['money1'] = $info['money1'] ? (int)$info['money1'] : 0;
                $info['money2'] = $info['money2'] ? (int)$info['money2'] : 0;
            }
            $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '请求成功', 'data' => $info]);
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_TYPE_ERROR, 'error_msg' => '错误的请求方式']);
        }
    }


    public function event()
    {
        $id = I('get.id');
        $logic = new CompanyLogicModel();
        $event = $logic->getEvent($id);
        $companyId = $event['cid'];
        if (empty($companyId)) {
            $this->_error();
        }
        //若前台不显示
        $companyLogic = new CompanyLogicModel();
        if (!$companyLogic->companyShow($companyId)) {
            $this->_error();
        }
        $info["user"] = $companyLogic->getUserInfo($companyId, $_SESSION["m_cityInfo"]["cid"]);
        $cityInfo = $this->cityInfo;;
        //seo 标题/描述/关键字
        $basic['head']['title'] = $event['title'] . '-' . $cityInfo["name"] . '齐装网';
        $basic['head']["keywords"] = $info["user"]['qc'] . "优惠活动," . $info["user"]['qc'] . "优惠活动";
        $basic['head']["description"] = $event['title'] . "优惠活动";

        $this->assign("basic", $basic);
        $this->assign("event", $event);
        $this->display();
    }



    private function getNewCacheList($orderby, $condition, $pageIndex = 1, $pageCount = 10){
        $cacheParam = array(
            "orderby" => $orderby,
            "condition" => $condition,
            "pageIndex" => $pageIndex,
            "pageCount" => $pageCount
        );

        $cacheMapKey = md5(serialize($cacheParam));
        $result = S("Cache:Mobile:CompanyList:" . $cacheMapKey);
        if (empty($result)) {
            unset($cacheParam["pageIndex"], $cacheParam["pageCount"]);
            $cacheParamKey = md5(serialize($cacheParam));
            $cachetime = S("Cache:Mobile:CompanyList:Keytime:" . $cacheParamKey);
            if (empty($cachetime)) {
                $cachetime = random_int(600, 900);
                S("Cache:Mobile:CompanyList:Keytime:" . $cacheParamKey, $cachetime, 86400);
            }
            $result = $this->getNewList($orderby, $condition, $pageIndex, $pageCount);
            S("Cache:Mobile:CompanyList:" . $cacheMapKey, $result, $cachetime);
        }

        return $result;
    }

    private function getNewList($orderby,$map,$pageIndex = 1,$pageCount = 10)
    {
        $companylogic = new CompanyLogicModel();
        $caseLogic = new CasesLogicModel();
        import('Library.Org.Page.Page');
        switch ($orderby) {
            case 'star'://信赖度 | 口碑
                $result = $companylogic->getStarList($map,($pageIndex-1) * $pageCount,$pageCount);
                break;
            case 'liangfang'://量房
                $result = $companylogic->getLiangfangList($map,($pageIndex-1) * $pageCount,$pageCount);
                break;
            case 'qiandan'://签单
                $result = $companylogic->getQiandanList($map,($pageIndex-1) * $pageCount,$pageCount);
                break;
            default:
                // 综合实力
                $result = $companylogic->getShiliList($map, ($pageIndex - 1) * $pageCount, $pageCount);
                break;
        }

        $comIds = array_column($result['result'], 'id');
        $designer = $companylogic->getCompanyDesigner($comIds);
        $designer = array_column($designer, null, 'company_id');

        //获取最新
        $ids = [];

        foreach ($result['result'] as $key => $value) {
            // 重新计算星星
            $startinfo = $companylogic->getCompanyScoreInfo($value["id"]);
            $result['result'][$key]["comment_score"] = $startinfo ? $startinfo : $value["comment_score"];

            $ids[] = $value['id'];
            //装修公司预约人数
            if (!S('Sub:Company:YuyueNum:' . $value['id'])) {
                S('Sub:Company:YuyueNum:' . $value['id'], mt_rand(100, 500), 2592000);
            }

            if ($value["classid"] == 6) {
                $qianIds[] = $value['id'];
            }

            // 设计师数量
            if($designer[$value['id']]['num'] ?? ''){
                $result['result'][$key]['team_count'] = $designer[$value['id']]['num'];
            }

            $result['result'][$key]['logo'] = changeImgUrl($value['logo']);

            if($value['classid'] == 3 && $value['cooperate_mode'] == 2 && floatval($value['deposit_money']) > 0){
                $result['result'][$key]['showCer'] = 1;
            }else{
                $result['result'][$key]['showCer'] = 0;
            }

            //banner
            $banners = $companylogic->getCompanyBannersList($value['id'], 4);
            $result['result'][$key]['banners'] = array_column($banners, 'img_path');

            //标签
            $tags = $companylogic->getCompanyLimitTags($value['id']);
            $result['result'][$key]['service_tags'] = array_column($tags, 'tag');
        }

        //获取案例的最新图片
        // $lastCases = $caseLogic->getLastCaseImg($ids);
        // foreach ($result['result'] as $key => $value) {
        //     //封面图片
        //     if ($value['img_host'] == 'qiniu' && !empty($value['img'])) {
        //         $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $value['img'];
        //     } else {
        //         //最新案例封面
        //         if ($lastCases[$value['id']]['img_host'] == 'qiniu') {
        //             $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $lastCases[$value['id']]["img_path"] . "-w640.jpg";
        //         } elseif ($lastCases[$value['id']]["img_path"]&&$lastCases[$value['id']]["img"]) {
        //             $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . $lastCases[$value['id']]["img_path"] . "m_" . $lastCases[$value['id']]["img"];
        //         }else {
        //             $result['result'][$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1')."/file/20180123/FsmhNCcgLnXA8gVgmUMbK2uwqlSd.jpg-w300.jpg";
        //         }
        //     }
        //     //都空，使用logo
        // }

        $count = $companylogic->getCompanyCount($map);
        // 获取推荐公司
        $recommend = [];
        if ($count < 10) {
            $map['comid'] = $ids;
            $recommend = $companylogic->getRecommendList($map, 10 - $count);

            //获取最新
            $recommendIds = [];
            foreach ($recommend as $key => $value) {
                $recommendIds[] = $value['id'];
            }
            if(!empty($recommendIds)){
                //获取案例的最新图片
                // $lastCases = $caseLogic->getLastCaseImg($recommendIds);
                foreach ($recommend as $key => $value) {
                    // //封面图片
                    // if ($value['img_host'] == 'qiniu' && !empty($value['img'])) {
                    //     $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $value['img'];
                    // } else {
                    //     //最新案例封面
                    //     if ($lastCases[$value['id']]['img_host'] == 'qiniu') {
                    //         $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . "/" . $lastCases[$value['id']]["img_path"] . "-w640.jpg";
                    //     } elseif ($lastCases[$value['id']]["img_path"]&&$lastCases[$value['id']]["img"]) {
                    //         $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . $lastCases[$value['id']]["img_path"] . "m_" . $lastCases[$value['id']]["img"];
                    //     }else {
                    //         $recommend[$key]['img'] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1')."/file/20180123/FsmhNCcgLnXA8gVgmUMbK2uwqlSd.jpg-w300.jpg";
                    //     }
                    // }
                    // //都空，使用logo

                    $recommend[$key]['logo'] = changeImgUrl($value['logo']);

                    //banner
                    $banners = $companylogic->getCompanyBannersList($value['id'], 4);
                    $recommend[$key]['banners'] = array_column($banners, 'img_path');

                    //标签
                    $tags = $companylogic->getCompanyLimitTags($value['id']);
                    $recommend[$key]['service_tags'] = array_column($tags, 'tag');

                    //优惠活动
                    $cardlist = D('Company')->getSpecialCardById($value['id'], 4);
                    $actlist = $companylogic->getCompanyActivityList($value['id']);

                    $recommend[$key]['activity'] = $this->fillMergeArr($cardlist, $actlist);
                }
            }
        }



        $maxCount = $pageCount * 100;
        if ($count > $maxCount) {
            $count = $maxCount;
        }


        //使用效果图分页 , 直接用对应参数就行
        $normal_params = array(
            "fuwu" => 0,
            "fengge" => 0,
            "guimo" => 0,
            "baozhang" => 0,
            "paixu" => 0,
            "youhui" => 0,
        );

        import('Library.Org.Page.MobilePage');
        //自定义配置项
        $config = array("prev", "next");
        //移动端分页
        $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
        $pageTmp = $page->show2();


        return array("list" => $result['result'], "page" => $pageTmp, "num" => $count, 'recommend' => $recommend);
    }


    //获取URL参数
    private function getUrlParams($url){

        if(stripos($url,'list') === false){
            $url = "fu0f0g0bz0";
        }else{
            $url = str_replace('list-','',$url);
        }

        //如果URL包含 ? 号
        if(stripos($url,'?') !== true){
            $url = explode('?',$url);
            $url = $url['0'];
        }
        $url = str_replace("/","",$url);
        if(stripos($url,'/') !== true){
            $url = explode('/',$url);
            $url = $url['0'];
        }
        $params = explode('|',strtr($url,array('fu'=>'|fu:','f'=>'|f:','g'=>'|g:','bz'=>'|bz:')));
        $sKey = [];
        foreach($params as $k => $v ){
            if(!empty($v)){
                $val = explode(':',$v);
                $sKey[$val['0']] = $val['1'];
            }
        }

        return $sKey;
    }


    // 装修地图
    public function zxdt()
    {
        $quyuLogic = new QuyuLogicModel();
        $cityList = $quyuLogic->getAllOpenCitys();      // 获取所有已开站城市

        $cs = $this->cityInfo["cid"];
        $this->assign("city_id", $cs);
        $this->assign("citylist", $cityList);
        $this->display();
    }

}