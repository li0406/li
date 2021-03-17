<?php

//展销落地页管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class ZhanxiaoController extends HomeBaseController{
   public function index(){
        $list = D("Home/Logic/ActivityZhanhuiLogic")->getActivityList();
        $this->assign("list",$list);
        $this->display();
   }
}