<?php

namespace Home\Model;

use Think\Model;

class UserShareRecordsModel extends Model{

    /**
     * 获取每日统计
     * @return [type] [description]
     */
    public function getDayCount($activity_id, $start = 0, $end = 0){
        $map = array(
            "activity_id" => array("EQ", $activity_id)
        );

        if (!empty($start) && !empty($end)) {
            $map["created_at"] = array("BETWEEN", [$start, $end]);
        } else if (!empty($start)) {
            $map["created_at"] = array("EGT", $start);
        } else if (!empty($end)) {
            $map["created_at"] = array("ELT", $end);
        }

        $field = ['count(id) share_count,FROM_UNIXTIME(created_at, "%Y-%m-%d") date'];
        return M("user_share_records")->where($map)->field($field)->group("date")->select();
    }

}