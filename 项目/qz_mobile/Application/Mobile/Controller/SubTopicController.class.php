<?php
namespace Mobile\Controller;
use Common\Model\Logic\SubthematicLogicModel;
use Mobile\Common\Controller\MobileBaseController;
use Common\Enums\ApiConfig;
class SubTopicController extends MobileBaseController{
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                $SCHEME_HOST = $this->SCHEME_HOST;
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$SCHEME_HOST['scheme_full']. $SCHEME_HOST['host'].$uri."/");
            }
        }
    }

    //分站专题页
    public function index(){
        $logic = new SubthematicLogicModel();
        //专题信息
        $info = $logic->getInfoByUrl($this->cityInfo['id'],I('get.category'));

        if(empty($info['id'])){
            $this->_error();
        }
        //TDK
        $basic['head']['title']  = $info['title'];
        $basic['head']['keywords']  = $info['keywords'];
        $basic['head']['description']  = $info['description'];

        //公司列表
        $info['company'] =  $logic->getCompanyList($info['id']);
        //案例列表
        $info['cases'] =  $logic->getCasesList($info['id']);
        //知识列表
        $info['article'] =  $logic->getArticleListV2($info['sub_tagid'],$this->cityInfo['id']);

        //最新专题
        $info['zhuanti'] =  $logic->getZhuantiList($this->cityInfo['id']);


       
        $this->assign('info',$info);
        $this->assign('basic',$basic);
        $this->display();
    }


}