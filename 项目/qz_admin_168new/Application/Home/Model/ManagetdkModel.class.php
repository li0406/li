<?php

//tdk管理表

namespace Home\Model;

Use Think\Model;

class ManagetdkModel extends Model
{
    public function getInfoCount($map)
    {
        return M('managetdk')->alias("m")->where($map)->count();
    }

    public function getInfo($map, $start = 0, $end = 20)
    {
        return M('managetdk')->alias("m")
            ->field('m.*,q.cname,q.bm,u.user as add_user,a.user as last_user')
            ->join('left join qz_quyu as q on m.cs = q.cid')
            ->join('left join qz_adminuser as u on m.uid = u.id')
            ->join('left join qz_adminuser as a on m.last_uid = a.id')
            ->where($map)
            ->limit($start, $end)
            ->select();
    }

    public function del($id)
    {
        return M('managetdk')->where(['id' => $id])->delete();
    }

    public function selectOneTdk($id, $field = 'id,title,keywords,description')
    {
        return M('managetdk')->field($field)->where(['id' => $id])->find();
    }

    public function saveEditData($ids, $data)
    {
        return M('managetdk')->where(['id' => ['in', $ids]])->save($data);
    }

    public function saveAddData($data)
    {
        return M('managetdk')->add($data);
    }

    public function selectOneTdkByMap($map)
    {
        return M('managetdk')->where($map)->find();
    }
}

