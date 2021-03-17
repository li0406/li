<?php

namespace Home\Model\Service;
/**
 *
 */
class QzorderServiceModel
{
    private $url;

    public function __construct(){
        $config = C("SERVICE.QZORDER");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);
    }

    public function orderRecall($order_id,$uid){
        $data = [
            "order_id" => $order_id,
            "operator" => $uid
        ];
        $headers = [
            'Content-Type: application/json;charset=utf-8;',
        ];
        $url = $this->url.'/v1/order/recall';
        $data = json_encode($data,320);
        $headers[] = 'Content-Length: ' . strlen($data);
        $result = curl($url,$data,$headers);
        return $result;
    }
}