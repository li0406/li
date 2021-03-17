<?php

namespace Common\Model\Db;

use Think\Model;

class DecorationMapModel extends Model
{
    public function getMapInfo($id)
    {
        return $this->where(['id' => ['eq', $id]])->find();
    }

    public function getMapCount($input)
    {
        $where = $this->getMap($input);
        return $this->field('id')->where($where)->count();
    }

    public function getMapList($input, $offset, $limit)
    {
        $where = $this->getMap($input);
        $buildSql = $this
            ->where($where)
            ->order('type,updated_at desc,id desc')
            ->limit($offset, $limit)
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->field('t.*,q.cname,q.bm')
            ->join('join qz_quyu q on q.cid = t.city')
            ->select();
    }

    public function getMap($input)
    {
        $where = [
            'lng' => ['neq', ''],
            'lat' => ['neq', ''],
        ];
        if (!empty($input['city'])) {
            $where['city'] = ['eq', $input['city']];
        }
        if (!empty($input['type'])) {
            $where['type'] = ['eq', $input['type']];
        }
        if (!empty($input['company'])) {
            $where['company_name'] = ['like', '%' . $input['company'] . '%'];
        }
        return $where;
    }

}