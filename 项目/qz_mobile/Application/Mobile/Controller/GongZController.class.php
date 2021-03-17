<?php

namespace Mobile\Controller;

use Mobile\Common\Controller\MobileBaseController;

/**
 * 公装落地页
 * @description:
 */
class GongZController extends MobileBaseController
{
	public function _initialize()
	{
		parent::_initialize();


        //今日发单人数
        $time = strtotime(date('Y-m-d',time()).' 23:59:59');
        if($time < time()){
            $num = rand(20001,30000);
            S("Cache:Gongz:fbzrs",$num);
        }else{
            $num = S("Cache:Gongz:fbzrs");
            if(!$num){
                $num = rand(20001,30000);
                S("Cache:Gongz:fbzrs",$num);
            }
        }
        $this->assign("jrfdrs", $num);

	}
	
	public function index()
	{
		$info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
		$this->assign("mapUseInfo", $_SESSION["m_mapUseInfo"]);
		$this->assign("info", $info);
		$this->display();
	}

    //m端公装报价落地页 p.2.17.7
	public function gzbj(){
        $city = session('m_cityInfo');
        if(empty($city['name'])){
            $city['name'] = '';
        }
        //获取src
        $src = $_GET['src'];
        $info['city'] = $city['name'];
        $this->assign("src", $src);
        $this->assign('info',$info);
	    $this->display();
    }
    //m端公装报价落地页 p.2.17.7
	public function gzbjjg(){
        $SCHEME_HOST = $this->SCHEME_HOST;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {

            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                //增加发单人数
                $num = S("Cache:Gongz:fbzrs");
                if(!$num){
                    $num = rand(20001,30000);
                    S("Cache:Gongz:fbzrs",$num);
                } else{
                    $num = $num + 3;
                    S("Cache:Gongz:fbzrs",$num);
                }

                $result['total'] = $order["mianji"] * 600;
                $basic["head"]["title"] = "公装报价 - 工装装修价格预算 - 齐装网";
                $basic["head"]["description"] = "齐装网公装报价平台，汇集全国5万多家正规装修公司，为您提供办公室装修报价、写字楼装修报价、厂房装修报价、餐厅会所装修报价、宾馆酒店装修报价等公装项目价格预算清单。精选公装案例、工装标准化施工流程及服务保障，让您装修无忧。";
                $basic["head"]["keywords"] = "公装报价，工装装修价格，工装预算";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->display();
                die();
            }
        }
        header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/gzbj/");
//	    $this->display();
    }
}