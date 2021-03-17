<?php

namespace app\common\model\service;

use think\Exception;

class SmsService
{
    /**
     * 发送短信
     * @param mixed $template_id 【模版ID】
     * @param mixed $tel 【发送电话】
     * @param mixed $params 【模版参数】
     */
    public function sendSms($template_id,$tel,$params = [])
    {
        try {
            foreach ($params as $key => $param) {
                $params[$key] = $param."";
            }
            $url = config("SMS_HOST")."/v1/sendsms";

            $data = [
                "tel" => $tel,
                "params" => $params,
                "from_app" => config("SMS_APP_SLOT"),
                "sms_id" => $template_id,
                "referer" => empty($_SERVER["HTTP_REFERER"])?"zxs.api.qizuang.com":$_SERVER["HTTP_REFERER"]
            ];
            $headers = [
                'Content-Type: application/json;charset=utf-8;',
            ];

            $data = json_encode($data,320);
            $headers[] = 'Content-Length: ' . strlen($data);
            $result = curl($url,$data,$headers);
        } catch (Exception $e) {
            $result = sys_response(1000001, "短信服务调用失败");
        }

        return $result;
    }

    /**
     * 设置加密后的验证码cookie
     * @param  $code 加密后的验证码
     * @param  $tel  手机号
     * @param  $time 当前时间
     * @return 无
     */
    public function setSafeCodeCookie ($code, $tel, $time,$step) {

        //加密code
        $authcode = authcode($code, '');
        //设置cookie
        $arr = array("code"=>$authcode,"tel"=>$tel); //做一个数组记录 加密的验证码 和 手机号码
        $serialize = serialize($arr); //序列化
        setcookie("w_ordersafecode", $serialize, $time+($step*60),'/', '.'.APP_HTTP_DOMAIN); //放入cookie
    }

    public function setPasswordCodeCookie($code, $tel, $time,$step)
    {
        //加密code
        $authcode = authcode($code, '');
        //设置cookie
        $arr = array("code"=>$authcode,"tel"=>$tel,"mode"=>1);
        $serialize = serialize($arr); //序列化
        setcookie("w_getpassword", $serialize, $time+($step*60),'/', '.'.APP_HTTP_DOMAIN); //放入cookie

    }
}