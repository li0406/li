<?php

namespace Common\Model\Db;
use Think\Model;

class JiajuOrderBlacklistModel extends Model
{
    protected $autoCheckFields = false;

    public function getOrderTelBlackCount($tel)
    {
        $map = ["tel"=>["EQ",$tel]];
        return M("jiaju_order_blacklist")->where($map)->count();
    }
}
