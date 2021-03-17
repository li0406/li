<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;

class PoplayerController extends SubBaseController
{
    public function _initialize(){
        parent::_initialize();
    }

    public function pop(){
        $name = R("Common/Poplayer/pop");
        $this->ajaxReturn($name);
    }
}
