<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class EmptyController extends SubBaseController{

    //空控制器
    public function index(){
        $this->_empty();
    }
}