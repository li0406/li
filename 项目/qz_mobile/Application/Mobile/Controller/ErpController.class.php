<?php

//ERP
namespace Mobile\Controller;

use Mobile\Common\Controller\MobileBaseController;
use Common\Validate\ErpUserValidateModel;
use Common\Model\Logic\ErpUserRegisterLogicModel;

class ErpController extends MobileBaseController{
  /**
   * 介绍ERP系统
   * @return string|mixed|void
   */
  public function erpjieshao() 
  {
      //SEO标题关键字描述
      $basic["head"]["title"] = '齐装云管家erp管理系统软件介绍 - 装修erp系统操作视频使用方法 - 齐装网';
      $basic["head"]["keywords"] = '装修erp系统使用说明，齐装云管家';
      $basic["head"]["description"] = '欢迎了解齐装云管家erp管理系统，这里提供齐装云管家软件的详细介绍和装修erp管理系统操作视频，齐装云管家erp软件操作简单，功能强大，可终身免费升级，更有专业客服团队随时响应客户所需。';
      $this->assign('basic',$basic);

      $this->display();
  }

  public function index()
  {
      if (IS_POST) {
          $data = [
              'name' => I('post.name'),
              'company_name' => I('post.company_name','','remove_xss'),
              'tel' => I('post.tel','','remove_xss'),
              'city_id' => I('post.city_id','','remove_xss'),
              'province_id' => I('post.province_id','','remove_xss'),
              'time' => time(),
              'source' => "m端",
          ];

          $validate = new ErpUserValidateModel();
          //验证规则
          $rules = [
              ['name', 'require', '姓名未填写', 1],
              ['company_name', 'require', '公司名称未填写', 1],
              ['tel', '/1\d{10}/', '请输入正确的手机号', 1, 'regex'],
          ];

          if (!$validate->validate($rules)->create($data)) {
              // 如果创建失败 表示验证没有通过 输出错误提示信息
              $this->ajaxReturn(array('error_code' => 9000001, 'error_msg' => $validate->getError()));
          }

          $model = new ErpUserRegisterLogicModel();
          $i = $model->register($data);

          if ($i !== false) {
              $this->ajaxReturn(array('error_code' => 0, 'error_msg' => '操作成功'));
          }
          $this->ajaxReturn(array('error_code' => 9000001, 'error_msg' => '服务器出去旅游了，稍后回来！~'));
      }
      //SEO标题关键字描述
      $basic["head"]["title"] = '装修erp - 齐装云管家 - 一款好用的装修公司erp管理系统软件 - 齐装网';
      $basic["head"]["keywords"] = '装修erp，齐装云管家，云管家，装修公司erp管理系统';
      $basic["head"]["description"] = '装修erp管理系统哪个好用？首选齐装云管家，专为装修公司打造的装修erp管理系统软件，提供一站式装企信息化管理解决方案，帮助传统装修公司快速转型、互联网装企轻松落地。';
      $this->assign('basic',$basic);

      $this->display();
  }

}