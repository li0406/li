<?php
/**
 * 用户中心-产品线平台
 */

namespace Home\Model\Db;


use Think\Model;

class UcenterPlatformModel extends Model
{
    protected $tableName = "ucenter_platform";

    public function getList()
    {
        $map = [];
        $map['status'] = array('eq',1);
        return $this->select();
    }
}