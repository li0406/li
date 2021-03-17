<?php

namespace Home\Model;
use Think\Model;

/**
*   voip电话查询
*/
class VoipModel extends Model
{
    protected $tableName = 'admin_voip_tels'; //数据库名.表名(包含了前缀)

    public function getvoiplist()
    {
        return M("admin_voip_tels")->alias("a")
                                   ->field("a.id,a.use_id,a.use_name,a.use_time,a.time_add,a.voipAccount,a.use_on,a.solutions")
                                   ->order("a.use_time DESC")
                                   ->select();
    }
}
