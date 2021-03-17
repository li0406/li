<?php
namespace Home\Model\Service;
/**
 *
 */
class CronapiServiceModel
{
    private $url = "";

    public function __construct(){
        $config = C("SERVICE.CRONAPI");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);
    }

    public function Cron($data)
    {
        $url = $this->url."/v1/cron/";

        $url = $url."?".http_build_query($data);

        $headers = array(
            "accept:application/json"
        );

        $timeout = 6;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $headers);
        curl_setopt($ch, CURLOPT_URL, $url); //定义请求地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); //定义请求类型
        curl_setopt($ch, CURLOPT_HEADER,0); //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch); //关闭
        if ($result != false) {
            $json = json_decode($result,true);
            return $json;
        }
        return [];
    }
}
