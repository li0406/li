<?php

//tdk管理表

namespace Common\Model;
use Think\Model;

class ManagetdkModel extends Model
{
    //tdk位置 1.pc端 2.移动端
    const LOCATION_PC = 1;
    const LOCATION_MOB = 2;
    public function getPcTdk($map)
    {
        $map['m.location'] = ['eq',self::LOCATION_PC];
        return M('managetdk')->alias("m")
            ->field('m.id,m.cs,m.title,m.keywords,m.description,q.bm')
            ->join('left join qz_quyu as q on m.cs = q.cid')
            ->join('left join qz_infotype as i on m.child_model = i.id')
            ->where($map)
            ->find();
    }

}

