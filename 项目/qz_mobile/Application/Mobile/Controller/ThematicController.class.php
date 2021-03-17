<?php

namespace Mobile\Controller;
use Common\Model\FriendlinkModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class ThematicController extends MobileBaseController
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
                header("Location: ". $this->SCHEME_HOST['scheme_full'] . C("MOBILE_DONAMES").$uri."/");
            }
        }
    }

    public function index()
    {
        $pageCount = 98;
        //获取标签列表
        $logic = new ThematicWordsLogicModel();
        $result = $logic->getList($pageCount);

        if ($result["pageIndex"] > 1) {
            $page = "第".$result["pageIndex"]."页 - ";
        }
        
        $basic['head']['title'] = $page.'装修知识大全 - 房屋装修经验心得 - 房子装修攻略 - 新房装修效果图 - 齐装网';
        $basic['head']['keywords'] = "装修知识,装修经验,装修攻略,装修心得,装修效果图";
        $basic['head']['description'] = '齐装网装修知识栏目为广大业主提供详细的装修经验，装修流程，装修设计效果图及装修注意事项等内容，并且免费提供装修报价清单和装修设计方案。';

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
        $info = S("Cache:Mobile:Them:".$id);
        if (!$info) {
            $info = $logic->getThematicInfoById($id);
            if (count($info) == 0 || $info["type"] != 1) {
                $this->_error();
                die();
            }
            S("Cache:Mobile:Them:".$id,$info,900);
        }
        $pageCount = 12;

        //获取列表
        $result = $logic->getThematicSearch($info["name"],$pageCount);

        //tdk
        $basic['head']['title'] = empty($info["title"])?$info["name"].' - 齐装网资讯':$info["title"];
        $basic['head']['keywords'] = empty($info["keywords"])?$info["name"]:$info["keywords"];
        $basic['head']['description'] = empty($info["description"])?'齐装网'.$info["name"].'专区,提供'.$info["name"].'相关的资讯，供广大业主装修时参考,更多的'.$info["name"].'尽在齐装网栏目。':$info["description"];

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

        //最新知识
        $new = $logic->getContentLabels($info["id"],6,20);
        if (count($new) > 0) {
            $this->assign("new",$new);
        }
        $this->assign("id",$id);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("thematic",$info["name"]);
        $this->assign('basic',$basic);
        $this->display();
    }

    public function thematictulist(){
        $id = I("get.id");

        //获取内容列表
        $thematicWordsLogic = new ThematicWordsLogicModel();
        $info = S("Cache:Mobile:Thematic:Meitu:".$id);
        if (!$info) {
            $info = $thematicWordsLogic->getThematicInfoById($id);
            if (count($info) == 0 || $info["type"] != 2) {
                $this->_error();
                die();
            }
            S("Cache:Mobile:Thematic:Meitu:".$id, $info, 900);
        }

        $limit = 40;
        $data = $thematicWordsLogic->getThematicTuSearch($info["name"], $limit);

        // 相关知识
        $relatedList = S('Cache:Mobile:Thematic:related:' . $info["id"]);
        if (empty($relatedList)) {
            $relatedList = $thematicWordsLogic->getContentLabels($info["id"], 6, 10, 2);
            S('Cache:Mobile:Thematic:related:' . $info["id"], $relatedList, 900);
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

        // TDK (576 图片标签TDK调用)
        $basic['head']['title'] = $info["title"]. ($data["pageIndex"] > 1 ? "-【第".$data["pageIndex"]."页】" : "") . "-齐装网装修效果图";
        $basic['head']['keywords'] = $info["keywords"];
        $basic['head']['description'] = $info["description"];
        $basic['head']['canonical'] = C("QZ_YUMINGWWW") . '/tu/'.$id.'/';
        $this->assign("info", $info);
        $this->assign("basic", $basic);
        $this->assign("data", $data);
        $this->assign("relatedList", $relatedList);
        $this->display();
    }
}
