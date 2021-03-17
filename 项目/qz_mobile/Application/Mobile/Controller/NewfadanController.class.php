<?php
/**
 * 移动版 - 发单2.0
 */
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class NewfadanController extends MobileBaseController{

    public function index(){
        $source = '17103016';
        $this->assign('source', $source);
        $this->display();
    }
}