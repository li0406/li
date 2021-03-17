<?php

namespace app\index\model\db;
use think\Model;

class User extends Model
{
    protected $tableName = 'qz_ucenter_center';
    public function getUserInfo($map,$field = 'a.uuid,a.status,a.uuid'){
        return $this->alias('a')
            ->field($field)
            ->join('qz_ucenter_oauth b','b.uuid = a.uuid')
            ->where($map)
            ->select();
    }
}