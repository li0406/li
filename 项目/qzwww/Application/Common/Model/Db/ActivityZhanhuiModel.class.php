<?php

namespace Common\Model\Db;
use Think\Model;

class ActivityZhanhuiModel extends Model
{
    public function findByTel($tel)
    {
        $map = [];
        $map['tel'] = ['EQ', $tel];
        return M("activity_zhanhui")->where($map)->select();
    }

    public function addInfo($data)
    {
       return M("activity_zhanhui")->add($data);
    }

}
