<?php

namespace app\index\controller\v1;

use Exception;
use think\Request;
use think\Controller;

use app\common\model\logic\AdminuserLogic;
use app\common\model\service\WechatService;

class Wechat extends Controller {

    public function initialize(){

    }

    /**
     * 小程序账号绑定
     * @param  Request        $request        [description]
     * @param  AdminuserLogic $adminuserLogic [description]
     * @return [type]                         [description]
     */
    public function appletBind(Request $request, AdminuserLogic $adminuserLogic){
        try {
            $code = $request->param("code");
            $appid = $request->param("appid");
            if (empty($code) || empty($appid)) {
                throw new Exception("", 4000002);
            }

            // 获取微信openid、unionid
            $wechatService = new WechatService($appid);
            $result = $wechatService->excute()->getOpenid($code);
            if (empty($result) || (isset($result["errcode"]) && $result["errcode"] != 0)) {
                throw new Exception("用户信息失败", 5000002);
            }

            $user_id = $request->user["user_id"];
            $unionid = !empty($result["unionid"]) ? $result["unionid"] : "";
            $result = $adminuserLogic->setUserOpenid($user_id, $appid, $result["openid"], $unionid);
            if (empty($result)) {
                throw new Exception("操作失败", 5000002);
            }

            $error_code = 0;
            $error_msg = "";
        } catch (Exception $e) {
            $error_code = $e->getCode();
            $error_msg = $e->getMessage();
        }
        
        return json(sys_response($error_code, $error_msg));
    }

}