<?php
/**
 * About
 */
namespace Mobile\Controller;
use Common\Model\Logic\MediaReportLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class AboutController extends MobileBaseController{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){
        $this->error("404");
    }

    // 关于我们
    public function about(){
        $this->display("about");
    }

    //权益保护
    public function quanyi(){
        $this->display('quanyi');
    }


    // 免责声明
    public function getLegal(){
        $this->display('legal');
    }

   //复制免责声明
    public function getLegal_zst(){
        $this->display("legal_zst");
    }

    // 复制免责声明
    public function getLegal_no(){
        $this->display('legal_no');
    }

    // 1047 制作2个装小七发单落地页
    public function getLegal_new(){
        $this->display('legal_new');
    }

    // 复制免责声明
    public function getLegal_zx(){
        $this->display('legal_zx');
    }

    //媒体报道
    public function media(){
        $assign = MediaReportLogicModel::getMediaReports();
        $this->assign($assign);
        $this->assign("tabIndex",0);
        $this->assign("navIndex",9);
        $this->display();
    }

}