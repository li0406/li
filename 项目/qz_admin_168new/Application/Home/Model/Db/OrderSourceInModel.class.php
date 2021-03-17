<?php

namespace Home\Model\Db;

use Think\Model;

class  OrderSourceInModel extends Model
{
    public function getSourceInCount()
    {
        return $this->field('id')->count();
    }

    public function getSourceInList($page = 0, $offset = 0)
    {
        return $this->limit($page, $offset)->order('is_use,id desc')->select();
    }

    public function getSourceInInfo($where)
    {
        return $this->where($where)->find();
    }

    public function addSourceIn($save)
    {
        return $this->add($save);
    }

    public function editSourceIn($id, $save)
    {
        return $this->where(['id' => ['eq', $id]])->save($save);
    }

    public function getUseSourceInList()
    {
        $where = [
            'is_use' => ['eq', 1]
        ];
        return $this->where($where)->select();
    }

    public function getOrderSourceInRelationCount($source_in)
    {
        $where = [
            'r.source_in' => ['eq', $source_in],
            's.visible' => ['eq', 0],
        ];
        return M('order_source_in_relation')->alias('r')
            ->field('r.id')
            ->where($where)
            ->join('qz_order_source s on s.src = r.src')
            ->count();
    }
}