<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class CompanyxgtController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        //添加顶部搜索栏信息
//        $this->assign('serch_uri','companysearch');
//        $this->assign('serch_type','装修公司');
//        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
    }

    public function index(){

        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $this->display();
    }




}