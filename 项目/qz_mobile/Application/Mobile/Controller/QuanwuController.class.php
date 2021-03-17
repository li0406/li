<?php
// +----------------------------------------------------------------------
// |  全屋定制落地页
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Common\Model\Logic\QuanwuLogicModel;
use Mobile\Common\Controller\MobileBaseController;


class QuanwuController extends MobileBaseController
{

    public function index()
    {
        $basic['head']['title'] =  '全屋定制家具 - 整体定制橱柜衣柜免费测量设计 - 齐装网';
        $basic['head']['description'] =  '齐装网为您提供家具、衣柜、橱柜、榻榻米等全屋家具定制,提供免费测量设计等一站式服务,让您的装修变的更放心、更安心。';
        $basic['head']['keywords'] =  '全屋定制,全屋定制家具,定制橱柜,定制衣柜';
        $this->assign('basic',$basic);
        $this->display();
    }

    public function addRecord(){
        $data = $_POST;
        if(empty($data['tel']) || empty($data['name']) || empty($data['cs'])){
            $this->ajaxReturn(array("error_msg" => "必填项不能为空", "error_code" => 1));
        }
        //加一个手机号判断！！！
        $tel = remove_xss($data["tel"]);
        if (!empty($tel)) {
            $reg = "/^(13|14|15|16|17|18|19)[0-9]{9}$/";
            if (!preg_match($reg, $tel)) {
                $this->ajaxReturn(array("error_msg" => "请填写正确的手机号码", "error_code" => 1));
            }
        }

        $result = (new QuanwuLogicModel())->addRecord($data);

        if($result){
            $this->ajaxReturn(array( "error_code" => 0));
        }else{
            $this->ajaxReturn(array("error_msg" => "抱歉,请稍后再试", "error_code" => 1));
        }
    }

}

