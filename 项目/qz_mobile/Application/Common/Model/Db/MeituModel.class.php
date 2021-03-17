<?php

namespace Common\Model\Db;
use Think\Model;

class MeituModel extends Model
{
    protected $tableName = 'meitu';
    public function getMeituListByIds($ids){
        $map['id'] = ['in',$ids];
        //1.查询美图信息
        $buildSql = $this->field('id')->where($map)->buildSql();

        //查询美图的图片信息
        return  $this->table($buildSql)->alias("t1")
            ->join("qz_meitu_img b on b.caseid = t1.id")
            ->field("t1.*,b.img_path,b.img_host")
            ->order("b.img_on desc ,b.px")
            ->select();
    }
}
