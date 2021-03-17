<?php
namespace Common\Model\Service;
/**
 *
 */
class PnpServiceModel
{
    private $url = "";

    public function __construct(){
        $config = C("SERVICE.QZPNP");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);
    }


    public function getOrderTelx($order_id)
    {
        $url = $this->url."/v1/getordertel?order_id=".$order_id;
        $result = curl($url);
        if ($result["error_code"] == 0) {
           return $result["data"];
        }
        return [];
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
}