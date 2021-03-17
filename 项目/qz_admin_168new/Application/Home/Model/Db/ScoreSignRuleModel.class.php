<?php

namespace Home\Model\Db;

use Think\Model;

class ScoreSignRuleModel extends Model
{
    public function getMap($cond)
    {
        $map = [];
        if ($cond['type']) {
            $map['type'] = ['eq', $cond['type']];
        }
        if ($cond['status']) {
            $map['status'] = ['eq', $cond['status']];
        }
        if ($cond['start_time']) {
            $map['start_time'] = ['eq', $cond['start_time']];
        }
        return $map;
    }

    /**
     * 是否有某个数据
     * @param $map
     * @return bool
     */
    public function has($map)
    {
        $count = (int)$this->where($map)->count();
        return !!$count;
    }

    /**
     * 获取数量
     * @param array $map
     * @return int
     */
    public function getCount(array $map)
    {
        $map = $this->getMap($map);
        $count = (int)$this->where($map)->count();
        return $count;
    }

    /**
     * 获取列表
     * @param array $map
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getList(array $map, $page, $limit)
    {
        $map = $this->getMap($map);
        $data = $this->where($map)->order('id desc')->limit($page, $limit)->select();
        return $data;
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function getDetail($id)
    {
        $map['id'] = ['eq', $id];
        $data = $this->where($map)->find();
        return $data;
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     */
    public function drop($id)
    {
        $map['id'] = ['eq', $id];
        $ret = $this->where($map)->delete();
        return $ret;
    }

    public function getEnableRule($map)
    {
//        $map = $this->getMap($map);
        $rule = $this->where($map)->order('id desc')->find();
        return $rule;
    }
}