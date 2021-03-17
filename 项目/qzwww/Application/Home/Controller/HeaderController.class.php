<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class HeaderController extends HomeBaseController
{
    public function _initialize(){
        parent::_initialize();
    }

    public function headerCity(){
        R("Common/Header/headerCity");
        exit;
    }
}
