<?php
namespace Home\Model\Service;

use Think\Exception;

class PnpServiceModel
{
    private $url = "";

    public function __construct(){
        $config = C("SERVICE.QZPNP");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);
    }

    public function BindOrderTelX($order_id)
    {
        $url = $this->url."/v1/bindordertel";

        $data = [
            "order_id" => $order_id
        ];
        $headers = [
            "Content-Type: application/json",
        ];
        $data = json_encode($data);
        $result = curl($url,$data,$headers);
        return $result;
    }

    public function unBindTel($order_id)
    {
        $url = $this->url."/v1/unbindordertel";
        $data = [
            "order_id" => $order_id
        ];
        $headers = [
            "Content-Type: application/json",
        ];
        $data = json_encode($data);
        $result = curl($url,$data,$headers);

        return $result;
    }

    public function unBindTelByTel($tel)
    {
        $url = $this->url."/v1/unbindtel";
        $data = [
            "tel" => $tel,
        ];
        $headers = [
            "Content-Type: application/json",
        ];

        $data = json_encode($data);
        $result = curl($url,$data,$headers);
        return $result;
    }

    public function unbindOrderTel($order_id){
        $url = $this->url . "/v1/axb/unbind_order_tel";
        try {
            $data = [
                "order_id" => $order_id,
            ];

            $headers = [
                "Content-Type: application/json",
            ];

            $data = json_encode($data);
            $result = curl($url, $data, $headers);
            if (empty($result) || !isset($result["error_code"])) {
                throw new Exception("Error Processing Request", 1);
            }
        } catch (Exception $e) {
            $result = [
                "error_code" => 1000001,
                "error_msg" => "PNP服务请求失败"
            ];
        }

        return $result;
    }
}