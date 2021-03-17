<?php

namespace Home\Model\Db;

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
        $buildSql = $this->field('id,type,company_name,company_id,city,address,lng,lat,updated_at,created_at')
            ->where($where)
            ->order('created_at desc,id desc')
            ->limit($offset, $limit)
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->field('t.*,q.cname,q.bm')
            ->join('join qz_quyu q on q.cid = t.city')
            ->order('t.created_at desc,t.id desc')
            ->select();
    }

    public function getDecorationMapByCompanyName($company)
    {
        if (count($company) == 0) {
            return [];
        }
        $where = [
            'company_name' => ['in', $company]
        ];
        return $this->field('company_name,company_id,city')->where($where)->select();
    }

    public function addDecorationMap($save)
    {
        return $this->addAll($save);
    }

    public function editDecorationMap($id, $save)
    {
        return $this->where(['id' => ['eq', $id]])->save($save);
    }

    public function delDecorationMap($id)
    {
        return $this->where(['id' => ['eq', $id]])->delete();
    }

    public function getMap($input)
    {
        $where = [];
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