<?php

namespace Common\Model\Logic;

use Common\Model\Db\UcenterUserModel;

class UcenterUserLogicModel
{
    public function setUserInfo($uuid){
        $user = (new UcenterUserModel())->getUserInfo($uuid);
        $_SESSION["u_userInfo"] = array(
            "id" => $user["uuid"],
            "name" => $user["nickname"],
            "user" => $user["nickname"],
            "cs" => $user["city"],
            "qx" => $user["area"],
            "logo" => $this->changeImgUrl($user["avatar"]),
            "classid" => 1,
            "bm" => $user["bm"],
            "cname" => $user["cname"],
            "jc" => $user["nickname"],
            "on" => 0,
            'blocked' => ''
        );
    }

    function changeImgUrl($url)
    {
        if(!empty($url)){
            $preg = "/^http(s)?:\\/\\/.+/";
            if (preg_match($preg, $url)) {
                return $url;
            } else {
                return C('ZXS_QINIU_DOMAIN').'/'.$url;
            }
        }
    }
}
