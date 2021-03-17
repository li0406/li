<?php
namespace Home\Common\Controller;
use Common\Controller\BaseController;
class HomeBaseController extends BaseController {
    public function _initialize(){
        parent::_initialize();
        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
    }
}