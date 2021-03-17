<?php

namespace Home\Model\Db;

use Think\Model;

class ScoreSignRuleListModel extends Model
{
    public function getMap($cond)
    {
        $map = [];
        if ($cond['rule_id']) {
            $map['rule_id'] = ['eq', $cond['rule_id']];
        }
        return $map;
    }

    public function getList($cond)
    {
        $map = $this->getMap($cond);
        return $this->where($map)->select();
    }

    public function drop($cond)
    {
        $map = $this->getMap($cond);
        return $this->where($map)->delete();
    }
}