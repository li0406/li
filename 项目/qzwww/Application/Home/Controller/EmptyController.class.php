<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class EmptyController extends HomeBaseController{

    //空控制器
    public function index(){
        $this->_empty();
    }
}


