<?php
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class EmptyController extends ApiBaseController{

    //空控制器
    public function index(){
        $this->_empty();
    }
}


