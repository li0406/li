<?php

namespace Home\Model;
use Think\Model;

class OrdersExceptionStatisticsModel extends Model
{
    public function editException($date,$hour,$data)
    {
        $map = array(
            "day" => array("EQ",$date),
            "time" => array("EQ",$hour)
        );
        return M("orders_exception_statistics")->where($map)->save($data);
    }

    /**
     * 根据日期删除
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function delExceptionByDay($date)
    {
        $map = array(
            "day" => array("EQ",$date)
        );
        return M("orders_exception_statistics")->where($map)->delete();
    }

    /**
     * 获取每日预警数据
     * @param  [type] $day [description]
     * @return [type]      [description]
     */
    public function getExceptionStatisticsByDay($date)
    {
        $map = array(
            "day" => array("EQ",$date)
        );
        return M("orders_exception_statistics")->where($map)->select();
    }
}