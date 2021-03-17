<?php

namespace Common\Model\Db;
use Think\Model;

class JiajuYyOrderInfoModel extends Model
{
    public function addInfo($data)
    {
        return M("jiaju_yy_order_info")->add($data);
    }

}
