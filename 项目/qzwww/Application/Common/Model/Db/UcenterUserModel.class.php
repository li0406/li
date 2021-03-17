<?php

namespace Common\Model\Db;

use Think\Model;

class UcenterUserModel extends Model
{
    protected $tableName = 'ucenter_user';

    /**
     * 获取条件
     * @param array $params
     * @return array
     */
    public function getUserInfo($uuid)
    {
        if(!empty($uuid)){
            $map['a.uuid'] = ["eq",$uuid];
            $buildSql = M("ucenter_user")->alias('a')
                ->join("qz_ucenter_profile as b on b.uuid = a.uuid")
                ->field("b.uuid,b.nickname,b.city,b.area,b.avatar")
                ->where($map)
                ->buildSql();
            $result = M("ucenter_user")->table($buildSql)->alias('t')
                ->field('t.*,c.cname,c.bm')
                ->join("LEFT JOIN qz_quyu as c on c.cid = t.city")
                ->select();
            if(!empty($result[0])){
                return  $result[0];
            }
        }
    }

   













}
