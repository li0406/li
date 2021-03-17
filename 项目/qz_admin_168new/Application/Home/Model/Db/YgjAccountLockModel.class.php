<?php

namespace Home\Model\Db;
use Think\Model;

class YgjAccountLockModel extends Model
{
    public function updateAccount($user_id,$data)
    {
        $map = array(
            "userid" => array("EQ",$user_id)
        );
        return $this->where($map)->save($data);
    }
}