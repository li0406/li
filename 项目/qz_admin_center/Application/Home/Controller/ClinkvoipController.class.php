<?php

namespace Home\Controller;
use Common\Model\Service\ClinkService;
use Home\Common\Controller\HomeBaseController;

class ClinkvoipController extends HomeBaseController
{
    public function index()
    {
        // 获取voip/坐席绑定信息
        $userInfo['user'] = D('Adminuser')->findUserInfoById(session("uc_userinfo.id"), 'clink');
        $this->assign("userInfo", $userInfo);
        $this->display();
    }

    public function up()
    {
        try {
            
            $voip = I("post.voip");
            //查询号码是否被占用
            $result = D('Admin_voip_tels')->getVoipInfoByvoipaccount($voip, 'clink');

            if (count($result) == 0) {
                $this->ajaxReturn(["error_code" => 401,"error_msg" => "未找到该坐席号"]);
            }

            if (count($result) > 0 && $result["use_id"] != 0 && $result["use_id"] != session("uc_userinfo.id")) {
                $this->ajaxReturn(["error_code" => 401,"error_msg" => "该坐席号已被绑定"]);
            }

            $data = [
                "use_id" => session("uc_userinfo.id"),
                "use_on" => 1,
                "use_time" => time(),
                "use_name" => session("uc_userinfo.name"),
            ];

            $result = D('Admin_voip_tels')->bindVoipInfoByvoipaccount($voip, 'clink', $data);

            if ($result != false) {
                $this->ajaxReturn(["error_code" => 0,"error_msg" => "绑定成功"]);
            }
            $this->ajaxReturn(["error_code" => 402,"error_msg" => "绑定失败"]);

        } catch (\Exception $e){
            $this->ajaxReturn(["error_code" => 402,"error_msg" => "绑定失败"]);
        }
    }

    public function unbind()
    {

        try {
            $voip = I("post.voip");
            //查询号码是否被占用
            $result = D('Admin_voip_tels')->getVoipInfoByvoipaccount($voip, 'clink');

            if (count($result) == 0 || $result["use_on"] == 0) {
                $this->ajaxReturn(["error_code" => 401,"error_msg" => "未找到该坐席号/坐席号未绑定"]);
            }

            if ( $result["use_id"] !=  session("uc_userinfo.id")) {
                $this->ajaxReturn(["error_code" => 401,"error_msg" => "不能解绑他人号码"]);
            }

            $result = D('Admin_voip_tels')->unbindVoipInfoByvoipaccount($voip,'clink');
            if ($result != false) {
                $this->ajaxReturn(["error_code" => 0,"error_msg" => "解绑成功"]);
            }
            $this->ajaxReturn(["error_code" => 402,"error_msg" => "解绑失败"]);

        } catch (\Exception $e){
            $this->ajaxReturn(["error_code" => 402,"error_msg" => "解绑失败"]);
        }
    }
}