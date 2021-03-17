<?php

namespace Home\Model\Db;

use Think\Model;

class LogClinkTelcenterModel extends Model {

    public function getRecodeInfo($call_sid)
    {
        $map = [
            "call_sid" => ["EQ",$call_sid]
        ];
        return $this->where($map)->find();
    }

    
}