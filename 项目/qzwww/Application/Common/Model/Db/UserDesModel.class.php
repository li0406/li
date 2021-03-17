<?php


namespace Common\Model\Db;


use Think\Model;

class UserDesModel extends Model
{
    protected $tableName = 'user_des';
    protected $autoCheckFields = false;

    // 新签返会员下的设计师增加设计师人气
    public function SetIncDesignerPv($designerId)
    {
        $map = [
            'userid' => array('eq', $designerId)
        ];
        return $this->where($map)
            ->setInc('popularity');
    }

}