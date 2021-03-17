<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class PoplayerController extends HomeBaseController
{
    public function _initialize(){
        parent::_initialize();
    }

    public function pop(){
        $name = R("Common/Poplayer/pop");
        $this->ajaxReturn($name);
    }
}
