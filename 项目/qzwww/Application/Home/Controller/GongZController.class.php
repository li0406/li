<?php
// +----------------------------------------------------------------------
// |  公装发单落地页
// +----------------------------------------------------------------------
namespace Home\Controller;

use Common\Model\Logic\QuyuLogicModel;
use Home\Common\Controller\HomeBaseController;


class GongZController extends HomeBaseController
{
    private $gongzhuangleixing = array('办公空间','厂房装修','店面装修','餐饮装修','娱乐场所','酒店装修','其他机构');
    /**
     * 公装发单落地页
     *
     * @return void
     */
    public function index()
    {

        $this->display();
    }

    //m端公装报价落地页 p.2.17.7
    public function gzbj(){
        $SCHEME_HOST = $this->SCHEME_HOST;


        $basic["head"]["title"] = "公装报价 - 工装装修价格预算 - 齐装网";
        $basic["head"]["description"] = "齐装网公装报价平台，汇集全国5万多家正规装修公司，为您提供办公室装修报价、写字楼装修报价、厂房装修报价、餐厅会所装修报价、宾馆酒店装修报价等公装项目价格预算清单。精选公装案例、工装标准化施工流程及服务保障，让您装修无忧。";
        $basic["head"]["keywords"] = "公装报价，工装装修价格，工装预算";
        $this->assign("basic",$basic);
        $this->assign('gongzhuangleixing',$this->gongzhuangleixing);

        // 底部二维码显示控制
        if (C("QZ_YUMING") == $SCHEME_HOST['domain']) {
            $ShowControlQRcode_n1 = 'on';
        } else {
            $ShowControlQRcode_n1 = 'off';
        }
        $this->assign("ShowControlQRcode",$ShowControlQRcode_n1);

        //获取城市(根据bm)
        $cityid = $this->theCityId;
        $quyulogic = new QuyuLogicModel();
        if($cityid){
            $cityinfo = $quyulogic->getCityInfoByCid($cityid);
        }else{
            $cityinfo = array();
        }
        $this -> assign('cityinfo',$cityinfo ? $cityinfo : '');

        //根据定位获取城市信息
        $ipcityinfo = getCityInfoByIp();
        $ipcityinfo = json_decode($ipcityinfo,true);
        $this -> assign('ipcityinfo',$ipcityinfo ? $ipcityinfo['data']['cname'] : '');

        //获取所有城市
        $allcity = S('Cache:Home:GongZ:Allcity');
        if(!$allcity){
            $allcity = $quyulogic ->getAllCity();
            S('Cache:Home:GongZ:Allcity',$allcity,86400);
        }
        $this -> assign('allcity',$allcity);

        $this->display();

    }


}

