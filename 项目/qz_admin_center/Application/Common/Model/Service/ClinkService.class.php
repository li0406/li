<?php

namespace Common\Model\Service;
/**
 *
 */
class ClinkService
{
    protected $accessKeyId = "";//客服云平台颁发给客户端的访问密钥 ID
    protected $expires = 60 * 3; //签名的有效时间（秒）最大值为 86400（24 小时）
    protected $timestamp = "";

    public function  __construct()
    {
        if (C("APP_ENV") != "prod") {
            $this->accessKeyId = "7u6v68233A74D38H5kxq";
        }
    }

    private  function getSignature($data){
        $data["AccessKeyId"] = $this->accessKeyId;
        $data["Expires"] = $this->expires;
        $data["Timestamp"] =  date("Y-m-dTH:i:sZ");
        //正向排序
        ksort($data,SORT_STRING);
        $param =  http_build_query($data);
        return hmac_sha1($param,$this->accessKeyId);
    }
}