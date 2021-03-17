<?php

namespace Common\Model\Db;
use Think\Model;

class QuanWuRecordModel extends Model
{
    protected $tableName = 'quanwu_record';

    public function addInfo($data)
    {
        return $this->add($data);
    }

    public function updateInfo($data,$tel){
        $map['tel'] = ['eq',$tel];
        return $this->where($map)->save($data);
    }

    public function recordCount($tel){
        $map['tel'] = ['eq',$tel];
        return $this->field('id')->where($map)->count();
    }
}