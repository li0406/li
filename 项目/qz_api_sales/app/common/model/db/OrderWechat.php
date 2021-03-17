<?php

namespace app\common\model\db;

use think\Model;

class OrderWechat extends Model {

    protected $autoWriteTimestamp = false;

    /**
     * 根据微信openid获取装修公司
     * @param  [type] $wx_openids [description]
     * @return [type]             [description]
     */
    public function getComByOpenids($wx_openids){
        return static::alias("a")
            ->where("a.wx_openid", "in", $wx_openids)
            ->where("a.is_delete", 0)
            ->join("user u", "u.id = a.comid")
            ->field("u.id,u.jc,a.wx_openid")
            ->select();
    }
}