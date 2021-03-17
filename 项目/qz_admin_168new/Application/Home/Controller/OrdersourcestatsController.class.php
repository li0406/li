<?php

//渠道来源统计

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class OrdersourcestatsController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
    }

    //首页
    public function index(){
        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        $condition['a.type'] = array('EQ','1');
        $result = $this->getList($condition,$pageIndex,$pageCount);
        $this->assign("sortby",$sortby);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("OrderSource")->getList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp);
    }
}