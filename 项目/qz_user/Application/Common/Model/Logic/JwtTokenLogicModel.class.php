<?php

namespace Common\Model\Logic;

use Library\Qizuang\JWTAuth\Src\Facade\Auth;

class JwtTokenLogicModel {

    const USER_TOKEN_TYPE = "qz_user";

    /**
     * 下发去商户中心的token
     * @return [type] [description]
     */
    public static function approvalToken(){
        $user = session("u_userInfo");

        $token_data = array(
            "user_id" => $user["id"],
        );

        // 下发Token
        $token_data["token_type"] = static::USER_TOKEN_TYPE;
        return Auth::getToken($token_data);
    }


    public static function approvalSubAccountToken($user_id)
    {
        $token_data = array(
            "user_id" => $user_id,
            "employee" => 1 //员工标识
        );

        // 下发Token
        $token_data["token_type"] = static::USER_TOKEN_TYPE;
        return Auth::getToken($token_data);
    }
}