<?php
namespace Home\Model\Service;
use Home\Model\AdminuserModel;
use Home\Model\Db\LogClinkDiyTelcenterModel;
use Home\Model\Db\LogClinkOrderTelcenterModel;
use Home\Model\Db\LogClinkReviewTelcenterModel;
use Home\Model\Db\LogClinkTelcenterModel;
use think\Log;

/**
 *
 */
class PhoneCallClinkServiceModel extends PhoneCallBaseServiceModel
{
    private $solution = "clink";
    private $accessKeyId = "";//客服云平台颁发给客户端的访问密钥 ID
    private $accessSecret = "";
    private $expires = 60 * 3; //签名的有效时间（秒）最大值为 86400（24 小时）

    public function  __construct()
    {
        $this->accessSecret = C("CLINK.SECRET");
        $this->accessKeyId =  C("CLINK.KEY");;
    }

    function getPhoneInfo($user_id)
    {
        $model = new AdminuserModel();
        $userInfo = $model->findUserVoipInfo($user_id, $this->solution);
        if (count($userInfo) > 0) {
            return $userInfo;
        }
        return [];
    }

    public function call($call_no, $caller_no)
    {
        $data = [
            "cno" => $call_no,
            "customerNumber" => $caller_no,
            "requestUniqueId" => uuid(""),
        ];

        $param["AccessKeyId"] = $this->accessKeyId;
        $param["Expires"] = $this->expires;
        $param["Timestamp"] =  date("Y-m-d\TH:i:s\Z");
        $signature = self::getSignature($param,"POST","api-bj.clink.cn/callout");
        $result = self::dialout($param,$data,$signature);
        return $result;
    }

    public function callBackToOrderLog($params)
    {
        $model = new LogClinkOrderTelcenterModel();
        return $model->addInfo($params);
    }

    public function callBackToSellerLog($params)
    {
        // TODO: Implement callBackToSellerLog() method.
    }

    public function callBackToOrderReviewLog($params)
    {
        $model = new LogClinkReviewTelcenterModel();
        return $model->addInfo($params);

    }

    public function callBackToOtherLog($params)
    {
        // TODO: Implement callBackToOtherLog() method.
    }

    public function callBackToDiyLog($params)
    {
        $model = new LogClinkDiyTelcenterModel();
        return $model->addInfo($params);
    }

    public function getMediaId($call_sid)
    {
        $model = new LogClinkTelcenterModel();
        return $model->getRecodeInfo($call_sid);
    }

    public function getClinkRecord($cdr_main_unique_id = "",$call_sid = "")
    {
        $param["AccessKeyId"] = $this->accessKeyId;
        $param["Expires"] = $this->expires;
        $param["Timestamp"] =  date("Y-m-d\TH:i:s\Z");
        $param["mainUniqueId"] = $cdr_main_unique_id;

        $signature = self::getSignature($param,"GET","api-bj.clink.cn/describe_record_file_url");

        $param = http_build_query($param);
        $param .= "&Signature=".$signature;
        $url = "https://api-bj.clink.cn/describe_record_file_url?".$param;
        $result = curl($url);
        if (isset($result["recordFileUrl"])) {

            $options = array(
                'http' => array(
                    'timeout' => 20, //设置一个超时时间，单位为秒
                )
            );
            $context = stream_context_create($options);
            $saudio  = file_get_contents($result["recordFileUrl"], false, $context);

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
            return $result["recordFileUrl"];
        }
        return "";
    }

    public function getOrderTelLogById($order_id)
    {
        $model = new LogClinkOrderTelcenterModel();
        return $model->getOrderTelLogById($order_id);
    }

    public function getReviewOrderTelLogById($order_id, $adminIds = [])
    {
        $model = new LogClinkReviewTelcenterModel();
        return $model->getOrderTelLogById($order_id, $adminIds);
    }

    /**
     * @param $call_no 主叫电话
     * @param $caller_no 被叫电话
     */
    private function dialout($param, $data,$signature)
    {
        $param = http_build_query($param);
        $param .= "&Signature=".$signature;
        $url = "https://api-bj.clink.cn/callout?".$param;
        $headers = [
            "Content-Type: application/json",
        ];
        $data = json_encode($data);
        $result = curl(trim($url),$data,$headers);

        if (isset($result["result"])) {
            //拨打成功,添加回调表
            return  ["code" => 0,"errmsg" => "通话成功！","data" =>$result["result"]["requestUniqueId"]];
        }
        return ["code" => 401,"errmsg" => $result["error"]["message"]];
    }

    private  function getSignature($param,$methed,$url){
        //正向排序
        ksort($param,SORT_STRING);
        $param =  http_build_query($param);
        return urlencode(base64_encode(hash_hmac("sha1",$methed.$url."?".$param, $this->accessSecret,true)));
    }

    public function checkClinkStatus($voip)
    {
        $param["AccessKeyId"] = $this->accessKeyId;
        $param["Expires"] = $this->expires;
        $param["Timestamp"] =  date("Y-m-d\TH:i:s\Z");
        $param["cnos"] =  $voip;
        $signature = self::getSignature($param,"GET","api-bj.clink.cn/agent_status");
        $param = http_build_query($param);
        $param .= "&Signature=".$signature;
        $url = "https://api-bj.clink.cn/agent_status?".$param;
        $result = curl($url);
        return $result;
    }
}