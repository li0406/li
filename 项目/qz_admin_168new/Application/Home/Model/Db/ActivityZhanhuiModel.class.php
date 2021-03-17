<?php

namespace Home\Model\Db;
use Think\Model;
class ActivityZhanhuiModel extends Model
{
    public function getActivityList()
    {
        $orderBy = 'time DESC';
        return M("activity_zhanhui")->order($orderBy)->select();
    }
}