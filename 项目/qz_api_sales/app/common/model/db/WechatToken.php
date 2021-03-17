<?php

namespace app\common\model\db;

use think\Model;

class WechatToken extends Model {

    public static function getByAppid($appid, $field = "*"){
        return static::where("appid", $appid)->field($field)->find();
    }

    public static function getToken($appid){
        return static::where("appid", $appid)
            ->where("expires_in", "GT", time())
            ->value("token");
    }

    /**
     * 保存token
     * @param $appid
     * @param $token
     * @param int $expires_in
     * @return bool
     */
    public static function setToken($appid, $token, $expires_in = 7200){
        $wechatToken = static::getByAppid($appid);
        if (empty($wechatToken)) {
            $wechatToken = new static();
            $wechatToken->appid = $appid;
            $wechatToken->info = "首次插入";
            $wechatToken->created_at = date("Y-m-d H:i:s");
        } else {
            $wechatToken->info = "正常更新";
        }

        $wechatToken->token = $token;
        $wechatToken->expires_in = $expires_in + time();
        $wechatToken->updated_at = date("Y-m-d H:i:s");
        return $wechatToken->save();
    }

}