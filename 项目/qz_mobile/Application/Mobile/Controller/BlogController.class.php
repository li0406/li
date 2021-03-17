<?php
/**
 * 移动版设计师博客页
 */
namespace Mobile\Controller;
use Common\Model\CasesModel;
use Common\Model\Logic\CompanyLogicModel;
use Mobile\Common\Controller\MobileBaseController;
class BlogController extends MobileBaseController{
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
    }

    public function index(){
        $cityInfo = $this->cityInfo;
        $info = S("Cache:mobileBlog:".I("get.id"));
        if(!$info){
            //1.获取设计师的详细信息
            $designer = $this->getDesingerInfo(I("get.id"),$cityInfo["id"]);
            $info['designer'] = $designer;
            $info["title"] = $designer['name']."的博客";
            S("Cache:mobileBlog:".I("get.id"),$info,3600*24);
        }

        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        //获取设计师作品
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = intval(I("get.p"));
        }
        $cases = $this->getDesingerCaseInfo(I("get.id"),$pageIndex,$pageCount);
        $info["cases"] = $cases["cases"];
        $info["page"] = $cases["page"];
        if($pageIndex <= 1){
            $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). '/blog/' . intval(I("get.id")).'/';
        }
        $basic["head"]["title"] = "装修设计师".$info["designer"]["name"]."的博客";
        $basic["head"]["keywords"] = $info["designer"]["name"].",".$info["designer"]["name"]."博客,装修设计师室内设计师";
        $basic["head"]["description"] =mb_substr($info["designer"]["text"], 0,100,"utf-8");
        $basic["body"]["title"] = $info["designer"]["name"]."的博客";
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->display();
    }

    public function indextwo(){
        $cityInfo = $this->cityInfo;
        $page = (int)I("get.p")?:1;
        $pageCount = 10;
        $info = S("Cache:mobileBlog:".I("get.id"));
        if(!$info){
            //1.获取设计师的详细信息
            $designer = $this->getDesingerInfo(I("get.id"),$cityInfo["id"]);
            $info['designer'] = $designer;
            $info["title"] = $designer['name']."的博客";
            S("Cache:mobileBlog:".I("get.id"),$info,3600*24);
        }

        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow($info["designer"]["comid"])){
            $this->_error();
        }

        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        //职位修改
        if($info["designer"]["zw"] == '0'){
            $info["designer"]["zw"] = '';
        }

        //收费修改
        if($info["designer"]["cost"] == '0'){
            $info["designer"]["cost"] = '';
        }else{
            $info["designer"]["cost"] = str_replace('元','',$info["designer"]["cost"]);
        }


        //毕业院校修改
        if(mb_strlen($info['designer']['school']) > 25){
            $info['designer']['school'] = mb_substr($info['designer']['school'],0,25).'...';
        }

        //就职公司修改
        if(mb_strlen($info['designer']['qc']) > 25){
            $info['designer']['qc'] = mb_substr($info['designer']['qc'],0,25).'...';
        }

        //设计理念修改
        if(mb_strlen($info['designer']['linian']) > 150){
            $info['designer']['linian'] = mb_substr($info['designer']['linian'],0,150).'...';
        }

        //代表作品修改
        if(mb_strlen($info['designer']['cases']) > 150){
            $info['designer']['cases'] = mb_substr($info['designer']['cases'],0,150).'...';
        }

        //个人介绍
        $testlength =  mb_strlen($info['designer']['text']);
        if($testlength >150){
            $info['designer']['text1'] = mb_substr($info['designer']['text'],0,150);
            $info['designer']['text2'] = mb_substr($info['designer']['text'],150,$testlength - 150);
        }else{
            $info['designer']['text1']  = $info['designer']['text'];
            $info['designer']['text2'] = '';
        }

        $extra  =I("get.extra");
        //更新设计师人气
        D("User")->editUvAndPv(I("get.id"),"pv");

        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). '/blog/' . intval(I("get.id")).'/';
        $basic["head"]["title"] = "装修设计师".$info["designer"]["name"]."的博客";
        $basic["head"]["keywords"] = $info["designer"]["name"].",".$info["designer"]["name"]."博客,装修设计师室内设计师";
        $basic["head"]["description"] =mb_substr($info["designer"]["text"], 0,100,"utf-8");
        $basic["body"]["title"] = $info["designer"]["name"]."的博客";

        //获取作品
        $cases = $this->getDesingerCaseInfov2(I("get.id"),$info["designer"]["comid"],$page,$pageCount);

        $info['canonical'] = $canonical = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$this->SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);

        //获取设计师作品
        $model = new CasesModel();
        $count = $model->getDesingerCaseInfoCountv2(I("get.id"),$info["designer"]["comid"]);
        $info["count"] = $count;

        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("userInfo",$info);
        $this->assign("info",$info);
        $this->assign("cases",$cases);
        $this->display();
    }

    public function indextwonew(){
        $cityInfo = $this->cityInfo;
        $page = (int)I("get.p")?:1;
        $pageCount = 10;
        $info = [];//S("Cache:mobileBlog:".I("get.id"));
        if(!$info){
            //1.获取设计师的详细信息
            $designer = $this->getNewDesingerInfo(I("get.id"));
            $info['designer'] = $designer;
            $info["title"] = $designer['name']."的博客";
            S("Cache:mobileBlog:".I("get.id"),$info,3600*24);
        }

        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        //职位修改
        if($info["designer"]["zw"] == '0'){
            $info["designer"]["zw"] = '';
        }

        //收费修改
        if($info["designer"]["cost"] == '0'){
            $info["designer"]["cost"] = '';
        }else{
            $info["designer"]["cost"] = str_replace('元','',$info["designer"]["cost"]);
        }


        //毕业院校修改
        if(mb_strlen($info['designer']['school']) > 25){
            $info['designer']['school'] = mb_substr($info['designer']['school'],0,25).'...';
        }

        //就职公司修改
        if(mb_strlen($info['designer']['qc']) > 25){
            $info['designer']['qc'] = mb_substr($info['designer']['qc'],0,25).'...';
        }

        //设计理念修改
        if(mb_strlen($info['designer']['linian']) > 150){
            $info['designer']['linian'] = mb_substr($info['designer']['linian'],0,150).'...';
        }

        //代表作品修改
        if(mb_strlen($info['designer']['cases']) > 150){
            $info['designer']['cases'] = mb_substr($info['designer']['cases'],0,150).'...';
        }

        //个人介绍
        $testlength =  mb_strlen($info['designer']['text']);
        if($testlength >150){
            $info['designer']['text1'] = mb_substr($info['designer']['text'],0,150);
            $info['designer']['text2'] = mb_substr($info['designer']['text'],150,$testlength - 150);
        }else{
            $info['designer']['text1']  = $info['designer']['text'];
            $info['designer']['text2'] = '';
        }

        //更新设计师人气
        D("User")->editDesignerPv(I("get.id"),$info["designer"]["comid"]);

        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). '/blog_new/' . intval(I("get.id")).'/';
        $basic["head"]["title"] = "装修设计师".$info["designer"]["name"]."的博客";
        $basic["head"]["keywords"] = $info["designer"]["name"].",".$info["designer"]["name"]."博客,装修设计师室内设计师";
        $basic["head"]["description"] =mb_substr($info["designer"]["text"], 0,100,"utf-8");
        $basic["body"]["title"] = $info["designer"]["name"]."的博客";

        //获取作品
        $cases = $this->getDesingerCaseInfov2(I("get.id"),$info["designer"]["company_id"],$page,$pageCount);

        $info['canonical'] = $canonical = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$this->SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);

        //获取设计师作品
        $info["count"] = $cases['count'];

        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("userInfo",$info);
        $this->assign("info",$info);
        $this->assign("cases",$cases);
        $this->display('indextwo');
    }

    public function getBlogById(){
        //获取设计师作品
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.page") !== ""){
            $pageIndex = intval(I("get.page"));
        }
        $cityInfo = $this->cityInfo;
        $info = S("Cache:mobileBlog:".I("get.id"));
        if(!$info){
            //1.获取设计师的详细信息
            $designer = $this->getDesingerInfo(I("get.id"),$cityInfo["id"]);
            $info['designer'] = $designer;
            $info["title"] = $designer['name']."的博客";
            S("Cache:mobileBlog:".I("get.id"),$info,3600*24);
        }

        $cases = $this->getDesingerCaseInfov2(I("get.id"),$info["designer"]["comid"],$pageIndex,$pageCount);
        $data["data"] = $cases["cases"];
        $data["page"] = $cases["page"];
        $this->ajaxReturn(array("info"=>"获取成功","error_code"=>0,"data"=>$data));
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
        $cityInfo = $this->cityInfo;
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
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config = array("prev", "next");
            //移动端分页
            $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
            $pageTmp = $page->show2();
            $cases = $model->getDesingerCaseInfov2($id,$comid,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($cases as $key => $val){
                if(mb_strlen($val['title']) > 10){
                    $cases[$key]['title1'] = mb_substr($val['title'],0,10);
                }else{
                    $cases[$key]['title1'] = $val['title'];
                }
                if($val['img_host'] == 'qiniu' && !empty($val['img_path'])){
                    $cases[$key]["img_path"] = $this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN').'/'.$val['img_path'];
                }elseif(!empty($val['img_path'])){
                    $cases[$key]["img_path"] = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$val['img_path'].'s_'.$val['img'];
                }
                $cases[$key]['url'] =   $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$cityInfo['bm'].'/caseinfo/'.$val['caseid'].'.shtml';
                $cases[$key]['title'] = mbstr($val['title'],0,10);
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

    /**
     * 获取设计师信息
     * @param  [type] $id [设计师编号]
     * @return [type]     [description]
     */
    private function getNewDesingerInfo($id){
       $info = D("Common/User")->getCompanyEmployee($id);
       $user = D("Common/User")->getNewDesingerInfo($info['id'],$info['company_id']);
       return $user;
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
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Cases")->getDesingerCaseInfoCount($id);
        if($count> 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $cases = D("Cases")->getDesingerCaseInfo($id,($page->pageIndex-1)*$pageCount,$pageCount);
            return array("cases"=>$cases,"page"=>$pageTmp);
        }
        return null;
    }
}