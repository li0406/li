<?php

namespace Common\Model\Db;

use Think\Model;

class ThreeDModel extends Model
{
    protected $tableName = 'xiaoguotu_threedimension';

    public function getHome($count = 5)
    {
        $map['status'] = 1;
        $ret = $this->alias('a')
            ->join('qz_xiaoguotu_threedimension_img ti on ti.xiaoguotu_threedimension_id=a.id')
            ->join('qz_xiaoguotu_threedimension_category tc2 on tc2.id=a.fengge and tc2.status=1')
            ->join('qz_xiaoguotu_threedimension_category tc3 on tc3.id=a.huxing and tc3.status=1')
            ->field('a.*,tc2.name fengge_name,tc3.name huxing_name')
            ->limit($count)
            ->order('create_time desc')
            ->select();
        return $ret;
    }

    //查询 3D美图 Item 列表
    public function getHomeThreeDItemList($where)
    {
        $where['h.type'] = 3;
        return M('tu_home')->alias('h')
            ->field("h.*,x.title,x.face")
            ->join('JOIN qz_xiaoguotu_threedimension AS x ON x.id = h.link_id')
            ->order('x.publish_time desc')
            ->where($where)
            ->limit(5)
            ->select();
    }
}
