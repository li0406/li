<?php
namespace Meitu\Controller;
use Meitu\Common\Controller\MeituBaseController;
class EmptyController extends MeituBaseController{

    //空控制器
    public function index(){
        $this->_empty();
    }
}