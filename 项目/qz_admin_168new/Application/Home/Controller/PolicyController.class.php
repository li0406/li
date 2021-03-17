<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderCompanyReviewLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\PolicyLogicModel;
use Home\Model\Logic\UcenterUserLogicModel;
use Home\Model\Logic\UserLogicModel;
use Library\Org\Umeng\Umeng;

/**
 *
 */
class PolicyController extends HomeBaseController
{
    public function index()
    {
        $begin = I("get.begin") == ""?date("Y-m-d",strtotime("-3 month")):I("get.begin");
        $end = I("get.end") == ""?date("Y-m-d"):I("get.end") ;
        $order_id = I("get.order_id");
        $apply = I("get.apply");
        $jc = I("get.jc");
        $sh_status = I("get.sh_status");
        $zq_status = I("get.zq_status");

        $loigc = new PolicyLogicModel();
        $list = $loigc->getPolicyList($begin,$end,$order_id,$apply,$jc,$sh_status,$zq_status);
        $this->assign("list",$list);
        $this->assign("begin",$begin);
        $this->assign("end",$end);
        $this->display();
    }

    public function policyUp()
    {
        if (IS_POST) {
            $data = [
                "audit_status" => I("post.sh_status"),
                "fail_cause" => I('post.remark'),
                "audit_time" => time(), // 审核时间
                "updated_at" => time(),
                "op_uid" => session("uc_userinfo.id"),
                "op_name" => session("uc_userinfo.name"),
            ];

            if ($data["audit_status"] == 2 && empty( $data["fail_cause"]) ) {
                $this->ajaxReturn(["error_code" => 1,"error_msg" => "请填写审核不通过原因"]);
            }

            //查询申请信息
            $id = I("post.id");
            $logic = new PolicyLogicModel();
            $info = $logic->getPolicyInfo($id);
            if ($info["audit_status"] == 1) {
                unset($data["op_uid"]);
                unset($data["op_name"]);
            }

            if ($data["audit_status"] == 2) {
                //不通过重置装企审核状态
                $data["company_status"] = 0;
            }

            //审核通过的齐装保申请无法修改审核状态
            if (($info["audit_status"]) == 1 && ($data["audit_status"] != 1)) {
                $this->ajaxReturn(["error_code" => 1, "error_msg" => "审核通过的申请无法修改"]);
            }


            $i = $logic->updateInfo($id,$data);
            //添加操作记录
            $logData[] = array(
                "remark" => "齐装宝订单审核",
                "username" => session("uc_userinfo.name"),
                "userid" => session("uc_userinfo.id"),
                "action_id" => $id,
                "action" => CONTROLLER_NAME . '/' . ACTION_NAME,
                "logtype" => "policy",
                "time" => date("Y-m-d H:i:s"),
                "ip" =>  get_client_ip(),
                'user_agent' => $_SERVER["HTTP_USER_AGENT"]
            );

            D('LogAdmin')->addAllLog($logData);

            //B端推送标题
            $b_msg = "您的装修业主申请了齐装保服务，请及时进行确认 >>";
            //C端推送标题
            $c_msg = "您的齐装保申请结果出来啦，看一看吧！";
            if ($i !== false) {
                //审核成功 推送装修公司和业主
                //app推送 站内信推送
                //ios推送
                //查询用户的TOKEN
                $uuid = $info["uuid"];
                $logic = new UcenterUserLogicModel();
                $userDevice = $logic->getUserDeviceTokenById($uuid);
                import('Library.Org.Umeng.Umeng', "", ".php");

                //推送业主端消息
                if (count($userDevice) > 0) {
                    if ($userDevice["system"] == 1) {
                        $c_appkey_ios = C('qz_umeng_appkey_ios');
                        $c_secret_ios = C('qz_ument_appMasterSecret_ios');
                        // ios推送
                        $send = new Umeng($c_appkey_ios, $c_secret_ios);
                        $send->sendIOSListcast($userDevice["device_token"], "", "", $c_msg,1);
                    } else {
                        $c_appkey_android = C('qz_umeng_appkey_android');
                        $c_secret_android = C('qz_ument_appMasterSecret_android');
                        $c_activity_android = C('qz_umeng_activity_android');
                        //android  推送
                        $send = new Umeng($c_appkey_android, $c_secret_android);
                        $send->sendAndroidListcast($userDevice["device_token"], "", "", "", $c_msg, "", 'go_app', 1, true, $c_activity_android);
                    }
                    //添加通知
                    $logic->sendUserNotice([$uuid],$info["id"]);
                }

                if ($data["audit_status"] == 1) {
                    //审核成功 推送装修公司
                    import('Library.Org.Umeng.Umeng', "", ".php");
                    $b_appkey_ios = C('umeng_appkey_ios');
                    $b_secret_ios = C('ument_appMasterSecret_ios');
                    $b_appkey_android = C('umeng_appkey_android');
                    $b_secret_android = C('ument_appMasterSecret_android');
                    $b_activity_android = C("umeng_activity_android");

                    //查询装修公司的TOKEN
                    $company_id = $info["company_id"];
                    $logic = new UserLogicModel();
                    $deviceTokens = $logic->getCompanyDeviceTokenById($company_id);

                    if (!empty($deviceTokens)) {
                        //ios推送
                        $send = new Umeng($b_appkey_ios, $b_secret_ios);
                        $send->sendIOSListcast($deviceTokens, "", "", $b_msg);

                        //android  推送
                        $mi_activity = 'com.qizuang.sjd.push.MiPushActivity';
                        $send = new Umeng($b_appkey_android, $b_secret_android);
                        $send->sendAndroidListcast($deviceTokens, "", "", '', $b_msg, "", 'go_app', 1, true, $b_activity_android);
                    }

                    //添加通知
                    $logic->sendComapnyNotice([$company_id],$info["id"]);


                    // 修改订单状态
                    $ordersLogic = new OrdersLogicModel();
                    $ordersLogic->PolicyQianDanOrders($info["order_id"], $info);


                }

                $this->ajaxReturn(["error_code" => 0]);
            }
            $this->ajaxReturn(["error_code" => 1,"error_msg" => "操作异常！"]);
        } else {
            $id = I("get.id");
            //获取申请的数据
            $logic = new PolicyLogicModel();
            $info = $logic->getPolicyInfo($id);

            //审核状态未确定之前修改最后的操作人
            if ($info["audit_status"] != 1) {
                $data = [
                    "op_uid" => session("uc_userinfo.id"),
                    "op_name" => session("uc_userinfo.name"),
                    "updated_at" => time()
                ];
                $logic->updateInfo($id,$data);
            }

            //订单信息
            $logic = new OrdersLogicModel();
            $order = $logic->getPolicyOrderInfo($info["order_id"]);

            //申请公司的量房状态
            if (!empty($info["company_id"])) {
                $order["liangfang_status"] = $logic->getPolicyOrderLfInfo($info["order_id"],$info["company_id"]);
            }
            
            //回访数据
            $logic = new OrderCompanyReviewLogicModel();
            $review = $logic->getPolicyReviwInfo($info["order_id"]);
            if (count($review) > 0) {
                $order["review_feedback"] = $review["review_feedback"];
            }

            $this->assign("order",$order);
            $this->assign("info",$info);
            $tmp = $this->fetch("policyup");
            $this->ajaxReturn(["error_code" => 0,"data" => $tmp]);
        }
    }
}