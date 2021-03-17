<?php

namespace Sub\Controller;

use Common\Model\Logic\OrdersLogicModel;
use Sub\Common\Controller\SubBaseController;

class QuoteController extends SubBaseController{

    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);

        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'].$this->bm.".".C("QZ_YUMING").$uri."/");
            }
        }

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
        $this->assign("tabIndex",5);
        $this->assign("cityinfo",$this->cityInfo);
    }

    public function index(){
        //获取当前城市id
        $cityInfo = $this->cityInfo;
        //移动端跳转处理
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header('Location: '.$this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES') .'/'.$cityInfo['bm'].$_SERVER['REQUEST_URI']);
            exit();
        }
        if(!$cityInfo['id'] || !$cityInfo['cname']){
            $this->_error();
        }
        //获取当前或全国20条装修订单数据 24小时缓存
        $ordersLogic = new OrdersLogicModel();
        $list = $ordersLogic->getBaojiaOrder($cityInfo['id'],$cityInfo['cname']);
        // 底部二维码显示控制
        $SCHEME_HOST = $this->SCHEME_HOST;
        if (C("QZ_YUMING") == $SCHEME_HOST['domain']) {
            $ShowControlQRcode_n1 = 'on';
        } else {
            $ShowControlQRcode_n1 = 'off';
        }
        $basic['canonical'] = $this->SCHEME_HOST['scheme_full'].$cityInfo['bm'].'.'.$this->SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $this->assign("ShowControlQRcode",$ShowControlQRcode_n1);
        $this->assign('list',$list);
        $this->assign('basic',$basic);
        $this->display();
    }


}