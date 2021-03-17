<?php

namespace Common\Model\Db;

use Think\Model;

class TuHomeModel extends Model
{
    protected $tableName = 'tu_home';

    /**
     * 获取公装首页数据
     * @param $type
     * @param int $count
     * @return mixed
     */
    public function getPub($count = 8)
    {
        $map = [];
        $map['a.type'] = array('eq',1);
        $ret = $this->alias('a')
            ->where($map)
            ->join("qz_tu tu on tu.id=a.link_id and tu.state=3 and tu.type=1")
            ->join('qz_tu_category c on c.id=tu.pub_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.link_id')
            ->field('tu.id,c.name,tu.title,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
            ->order('tu.publish_time desc,tu.id desc')
            ->limit(0, $count)
            ->select();
        return $ret;
    }

    /**
     * 获取家装首页数据
     * @param $type
     * @param int $count
     * @return mixed
     */
    public function getHome($count = 8)
    {
        $map = [];
        $map['a.type'] = array('eq',2);
        $ret = $this->alias('a')
            ->where($map)
            ->join("qz_tu tu on tu.id=a.link_id and tu.state=3 and tu.type=2")
            ->join('qz_tu_category c1 on c1.id=tu.home_space_category')
            ->join('qz_tu_category c2 on c2.id=tu.home_part_category')
            ->join('qz_tu_category c3 on c3.id=tu.home_style_category')
            ->join('qz_tu_category c4 on c4.id=tu.home_layout_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.link_id')
            ->field('tu.id,c1.name space_name,c2.name part_name,c3.name style_name,c4.name layout_name,tu.title,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
            ->order('tu.publish_time desc,tu.id desc')
            ->limit(0, $count)
            ->select();
        return $ret;
    }

    public function getRecommentList()
    {
        $map = array(
            "type" => array("EQ",4)
        );
        return $this->where($map)->select();
    }

    /**
     * 获取家装的附属信息
     * @param $map
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getHomeAttribute($id,$field = "r.*")
    {
        $map = [
            't.id'=>['in', $id],
        ];
        return M('tu')->alias('t')
            ->join('qz_tu_category c1 on c1.id = t.home_space_category')
            ->join('qz_tu_category c2 on c2.id = t.home_part_category')
            ->join('qz_tu_category c3 on c3.id = t.home_style_category')
            ->join('qz_tu_category c4 on c4.id = t.home_layout_category')
            ->where($map)
            ->field($field)
            ->order('t.id desc')
            ->select();
    }
}
