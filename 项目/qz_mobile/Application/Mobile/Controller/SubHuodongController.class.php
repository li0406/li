<?php
/**
 * 分站活动页
 */
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class SubHuodongController extends MobileBaseController{
    public function index(){
      
        $this->display();
    }
    // 活动，居然之家
    public function juranzhijia(){
        $currentBm = $this->$bm['cityInfo']['bm'];
        $basic["head"]["title"] = "成都居然之家金沙店活动特惠-成都齐装网";
        $basic["head"]["keywords"] = "成都居然之家";
        $basic["head"]["description"] = "成都居然之家金沙店与齐装网深度合作，凡是在齐装网免费获取装修报价的同时，再送家居优惠券！";
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('info',$info);
        $this->assign('cityid',$cityid);
        if("cd" == $currentBm){
            $this->assign("basic",$basic);
            $this->display();
        } else{
            $this->error();
        }
        
    }
    
}