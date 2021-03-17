<?php

namespace Home\Model\Db;


use Think\Model;

class ScoreLevelModel extends Model
{
    protected $tableName = "score_level";

    public function getLevelInfo(){
        return $this->field('level')->select();
    }

    /**
     * 根据积分获取会员等级
     * @param $growth   积分值
     * @return mixed
     */
    public function getlevelByGrowth($growth)
    {
        $map = [];
        $map['growth'] = array('elt',$growth);
        return $this->where($map)->field('max(level) as level')->find();
    }



}