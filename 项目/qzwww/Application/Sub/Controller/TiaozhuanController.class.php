<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class TiaozhuanController extends SubBaseController{
    public function index(){

    }

    public function tiaozhuan_mzb() {
            $csbm = I('get.csbm');
            if (empty($csbm)) { //如果没有get参数csbm
                list($csbm) = explode('.', $_SERVER['SERVER_NAME']);
            }
            header('HTTP/1.1 302 Moved Temporarily');
            if($csbm != "www"){
                    //header("Location: http://m.".C('QZ_YUMING')."/".$csbm."/");
                    //header("Location: http://www.qizuang.com/mobile/zb?cs=". $csbm); //新版招标单页
                    header("Location: ".$this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES') ."/zhaobiao/?bm=". $csbm); //新移动版招标 带城市

            }else{
                    //header("Location: http://m.".C('QZ_YUMING')."/");
                    //header("Location: http://www.qizuang.com/mobile/zb"); //新版招标单页
                    header("Location: ".$this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES') ."/zhaobiao"); //新移动版招标
            }
    }

}