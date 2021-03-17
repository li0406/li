<?php

namespace app\index\controller\v1;

use app\common\model\logic\VoipLogic;
use app\common\model\logic\OptionsLogic;
use app\common\model\service\PhoneCallHollyService;
use think\Controller;
use think\Request;

Class Voip extends Controller
{
    public function voipCall(Request $request){

        $user_id = $request->user['user_id'];
        $user_name = $request->user['user_name'];

        //获取电话商设置
        $optionModel = new OptionsLogic();
        $channel = $optionModel->getMyTelCenterChannelByid($user_id);

        switch ($channel){
            case "holly":
                //合力电话
                $info = self::callHolly($request->tel,$user_id,$user_name,'sales');
                break;
            default:
                //默认电话
                $vLogic = new VoipLogic();
                $info = $vLogic->telephoneVoipCall($request->user,$request->tel);
                break;
        }

        return ['error_code' => $info['error_code'], 'error_msg' => $info['error_msg'],'data' => $info['data']];
    }

    /**
     * 合力电话呼叫
     * @param $order_id
     */
    public function callHolly($tel,$user_id,$user_name,$logType)
    {
        $service = new PhoneCallHollyService();
        //获取用户电话信息
        $userInfo = $service->getPhoneInfo($user_id);
        if (empty($userInfo["voipaccount"])) {
            return array("error_code"=>4000001,"error_msg"=>'请先设置VOIP号码');
        }

        //拨打电话
        $result = $service->call($userInfo["voipaccount"],$tel);

        if ($result["error_code"] == 200) {
            //添加回调日志
            switch ($logType){
                case 'sales':
                    $params = [
                        "record_style" =>1,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToSellerLog($params);
                    break;
            }
            return array("error_code" => 0,"error_msg"=>'通话成功！',"data"=>$result["data"]);
        }
        return array("error_code" => 4000001,"error_msg"=>$result["error_msg"]);
    }

    /**
     * 获取某次通话的通话记录
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getVoipRecordList(Request $request){
        $param = $request->param();
        if(empty($param['call_sid']) || empty($param['channel'])){
            return sys_response(4000002);
        }

        switch ($param['channel']) {
            case 'holly':
                $voipLogic = new VoipLogic();
                $calllist = $voipLogic->getVoipRecordHolly($param['call_sid']);
                break;
            
            default:
                $voipLogic = new VoipLogic();
                $calllist = $voipLogic->getVoipRecordDefault($param['call_sid']);
                break;
        }

        return json(sys_response(0, '', $calllist));
    }

    /**
     * 获取订单通话记录
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getOrderRecordList(Request $request){
        $orderid = $request->param("orderid");
        if (empty($orderid)) {
            return json(sys_response(4000002));
        }

        $logic = new VoipLogic();
        $calllist = $logic->getTelListByOrderId($orderid);
        if (!empty($calllist)) {
            return json(sys_response(0, '', [
                    "list" => $calllist
                ]));
        } else {
            return json(sys_response(4000001, '未找到对应的录音列表'));
        }
    }
}