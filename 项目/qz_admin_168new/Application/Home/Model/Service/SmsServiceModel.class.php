<?php

namespace Home\Model\Service;
/**
 *
 */
class SmsServiceModel
{
    /**
     * @param $template_id 【短信模版ID】
     * @param $tel 【发送的电话】
     * @param $params 【模版内容的参数】
     */
    public function sendSms($template_id,$tel,$params = [])
    {
        try {
            foreach ($params as $key => $param) {
                $params[$key] = $param."";
            }
            $url = C("SMS_HOST")."/v1/sendsms";

            $data = [
                "tel" => $tel,
                "params" => $params,
                "from_app" => C("SMS_APP_SLOT"),
                "sms_id" => $template_id,
                "referer" => $_SERVER["HTTP_REFERER"]
            ];

            $headers = [
                'Content-Type: application/json;charset=utf-8;',
            ];

            $data = json_encode($data,320);
            $headers[] = 'Content-Length: ' . strlen($data);
            $result = curl($url,$data,$headers);
        } catch (\Exception $e) {
            
        }
        
        return $result;
    }
}