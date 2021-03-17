<?php


namespace Mobile\Controller;
use Common\Model\Logic\SpecialkeywordLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class ZhishiController extends MobileBaseController
{
    //装修知识m站
    public function index(){
        $logic = new SpecialkeywordLogicModel();
        //攻略装修知识
        $gongluelist = S("Cache:Mobile:Zhishi:Gonglvelist");
        if(empty($gongluelist)){
            $gongluelist = $logic->getGongLveZhuanTiList(3);
            S("Cache:Mobile:Zhishi:Gonglvelist",$gongluelist,900);
        }
        $this->assign('gongluelist',$gongluelist);
        //百科装修知识
        $baikelist = S("Cache:Mobile:Zhishi:BaikeList");
        if(empty($baikelist)){
            $baikelist = $logic->getBaikeZhuanTiList(8);
            S("Cache:Mobile:Zhishi:BaikeList",$baikelist,900);
        }

        $this->assign('baikelist',$baikelist);

        //装修问答知识
        $wendalist = S("Cache:Mobile:Zhishi:WendaList");
        if(empty($wendalist)){
            $wendalist = $logic->getWendaZhuanTiList(10);
            S("Cache:Mobile:Zhishi:WendaList",$wendalist,900);
        }
        $this->assign('wendalist',$wendalist);
        //TDKs
        $basic['head']['title'] = '装修知识大全-房屋装修经验-房子装修攻略-新房装修心得-齐装网';
        $basic['head']['keywords'] = '装修知识,装修经验,装修攻略,装修心得';
        $basic['head']['description'] = '装网装修知识栏目为广大业主提供详细的装修经验，装修流程，装修设计及装修注意事项等内容，并且免费提供装修报价清单和装修设计方案。';
        $this->assign('basic',$basic);
        $this->display();
    }

}