<?php

// 招商
namespace Mobile\Controller;

use Mobile\Common\Controller\MobileBaseController;

class ZhaoShangController extends MobileBaseController{

  public function index()
  {
      //SEO标题关键字描述
      $basic["head"]["title"] = '装修公司入驻招商 - 齐装网';
      $basic["head"]["keywords"] = '装修公司入驻招商，装修公司入驻平台';
      $basic["head"]["description"] = '齐装网互联网装修平台诚邀各地装修公司入驻，加入齐装网，装修公司即可获得全网营销方案、稳定的订单量、独立企业网店、免费装企培训课程、精准营销推广及完善的售后服务。现在申请，立即开始接单盈利。';
      $this->assign('basic',$basic);

      $this->display();
  }

}
