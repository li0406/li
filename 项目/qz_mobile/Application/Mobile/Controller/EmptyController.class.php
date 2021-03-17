<?php

namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;

class EmptyController extends MobileBaseController{

    //空控制器
    public function index(){
        $this->_empty();
    }
}