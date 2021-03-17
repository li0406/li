<?php
namespace Sub\Controller;
use Common\Model\CasesModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\UserCompanyEmployeeLogicModel;
use Sub\Common\Controller\SubBaseController;
use Common\Enums\CompanyCodeEnum;

class BlogController extends SubBaseController{
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'].".".C("QZ_YUMING").$uri."/");
            }
        }
    }

    public function index(){
        $cityInfo = $this->cityInfo;
        if(I("get.id") !== ""){
            $userInfo = S('Cache:SubBlog:'.$cityInfo["bm"]. ":" .intval(I("get.id") ) );
            if(!$userInfo){
                //获取设计师信息
                $designer = $this->getDesingerInfo(I("get.id"),$cityInfo["id"]);
                $userInfo["designer"] = $designer;

                S('Cache:SubBlog:'.$cityInfo["bm"]. ":" .intval(I("get.id")),$userInfo,900);
            }

            $companyLogic  = new CompanyLogicModel();

            // 如果公司是2.0的则不显示
            if ($companyLogic->isNewVip($userInfo["designer"]["comid"]) > 0) {
                $this->_error();
            }

            //若前台不显示
            if(!$companyLogic->companyShow($userInfo["designer"]["comid"])){
                $this->_error();
            }

            if(empty($userInfo["designer"]["name"])){
                $this->_error();
                die();
            }

            //头像修改
            if($userInfo["designer"]["logo"] == ""){
                $userInfo["designer"]["logo"] = changeImgUrl(CompanyCodeEnum::EMPLOYEE_DEFAULT_LOGO);
            } else {
                $userInfo["designer"]["logo"] = changeImgUrl($userInfo["designer"]["logo"]);
            }
            
            //职位修改
            if($userInfo["designer"]["zw"] == '0'){
                $userInfo["designer"]["zw"] = '';
            }

            //收费修改
            if($userInfo["designer"]["cost"] == '0'){
                $userInfo["designer"]["cost"] = '';
            }else{
                $userInfo["designer"]["cost"] = str_replace('元','',$userInfo["designer"]["cost"]);
            }


            //毕业院校修改
            if(mb_strlen($userInfo['designer']['school']) > 25){
                $userInfo['designer']['school'] = mb_substr($userInfo['designer']['school'],0,25).'...';
            }

            //就职公司修改
            if(mb_strlen($userInfo['designer']['qc']) > 25){
                $userInfo['designer']['qc'] = mb_substr($userInfo['designer']['qc'],0,25).'...';
            }

            //设计理念修改
            if(mb_strlen($userInfo['designer']['linian']) > 150){
                $userInfo['designer']['linian'] = mb_substr($userInfo['designer']['linian'],0,150).'...';
            }

            //代表作品修改
            if(mb_strlen($userInfo['designer']['cases']) > 150){
                $userInfo['designer']['cases'] = mb_substr($userInfo['designer']['cases'],0,150).'...';
            }



            //个人介绍
            $testlength =  mb_strlen($userInfo['designer']['text']);
            if($testlength >150){
                $userInfo['designer']['text1'] = mb_substr($userInfo['designer']['text'],0,150);
                $userInfo['designer']['text2'] = mb_substr($userInfo['designer']['text'],150,$testlength - 150);
            }else{
                $userInfo['designer']['text1']  = $userInfo['designer']['text'];
                $userInfo['designer']['text2'] = '';
            }

            $extra  =I("get.extra");
            //更新设计师人气
            D("User")->editUvAndPv(I("get.id"),"pv");

            // 因为新签返设计师的原因des表人气也需要同步增加
            $userCompanyEmployeeLogic = new UserCompanyEmployeeLogicModel();
            $userCompanyEmployeeLogic->SetIncDesignerPv(I("get.id"));

            $pageIndex = 1;
            $pageCount = 8;     //数据超过8条展示分页
            $articlePageIndex = 1;
            $articlePageCount = 5;

            if($extra == 1){
                if(I("get.p")!==""){
                    $pageIndex = I("get.p");
                }
            }elseif($extra == 2){
                if(I("get.p")!==""){
                    $articlePageIndex = I("get.p");
                }
            }
            $this->assign("extra",$extra);
            //获取设计师作品
            $cases = $this->getDesingerCaseInfov2(I("get.id"),$userInfo["designer"]["comid"],$pageIndex,$pageCount);
            $userInfo["cases"] = $cases["cases"] ? $cases["cases"] : [];
            $userInfo["page"] = $cases["page"];
            $userInfo["count"] = $cases["count"];
            if('/blog/'.I("get.id").'/' == $_SERVER['REQUEST_URI']){
                $info['head']['canonical'] = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.C('QZ_YUMING').'/blog/'.I("get.id").'/';
            }

            //获取装修公司信息
            $companylogic = new CompanyLogicModel();
            $companyinfo = $companylogic->getCompanyInfoByComId($userInfo["designer"]["comid"]);
            $this->assign("companyinfo",$companyinfo ? $companyinfo : []);

            //获取设计师文章作品
            $article = $this->getArticlesList($articlePageIndex,$articlePageCount,I("get.id"),$cityInfo["id"]);
            $userInfo["article"] = $article["article"];
            $userInfo["articlePage"] = $article["page"];

            //获取其他设计师
            $otherdesignerlist = S('Cache:SubBlog:OtherDesigner' .":" .intval(I("get.id")));
            if(!$otherdesignerlist){
                $otherdesignerlist = $companylogic->getOtherDesignerByCompanyAndUserId($userInfo["designer"]["userid"],$userInfo["designer"]["comid"]);
                S('Cache:SubBlog:OtherDesigner'. ":" .intval(I("get.id")),$otherdesignerlist,900);
            }
            $this->assign("otherdesignerlist",$otherdesignerlist);

            //剩余名额
//            $lostcount = rand(20,1000);
//            $this->assign('lostcount',$lostcount);


             //seo 标题/描述/关键字
            $keys["title"] = $userInfo["designer"]["name"]."装修设计师_".$userInfo["designer"]["name"]."设计作品-".$cityInfo['name']."齐装网";
            $keys["keywords"] = $userInfo["designer"]["name"].",".$companyinfo['jc']."设计师,室内设计师".$userInfo["designer"]["name"];
            $keys["description"] =$cityInfo['name']."齐装网为您提供".$companyinfo['jc'].$userInfo['designer']['zw'].$userInfo['designer']['name']."的个人介绍,包括从业经验、设计作品、设计收费标准等信息,在".$cityInfo['name']."找室内设计师就上齐装网。";
            $this->assign("keys",$keys);
             //安全验证码
            $safe = getSafeCode();
            $this->assign("safecode",$safe["safecode"]);
            $this->assign("safekey",$safe["safekey"]);
            $this->assign("ssid",$safe["ssid"]);
            //菜单导航
            $this->assign("tabIndex",2);
            $this->assign("userInfo",$userInfo);
            $this->assign("info",$info);
            $this->assign("cityInfo",$cityInfo);
            $this->display();
            die();
        }
        $this->_error();
    }


    // 新签返装修公司的设计师详情页
    public function designerEmployeeInfo(){
        $cityInfo = $this->cityInfo;
        $userCompanyEmployeeLogic = new UserCompanyEmployeeLogicModel();
        if(I("get.id") !== ""){
            $userInfo = S('Cache:QianfanSubBlog:'.$cityInfo["bm"]. ":" .intval(I("get.id") ) );
            if(!$userInfo){
                //获取设计师信息
                $designer = $this->getQianfanSignerInfo(I("get.id"),$cityInfo["id"]);
                $userInfo["designer"] = $designer;

                S('Cache:QianfanSubBlog:'.$cityInfo["bm"]. ":" .intval(I("get.id")),$userInfo,900);
            }

            //若前台不显示
            $companyLogic  = new CompanyLogicModel();
            if(!$companyLogic->companyShow($userInfo["designer"]["comid"])){
                $this->_error();
            }

            if(empty($userInfo["designer"]["name"])){
                $this->_error();
                die();
            }

            //头像修改
            if($userInfo["designer"]["logo"] == ""){
                $userInfo["designer"]["logo"] = changeImgUrl(CompanyCodeEnum::EMPLOYEE_DEFAULT_LOGO);
            } else {
                $userInfo["designer"]["logo"] = changeImgUrl($userInfo["designer"]["logo"]);
            }

            //职位修改
            if($userInfo["designer"]["zw"] == '0'){
                $userInfo["designer"]["zw"] = '';
            }

            //收费修改
            if($userInfo["designer"]["cost"] == '0'){
                $userInfo["designer"]["cost"] = '';
            }else{
                $userInfo["designer"]["cost"] = str_replace('元','',$userInfo["designer"]["cost"]);
            }


            //毕业院校修改
            if(mb_strlen($userInfo['designer']['school']) > 25){
                $userInfo['designer']['school'] = mb_substr($userInfo['designer']['school'],0,25).'...';
            }

            //就职公司修改
            if(mb_strlen($userInfo['designer']['qc']) > 25){
                $userInfo['designer']['qc'] = mb_substr($userInfo['designer']['qc'],0,25).'...';
            }

            //设计理念修改
            if(mb_strlen($userInfo['designer']['linian']) > 150){
                $userInfo['designer']['linian'] = mb_substr($userInfo['designer']['linian'],0,150).'...';
            }

            //代表作品修改
            if(mb_strlen($userInfo['designer']['cases']) > 150){
                $userInfo['designer']['cases'] = mb_substr($userInfo['designer']['cases'],0,150).'...';
            }



            //个人介绍
            $testlength =  mb_strlen($userInfo['designer']['text']);
            if($testlength >150){
                $userInfo['designer']['text1'] = mb_substr($userInfo['designer']['text'],0,150);
                $userInfo['designer']['text2'] = mb_substr($userInfo['designer']['text'],150,$testlength - 150);
            }else{
                $userInfo['designer']['text1']  = $userInfo['designer']['text'];
                $userInfo['designer']['text2'] = '';
            }

            $extra  =I("get.extra");
            //更新设计师人气
            $userCompanyEmployeeLogic->SetIncDesignerPv(I("get.id"));


            $pageIndex = 1;
            $pageCount = 8;     //数据超过8条展示分页
            $articlePageIndex = 1;
            $articlePageCount = 5;

            if($extra == 1){
                if(I("get.p")!==""){
                    $pageIndex = I("get.p");
                }
            }elseif($extra == 2){
                if(I("get.p")!==""){
                    $articlePageIndex = I("get.p");
                }
            }
            $this->assign("extra",$extra);
            //获取设计师作品
            $cases = $this->getDesingerCaseInfov2(I("get.id"),$userInfo["designer"]["comid"],$pageIndex,$pageCount);
            $userInfo["cases"] = $cases["cases"] ? $cases["cases"] : [];
            $userInfo["page"] = $cases["page"];
            $userInfo["count"] = $cases["count"];
            if('/blog/'.I("get.id").'/' == $_SERVER['REQUEST_URI']){
                $info['head']['canonical'] = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.C('QZ_YUMING').'/blog/'.I("get.id").'/';
            }

            //获取装修公司信息
            $companylogic = new CompanyLogicModel();
            $companyinfo = $companylogic->getCompanyInfoByComId($userInfo["designer"]["comid"]);
            $this->assign("companyinfo",$companyinfo ? $companyinfo : []);

            //获取设计师文章作品 （页面没有用到文章相关，代码删除）

            //获取其他设计师
            $otherdesignerlist = S('Cache:QianfanSubBlog:OtherDesigner' .":" .intval(I("get.id")));
            if(!$otherdesignerlist){
                $otherdesignerlist = $userCompanyEmployeeLogic->otherDesignerByQianfanCompanyAndUserId($userInfo["designer"]["userid"],$userInfo["designer"]["comid"]);
                S('Cache:QianfanSubBlog:OtherDesigner'. ":" .intval(I("get.id")),$otherdesignerlist,900);
            }
            $this->assign("otherdesignerlist",$otherdesignerlist);

            //剩余名额
//            $lostcount = rand(20,1000);
//            $this->assign('lostcount',$lostcount);


            //seo 标题/描述/关键字
            $keys["title"] = $userInfo["designer"]["name"]."装修设计师_".$userInfo["designer"]["name"]."设计作品-".$cityInfo['name']."齐装网";
            $keys["keywords"] = $userInfo["designer"]["name"].",".$companyinfo['jc']."设计师,室内设计师".$userInfo["designer"]["name"];
            $keys["description"] =$cityInfo['name']."齐装网为您提供".$companyinfo['jc'].$userInfo['designer']['zw'].$userInfo['designer']['name']."的个人介绍,包括从业经验、设计作品、设计收费标准等信息,在".$cityInfo['name']."找室内设计师就上齐装网。";
            $this->assign("keys",$keys);
            //安全验证码
            $safe = getSafeCode();
            $this->assign("safecode",$safe["safecode"]);
            $this->assign("safekey",$safe["safekey"]);
            $this->assign("ssid",$safe["ssid"]);
            //菜单导航
            $this->assign("tabIndex",2);
            $this->assign("userInfo",$userInfo);
            $this->assign("info",$info);
            $this->assign("cityInfo",$cityInfo);
            $this->display();
            return ;
            // die();
        }
        $this->_error();
    }

    /**
     * 获取设计师案例列表
     * @param  [type] $id        [设计师编号]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $tab       [额外参数]
     * @return [type]            [description]
     */
    private function getDesingerCaseInfo($id,$pageIndex,$pageCount)
    {
        $model = new CasesModel();
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = $model->getDesingerCaseInfoCount($id);

        if($count> 0){
            $extra = array(
                    "extra"=>1
                           );
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config,$extra);
            $pageTmp =  $page->show();
            $cases = $model->getDesingerCaseInfo($id,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($cases as $key => $val){
                if(mb_strlen($val['title']) > 10){
                    $cases[$key]['title1'] = mb_substr($val['title'],0,10);
                }else{
                    $cases[$key]['title1'] = $val['title'];
                }
            }
            return array("cases"=>$cases,"page"=>$pageTmp,"count"=>$count);
        }
        return null;
    }
    /**
     * 获取设计师案例列表
     * @param  [type] $id        [设计师编号]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $tab       [额外参数]
     * @return [type]            [description]
     */
    private function getDesingerCaseInfov2($id,$comid,$pageIndex,$pageCount)
    {
        $model = new CasesModel();
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = $model->getDesingerCaseInfoCountv2($id,$comid);

        if($count> 0){
            $extra = array(
                "extra"=>1
            );
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config,$extra);
            $pageTmp =  $page->show();
            $cases = $model->getDesingerCaseInfov2($id,$comid,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($cases as $key => $val){
                if(mb_strlen($val['title']) > 10){
                    $cases[$key]['title1'] = mb_substr($val['title'],0,10);
                }else{
                    $cases[$key]['title1'] = $val['title'];
                }
            }
            return array("cases"=>$cases,"page"=>$pageTmp,"count"=>$count);
        }
        return null;
    }

    /**
     * 获取设计师信息
     * @param  [type] $id [设计师编号]
     * @param  [type] $cid [公司编号]
     * @return [type]     [description]
     */
    private function getDesingerInfo($id,$cs){
       $user = D("Common/User")->getDesingerInfo($id,$cs);
       return $user;
    }

    private function getQianfanSignerInfo($id, $cs)
    {
        $logic = new UserCompanyEmployeeLogicModel();
        $user = $logic->getQianfanSignerInfo($id, $cs);
        return $user;

    }

    /**
     * 获取设计师文章列表
     * @return [type] [description]
     */
    private function getArticlesList($pageIndex,$pageCount,$id,$cs)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Article")->getArticleListCount($id,$cs);
        if($count > 0){
            $extra = array(
                    "extra"=>2
                           );
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config,$extra);
            $pageTmp =  $page->show();
            $article = D("Article")->getArticleList($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount);
            return array("article"=>$article,"page"=>$pageTmp,"count"=>$count);
        }
    }
}