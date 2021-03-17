<?php

/**
 * 微信小程序首页
 */

namespace WxApp\Controller;
use WxApp\Common\Controller\WxAppBaseController;

class IndexController extends WxAppBaseController {

    public function _initialize(){
        parent::_initialize();
    }

    public function index(){
        //暂时报404错误
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die;
    }
}