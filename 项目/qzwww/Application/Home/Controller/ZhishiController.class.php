<?php


namespace Home\Controller;
use Common\Model\FriendlinkModel;
use Common\Model\Logic\SpecialkeywordLogicModel;
use Home\Common\Controller\HomeBaseController;

class ZhishiController extends HomeBaseController
{
    //装修知识pc站
    public function index(){
        $logic = new SpecialkeywordLogicModel();
        //攻略装修知识
       $gongluelist = S("Cache:Home:Zhishi:Gonglvelist");
        if(empty($gongluelist)){
            $gongluelist = $logic->getGongLveZhuanTiList(8);
            S("Cache:Home:Zhishi:Gonglvelist",$gongluelist,900);
        }
        $this->assign('gongluelist',$gongluelist);

        //百科装修知识
        $baikelist = S("Cache:Home:Zhishi:BaikeList");
        if(empty($baikelist)){
            $baikelist = $logic->getBaikeZhuanTiList(8);
            S("Cache:Home:Zhishi:BaikeList",$baikelist,900);
        }
        $this->assign('baikelist',$baikelist);

        //装修问答知识
        $wendalist = S("Cache:Home:Zhishi:WendaList");
        if(empty($wendalist)){
            $wendalist = $logic->getWendaZhuanTiList(10);
            S("Cache:Home:Zhishi:WendaList",$wendalist,900);
        }
        $this->assign('wendalist',$wendalist);

        //友情链接
        $fdlinkmodel = new FriendlinkModel();
        $fdlink = $fdlinkmodel->getFriendLinkList("000001",1,"zhishi");
        $this->assign('frendlink',$fdlink);

        //TDK
        $head['title'] = '装修知识大全-房屋装修经验-房子装修攻略-新房装修心得-齐装网';
        $head['keywords'] = '装修知识,装修经验,装修攻略,装修心得';
        $head['description'] = '装网装修知识栏目为广大业主提供详细的装修经验，装修流程，装修设计及装修注意事项等内容，并且免费提供装修报价清单和装修设计方案。';
        $this->assign('head',$head);

        //获取黄历报价模版
        $this->assign("hlBaoJia",$this->fetch(T("Common@Order/hlbaojia2")));
        //导航栏标识
        $this->assign("tabIndex",5);
        $this->assign('choose_gonglue', 'zhishi');
        $this->display();
    }

}