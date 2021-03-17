<?php
namespace User\Controller;
use User\Common\Controller\UserCenterBaseController;
class EmptyController extends UserCenterBaseController{

    //空控制器
    public function index(){
        $this->_empty();
    }
}