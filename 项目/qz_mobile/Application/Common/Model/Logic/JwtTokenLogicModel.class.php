<?php

namespace Common\Model\Logic;

use Library\Qizuang\JWTAuth\Src\Facade\Auth;

class JwtTokenLogicModel {

    const USER_TOKEN_TYPE = "qz_mobile";

    /**
     * 下发去商户中心的token
     * @return [type] [description]
     */
    public static function approvalToken(){

        $token_data = array(

        );

        // 下发Token
        $token_data["token_type"] = static::USER_TOKEN_TYPE;
        return Auth::getToken($token_data);
    }

}