<?php
namespace Home\Model\Service;

use Home\Model\Logic\HollyLogicModel;
use Home\Model\Service\PhoneCallBaseServiceModel;
use Home\Model\AdminuserModel;
use Library\Org\Holly\Holly;

/**
 *
 */
class PhoneCallHollyServiceModel extends PhoneCallBaseServiceModel
{

    private $solution = "holly";

    /**
     * 获取用户电话信息
     * @param $user_id
     * @return mixed
     */
    function getPhoneInfo($user_id)
    {
        $model = new AdminuserModel();
        $userInfo = $model->findUserVoipInfo($user_id, $this->solution);
        if (count($userInfo) > 0) {
            return $userInfo;
        }
        return [];
    }

    /**
     * 打电话
     * @param $call_no 主叫号码
     * @param $caller_no 被叫号码
     * @return mixed
     */
    public function call($call_no, $caller_no)
    {
        $holly = new Holly();
        $holly->GenToken();
        $call_sid = $holly->uuid(); // call_sid
        $ExternalData = "CallSid:". $call_sid; // 扩展数据 callsid, : 分割kv  , 分割组
        $callResult = $holly->dialout($call_no,$caller_no,'', $ExternalData);

        if ($callResult["api_resp"] == "200") {
            return array("error_code" =>$callResult["api_resp"],"error_msg" => $callResult["api_msg"],"data" => $call_sid );
        }
        return array("error_code" =>$callResult["api_resp"],"error_msg" => $callResult["api_msg"] );
    }

    /**
     * 订单回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToOrderLog($params)
    {
        $model = new HollyLogicModel();
        return $model->addOrderTelLog($params);
    }

    /**
     * 销售回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToSellerLog($params)
    {
        // TODO: Implement callBackToSellerLog() method.
    }

    /**
     * 客服回访回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToOrderReviewLog($params)
    {
        $model = new HollyLogicModel();
        return $model->addOrderReviewTelLog($params);
    }

    /**
     * 非客服订单回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToOtherLog($params)
    {
        $model = new HollyLogicModel();
        return $model->addOrderOtherTelLog($params);
    }

    /**
     * 合力亿捷 透传通话录音
     * 解决录音url中包含了被叫号码的问题
     * @param  string $call_sid call_sid
     * @return mixed 录音媒体 || 文本错误信息
     */
    public function recordFile($call_sid) {
        if (empty($call_sid)) {
            die("错误:call_sid不能为空");
        }

        // 通过call_sid 查询 通话记录中的录音文件url
        $LogHollyTelcenterDb = D('Home/Db/LogHollyTelcenter');
        $RecordFile = $LogHollyTelcenterDb->getRecordFileByCallSid($call_sid);
        $record_file_url = '';
        if (!empty($RecordFile)) {
            $record_file_url = $RecordFile['record_file_url'];
        }
        if (!empty($record_file_url)) {

            $options = array(
                'http' => array(
                    'timeout' => 20, //设置一个超时时间，单位为秒
                )
            );
            $context = stream_context_create($options);
            $saudio  = file_get_contents($record_file_url, false, $context);

            $size = strlen($saudio);
            $begin = 0;
            $end = $size - 1;
            header ( "Content-Type: audio/mpeg" ); //文件媒体类型 mp3
            // header ( 'Cache-Control: public, must-revalidate, max-age=0' );
            header ( "Pragma: no-cache" ); //禁止CDN缓存
            // header ( 'Accept-Ranges: bytes' );
            header ( "Content-Length:" . (($end - $begin) + 1) );
            header ("Content-Disposition:attachment;filename=".$call_sid.'_'.date("YmdHis").".mp3"); //下载后的文件名
            print $saudio; //打印到body
            die();
        } else {
            die('错误:'. $call_sid .'未查询到有录音文件');
        }

    }

    /**
     * 自助电话回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToDiyLog($params)
    {
        $model = new HollyLogicModel();
        return $model->addDiyTelLog($params);
    }
}