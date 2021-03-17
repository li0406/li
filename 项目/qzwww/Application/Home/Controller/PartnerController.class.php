<?php

//合作商管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class PartnerController extends HomeBaseController{

    // 获取异业合作广告信息
    public function getPartneradInfo(){
        if($_POST) {
            $post = I('post.source');
                $info = D("Common/Logic/PartneradLogic")->getPartneradInfo($post);
                if($info){
                    D("Common/Logic/PartneradLogic")->addUv($info["id"]);
                    $this->ajaxReturn(array("info"=>"获取异业广告成功","error_code"=>0,"data"=>$info));
                }else{
                    $this->ajaxReturn(array("info"=>"获取异业广告失败","error_code"=>1));
                }
        }
        $this->ajaxReturn(array("info"=>"获取失败","error_code"=>1));
    }

    //新增异业广告click
    public function addPartneradClick(){
        if($_POST) {
            $post = I('post.id');
            $info = D("Common/Logic/PartneradLogic")->addClick($post);
            if($info){
                $this->ajaxReturn(array("info"=>"操作成功","error_code"=>0));
            }else{
                $this->ajaxReturn(array("info"=>"操作失败","error_code"=>1));
            }
        }
        $this->ajaxReturn(array("info"=>"操作失败","error_code"=>1));
    }
}
