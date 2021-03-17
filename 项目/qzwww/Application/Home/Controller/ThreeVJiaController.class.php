<?php

namespace Home\Controller;
use Common\Model\Logic\QuyuLogicModel;
use Common\Model\Logic\ThreeVJiaLogicModel;
use Home\Common\Controller\HomeBaseController;
use Common\Validate\ThreeVJiaValidateModel;

class ThreeVJiaController extends HomeBaseController{

  public function index(){
      //获取所有省份
      $model = new QuyuLogicModel();

      $province = $model->getAllProvince();
      if (count($province) > 0) {
          $province_id = $province[0]["id"];
          $city = $model->getProvinceCityList($province_id);
          $this->assign("city",$city);
      }

      $this->assign("province",$province);
      $this->display();
  }

  public function register()
  {

      if ($_POST) {
          $data = array(
              "type" => I("post.type"),
              "industry" => I("post.industry"),
              "name" => I("post.name"),
              "tel" => I("post.tel"),
              "city_id" => I("post.city_id"),
              "province_id" => I("post.province_id"),
              "time" => time(),
          );

          $validate = new ThreeVJiaValidateModel();
          //验证规则
          $rules = array(
              array('type','require','身份未选择!',1),
              array('industry','require','行业未选择',1),
              array('name','require','姓名未填写',1),
              array('tel','/1\d{10}/','请输入正确的手机号',1,"regex"),
              array('city_id','require','请选择省',1),
              array('province_id','require','请选择市',1)
          );

          if (!$validate->validate($rules)->create($data)){
              // 如果创建失败 表示验证没有通过 输出错误提示信息
              $this->ajaxReturn(array("error_code" => 9000001,"error_msg" => $validate->getError()));
          }

          $model = new ThreeVJiaLogicModel();
          $i = $model->register($data);

          if ($i !== false) {
              $this->ajaxReturn(array("error_code" => 0,"error_msg" => "操作成功"));
          }
          $this->ajaxReturn(array("error_code" => 9000001,"error_msg" => '服务器出去旅游了，稍后回来！~'));
      }
  }

  public function getCity()
  {
      if ($_POST) {
          $province_id = I("post.id");
          $model = new QuyuLogicModel();
          $city = $model->getProvinceCityList($province_id);
          if (count($city) > 0) {
              $this->ajaxReturn(array("error_code" => 0,"error_msg" => "操作成功","data"=>$city));
          }
      }
      $this->ajaxReturn(array("error_code" => 9000001,"error_msg" => '服务器出去旅游了，稍后回来！~'));
  }
}
    
