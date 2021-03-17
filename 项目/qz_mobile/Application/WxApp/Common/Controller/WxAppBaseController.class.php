<?php

namespace WxApp\Common\Controller;
use Think\Controller;

class WxAppBaseController extends Controller {

    public function _initialize(){

        B("Common\Behavior\MarkTrackingOrderSourceCheck");
        //$this->assign('title',"齐装网");
    }

    //空操作
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die();
    }

    // Error
    public function _error(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die();
    }
}