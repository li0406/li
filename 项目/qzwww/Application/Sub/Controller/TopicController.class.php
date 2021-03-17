<?php


namespace Sub\Controller;
use Common\Model\Logic\SubthematicLogicModel;
use Sub\Common\Controller\SubBaseController;

class TopicController extends SubBaseController
{
    public function _initialize(){
        parent::_initialize();
    }

    //分站专题页
    public function index(){
        if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') .'/'. $this->cityInfo['bm'] . $_SERVER['REQUEST_URI']);
            exit();
        }
        $logic = new SubthematicLogicModel();
        //专题信息
        $info = $logic->getInfoByUrl($this->cityInfo['id'],I('get.category'));
        
        if(empty($info['id'])){
            $this->_error();
        }
        //公司列表
        $info['company'] =  $logic->getCompanyList($info['id']);
        //案例列表
        $info['cases'] =  $logic->getCasesList($info['id']);
        //知识列表
        $info['article'] =  $logic->getArticleListV2($info['sub_tagid'],$this->cityInfo['id']);

        //最新专题
        $info['zhuanti'] =  $logic->getZhuantiList($this->cityInfo['id']);


        $info['cityname'] = $this->cityInfo['name'];
        $info['bm'] = $this->cityInfo['bm'];

        // 截取专题名称的城市名称的城市名称


        $newtitle = str_replace($this->cityInfo['name'], "", $info["sub_tag_name"]);
        if (!$newtitle) {
            $newtitle = str_replace($this->cityInfo['name'], "", $info["tag_name"]);
        }

        // 区域专题链接
        $sameTag = $logic->getSameCaseTag($this->cityInfo['id'], $newtitle);

        if (count($sameTag)) {
            $friendLink["sameTagList"]["title"] = "区域" . $newtitle;
            $friendLink["sameTagList"]["list"] = $sameTag;
            $this->assign("friendLink", $friendLink);
        }



        $this->assign('info',$info);
        $this->display();
    }

}