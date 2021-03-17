<?php
namespace Home\Model\Service;
/**
 *
 */
class AdminApiService
{
    private $url = "";

    public function __construct(){
        $config = C("SERVICE.ADMINAPI");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);
    }

    public function getPnpList($tel,$city,$status,$page)
    {
         $url = $this->url."/pnp/v1/getpnptellist";

         $data = [
             "tel" => $tel,
             "city" => $city,
             "status" => $status,
             "page" => $page,
         ];

         $url = $url."?".http_build_query($data);

         $header = [
             "token:".session("uc_userinfo.sales_token")
         ];
         $result = curl($url,null,$header);
         if ($result["error_code"] == 0) {
             return $result["data"];
         }
         return [];
    }

    public function getDropDowCity()
    {
        $url = $this->url."/pnp/v1/getpnpdropdowncity";
        $header = [
            "token:".session("uc_userinfo.sales_token")
        ];
        $result = curl($url,null,$header);
        $result = curl($url,null,$header);
        if ($result["error_code"] == 0) {
            return $result["data"];
        }
        return [];
    }

    public function getHistoryList($tel,$order_id,$page)
    {
        $url = $this->url."/pnp/v1/gettelhistory";

        $data = [
            "tel" => $tel,
            "order_id" => $order_id,
            "page" => $page,
        ];

        $url = $url."?".http_build_query($data);

        $header = [
            "token:".session("uc_userinfo.sales_token")
        ];
        $result = curl($url,null,$header);
        if ($result["error_code"] == 0) {
            return $result["data"];
        }
        return [];
    }

    public function getVoliceList($order_id)
    {
        $url = $this->url."/pnp/v1/getvoicelist";
        $data = [
            "order_id" => $order_id,
        ];
        $url = $url."?".http_build_query($data);
        $header = [
            "token:".session("uc_userinfo.sales_token")
        ];
        $result = curl($url,null,$header);
        if ($result["error_code"] == 0) {
            return $result["data"];
        }
        return [];
    }
}