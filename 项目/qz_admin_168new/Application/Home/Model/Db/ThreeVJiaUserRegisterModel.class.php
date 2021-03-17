<?php

namespace Home\Model\Db;
use Think\Model;
class ThreeVJiaUserRegisterModel extends Model
{
    protected $tableName = 'threevjia_user_register';

    public function getRegisterListCount()
    {
        return $this->count();
    }

    public function getRegisterList($pageIndex,$pageCount)
    {
        return $this->alias("a")
            ->join("join qz_quyu q on q.cid = a.city_id")
            ->join("join qz_province p on p.qz_provinceid = a.province_id")
            ->order("time desc")
            ->field("a.*,q.cname,p.qz_province")
            ->limit($pageIndex.",".$pageCount)->select();
    }
}