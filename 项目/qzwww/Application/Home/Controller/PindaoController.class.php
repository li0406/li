<?php
/**
 * Created by PhpStorm.
 * User: qzjsb
 * Date: 2019/4/29
 * Time: 10:27
 */

namespace Home\Controller;


use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\WwwArticleTagsLogicModel;
use Common\Model\WwwArticleTagsModel;
use Home\Common\Controller\HomeBaseController;

class PindaoController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot', 1);
        }

        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW").$uri."/");
            }
        }

        if (!empty($this->cityInfo["bm"])) {
            if (!$robotIsTrue) {
                $this->assign("headerTmp", 1);
            }
        }
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
        }
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
        $this->assign("header_search", 3);
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'gonglue/search');
        $this->assign('serch_type', '装修攻略');
        $this->assign('holdercontent', '了解相关的装修资讯知识');
    }

    public function index(){
        if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $pageIndex = 1;
        $pageCount = 58;     //加上2条hot  一页60条

        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }
        //TDK
        $basic['head']['title'] = '在线知识问答平台 - 齐装网装修问答';
        $basic['head']['keywords'] = '知识问答,问答平台,在线问答,装修问答';
        $basic['head']['description'] = '齐装网在线知识问答平台提供全面的生活装修知识问答、在线问答、问答知识等网友提问及回答信息，解决装修问题、生活问题就来齐装网在线知识问答平台。';
        $basic['body']['title'] = '问答知识频道';

        $logic = new ThematicWordsLogicModel();
        $result = $logic->getList(3,$pageIndex,$pageCount);
        $hot = $logic->getHotList(3,20);

        $uri = explode('?',__SELF__);
        $this->assign('uri',$uri[0]);
        $this->assign('basic',$basic);
        $this->assign("tabIndex",4);
        $this->assign("hot",$hot);
        $this->assign("page",$result["page"]);
        $this->assign("list",$result["list"]);
        $this->display('index');
    }

    /**
     * 获取关键词列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSpecialkeyword($pageIndex,$pageCount,$keyword_module,$is_hot)
    {
        $count = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordCount($keyword_module,$is_hot);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next","begin","end");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $show    = $page->pindaoshow();//,$p->firstRow,$p->listRows
            $list = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordList($keyword_module, $is_hot,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $value) {
                if($value["keyword_module"] == 3){
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") . "/wenda/zs/".$value["href"]."/";
                }else{
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") . "/baike/zs/".$value["href"]."/";
                }
            }
        }
        return array("list"=>$list,"page"=>$show);
    }

    /**
     * 获取底部标签组,标签
     */
    public function getTag($style){
        $logic = new WwwArticleTagsLogicModel();
        $ret = $logic->getTags($style);
        return $ret;
    }
}