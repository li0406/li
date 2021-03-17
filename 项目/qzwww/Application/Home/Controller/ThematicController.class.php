<?php

namespace Home\Controller;

use Common\Model\FriendlinkModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Home\Common\Controller\HomeBaseController;

class ThematicController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW").$uri."/");
            }
        }

        $t = T("Home@Index:header");
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    public function index()
    {
        $pageCount = 98;
        //获取标签列表
        $logic = new ThematicWordsLogicModel();
        $result = $logic->getList($pageCount);
        $hot = $logic->getHotList(20);

        if ($result["pageIndex"] > 1) {
           $page = "第".$result["pageIndex"]."页 - ";
        }else{
            //友情链接
            $friendLink = S('Cache:Home:Index:zhishi');
            if(!$friendLink){
                $fdlinkmodel = new FriendlinkModel();
                $friendLink["link"] = $fdlinkmodel->getFriendLinkList("000001",1,"zhishi");
                S('Cache:Home:Index:zhishi',$friendLink,900);
            }

            $this->assign('friendLink',$friendLink);
        }

        $basic['head']['title'] = '装修知识大全 - 房屋装修经验心得 - 房子装修攻略 - 新房装修效果图 - '.$page.'齐装网';
        $basic['head']['keywords'] = "装修知识,装修经验,装修攻略,装修心得,装修效果图";
        $basic['head']['description'] = '齐装网装修知识栏目为广大业主提供详细的装修经验，装修流程，装修设计效果图及装修注意事项等内容，并且免费提供装修报价清单和装修设计方案。';

        $this->assign("order_source", 20091522);

        $this->assign('hot',$hot);
        $this->assign('basic',$basic);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    public function thematicList()
    {
        $id = I("get.id");
        //获取内容列表
        $logic = new ThematicWordsLogicModel();
        $info = S("Cache:Them:".$id);
        if (!$info) {
            $info = $logic->getThematicInfoById($id);
            if (count($info) == 0 || $info["type"] != 1) {
                $this->_error();
                die();
            }
            S("Cache:Them:".$id,$info,900);
        }

        //获取列表
        $pageCount = 12;
        $result = $logic->getThematicSearch($info["name"],$pageCount);
        if ($result["pageIndex"] > 1) {
            $page = " - 第".$result["pageIndex"]."页";
        }

        //tdk
        $basic['head']['title'] = (empty($info["title"])?$info["name"]:$info["title"]).$page.' - 齐装网资讯';
        $basic['head']['keywords'] = empty($info["keywords"])?$info["name"]:$info["keywords"];
        $basic['head']['description'] = empty($info["description"])?'齐装网'.$info["name"].'专区,提供'.$info["name"].'相关的资讯，供广大业主装修时参考,更多的'.$info["name"].'尽在齐装网栏目。':$info["description"];

        //相关知识
        $related = $logic->getContentLabels($info["id"], 6, 20, 1);
        if (count($related) > 0) {
            $this->assign("related",$related);
        }

        //最新知识
        $new = $logic->getNewLabels($info["id"],20);
        if (count($new) > 0) {
            $this->assign("new",$new);
        }

        // 1353百度官方号需求
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($info['description']), 0, 100, 'utf-8');
        $baidu['title'] = $info["title"];
        if(empty($info['uptime'])){
            $optimee =date("Y-m-d",$info['createtime']);
            $optimes =date("H:i:s",$info['createtime']);
        }else{
            $optimee =date("Y-m-d",$info['uptime']);
            $optimes =date("H:i:s",$info['uptime']);
        }
        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = date("Y-m-d",$info["createtime"])."T".date("H:i:s",$info["createtime"]);
        $this->assign("baidu",$baidu);

        $this->assign("order_source", 20091540);

        $this->assign("id",$id);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("thematic",$info["name"]);
        $this->assign('basic',$basic);
        $this->display('thematiclist');
    }

    public function thematictulist(){
        $id = I("get.id");

        // 跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:".$this->SCHEME_HOST["scheme_full"] . C("MOBILE_DONAMES") . $_SERVER["REQUEST_URI"]);
            exit();
        }

        //获取内容列表
        $thematicWordsLogic = new ThematicWordsLogicModel();
        $info = S("Cache:Them:Meitu:".$id);
        if (!$info) {
            $info = $thematicWordsLogic->getThematicInfoById($id);
            if (count($info) == 0 || $info["type"] != 2) {
                $this->_error();
                die();
            }
            S("Cache:Them:Meitu:".$id, $info, 900);
        }

        //获取列表
        $pageCount = 40;
        $result = $thematicWordsLogic->getThematicTuSearch($info["name"], $pageCount);
        // TDK (576 图片标签TDK调用)
        $basic['head']['title'] = $info["title"]. ($result["pageIndex"] > 1 ? "-【第".$result["pageIndex"]."页】" : "") . "-齐装网装修效果图";
        $basic['head']['keywords'] = $info["keywords"];
        $basic['head']['description'] = $info["description"];
        $basic['head']['canonical'] = $_SERVER['HTTP_HOST'] . '/tu/'.$id.'/';


        // 模板替换内容
        // $replace = array(
        //     "{标签名}" => $info["name"],
        //     "{page}" => $result["pageIndex"] > 1 ? "【第".$result["pageIndex"]."页】 - " : ""
        // );

        //tdk
        // $basic['head']['title'] = strtr("{标签名} - {page}齐装网装修效果图", $replace);
        // $basic['head']['keywords'] = $info["name"];
        // $basic['head']['description'] = strtr("齐装网{标签名}专区,收集整理精美的{标签名}图片大全,供广大业主装修时参考,更多的{标签名}尽在齐装网装修效果图栏目。", $replace);

        // 相关知识
        $relatedList = S('Cache:WWW:Thematic:related:' . $info["id"]);
        if (empty($relatedList)) {
            $relatedList = $thematicWordsLogic->getContentLabels($info["id"], 6, 10, 2);
            S('Cache:WWW:Thematic:related:' . $info["id"], $relatedList, 900);
        }

        // 家装栏目
        $categoryList = S('Cache:getList-2:categorylist');
        if(!$categoryList){
            $tuCategoryLogic = new TuCategoryLogicModel();
            $categoryList = $tuCategoryLogic->getMeituLv2CategoryByType(2);
            S('Cache:getList-2:categorylist', $categoryList, 60 * 60 * 12);
        }

        $friendLink = S("Cache:WWW:Tu:thematicTuList:FriendLink:" . $id);
        if (empty($friendLink)) {
            $scheme = getSchemeAndHost();
            // 装修效果图
            $nearbyWords = $thematicWordsLogic->getNearbyWords($id, 2, 15);

            $friendLink["tags"] = [];
            foreach ($nearbyWords as $key => $item) {
                $friendLink["tags"][] = array(
                    "link_name" => $item["name"],
                    "link_url" => sprintf("%s://%s/tu/%d/", $scheme["scheme"], C("QZ_YUMINGWWW"), $item["id"])
                );
            }

            S("Cache:WWW:Tu:thematicTuList:FriendLink:" . $id, $friendLink, 900);
        }

        // 1353百度官方号需求
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($info['description']), 0, 100, 'utf-8');
        $baidu['title'] = $info["title"];
        if(empty($info['uptime'])){
            $optimee =date("Y-m-d",$info['createtime']);
            $optimes =date("H:i:s",$info['createtime']);
        }else{
            $optimee =date("Y-m-d",$info['uptime']);
            $optimes =date("H:i:s",$info['uptime']);
        }
        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = date("Y-m-d",$info["createtime"])."T".date("H:i:s",$info["createtime"]);
        $this->assign("baidu",$baidu);

        $this->assign("id",$id);
        $this->assign("friendLink", $friendLink);
        $this->assign("relatedList", $relatedList);
        $this->assign("categoryList", $categoryList);
        $this->assign("list", $result["list"]);
        $this->assign("page", $result["page"]);
        $this->assign("thematic",$info["name"]);
        $this->assign('basic',$basic);
        $this->display();
    }
}
