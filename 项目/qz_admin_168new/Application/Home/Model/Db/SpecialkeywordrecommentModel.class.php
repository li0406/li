<?php

namespace Home\Model\Db;

class SpecialkeywordrecommentModel
{
    protected $autoCheckFields = false;

    //添加
    public function adddata($data)
    {
        return M('special_keyword_recommend')->add($data);
    }

    //删除
    public function deletedata($map)
    {
        return M('special_keyword_recommend')->where($map)->delete();

    }
}