<?php

namespace Home\Model;

Use Think\Model;

class HzsLoggingModel extends Model
{
    /**
     * 添加日志
     */
    public function addLog($data){
        return M("hzs_logging")->add($data);
    }
}
