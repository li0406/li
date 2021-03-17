<?php

namespace Common\Model\Db;

use Think\Model;

class TuModel extends Model
{
    protected $tableName = 'tu';

    /**
     * 获取条件
     * @param array $params
     * @return array
     */
    public function getMap(array $params)
    {
        $map = [];
        $map['a.type'] = $params['type'] ?: 1;    //默认公装
        $map['a.state'] = 3;

        if (!empty($params['ids'])) {
            $map['a.id'] = ['IN', $params['ids']];
        }

        if (!empty($params['keyword'] && !is_int($params['keyword']))) {
            $map['a.title'] = ['like', "%{$params['keyword']}%"];
        }

        //公装分类
        if (!empty($params['pub_category'])) {
            $map['pub_category'] = (int)$params['pub_category'];
        }

        //家装分类
        if (!empty($params['home_space_category'])) {
            $map['home_space_category'] = (int)$params['home_space_category'];
        }

        if (!empty($params['home_part_category'])) {
            $map['home_part_category'] = (int)$params['home_part_category'];
        }

        if (!empty($params['home_style_category'])) {
            $map['home_style_category'] = (int)$params['home_style_category'];
        }

        if (!empty($params['home_layout_category'])) {
            $map['home_layout_category'] = (int)$params['home_layout_category'];
        }
        return $map;
    }

    /**
     * 获取公装数量
     * @param $params
     * @return mixed
     */
    public function getPubCount($params)
    {
        $map = $this->getMap($params);
        $buildSql = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.id')
//            ->group('a.id')
            ->buildSql();
        $count = $this->table($buildSql)->alias('t')->count();
        return $count;
    }

    /**
     * 获取公装列表
     * @param $params
     * @param $page
     * @param $perCount
     * @return mixed
     */
    public function getPubList($params, $page, $perCount,$field = 'a.*,c.name,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
    {
        $map = $this->getMap($params);
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field($field)
            ->order('a.publish_time desc,a.id desc')
//            ->group('a.id')
            ->limit($page, $perCount)
            ->select();
        return $ret;
    }

    /**
     * 获取公装详情
     * @param $id
     * @return mixed
     */
    public function getPubDetail($id)
    {
        $map['a.id'] = $id;
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,c.name,c.is_enable pubis_enable,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
            ->find();
        return $ret;
    }

    /**
     * 获取家装数量
     * @param $params
     * @return mixed
     */
    public function getHomeCount($params)
    {
        $map = $this->getMap($params);
        $count = $this->alias('a')->where($map)
            // ->join('qz_tu_category c1 on c1.id=a.home_space_category')
            // ->join('qz_tu_category c2 on c2.id=a.home_part_category')
            // ->join('qz_tu_category c3 on c3.id=a.home_style_category')
            // ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
            // ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.id')
//            ->group('a.id')
            ->count();
//        $count = $this->table($buildSql)->alias('t')->count();
        return $count;
    }

    /**
     * 获取家装列表
     * @param $params
     * @param $page
     * @param $perCount
     * @return mixed
     */
    public function getHomeList($params, $page, $perCount, $field = false)
    {
        if ($field == false) {
            $field = 'a.*,c1.`name` space_name,c2.`name` part_name,c3.`name` style_name,c4.`name` layout_name,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height';
        }

        $map = $this->getMap($params);
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c1 on c1.id=a.home_space_category')
            ->join('qz_tu_category c2 on c2.id=a.home_part_category')
            ->join('qz_tu_category c3 on c3.id=a.home_style_category')
            ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field($field)
            ->order('a.publish_time desc,a.id desc')
//            ->group('a.id')
            ->limit($page, $perCount)
            ->select();
        return $ret;
    }

    /**
     * 获取家装详情
     * @param $id
     * @return mixed
     */
    public function getHomeDetail($id)
    {
        $map['a.id'] = $id;
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c1 on c1.id=a.home_space_category')
            ->join('qz_tu_category c2 on c2.id=a.home_part_category')
            ->join('qz_tu_category c3 on c3.id=a.home_style_category')
            ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,c1.url kj_url,c1.is_enable c1is_enable,c2.url jb_url,c2.is_enable c2is_enable,c3.url fg_url,c3.is_enable c3is_enable,c4.url hx_url,c4.is_enable c4is_enable,c1.name space_name,c2.name part_name,c3.name style_name,c4.name layout_name,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
            ->find();
        return $ret;
    }

    //查询单个图片前后图集
    public function getSingleCases($id,$type,$preNum,$meitu_type,$uid='')
    {
        $map = [];
        $map['tu.type'] = array('eq',$meitu_type);
        $map['tu.state'] = array('eq',3);
        if($meitu_type == 1){   //公装
            if($type == 'pre'){     //查询前面的单图（发布时间越来越大）
                $map['tu.id'] = array('lt',$id);
                $sort = 'tu.id desc';
            }else{  //查询后面的单图（发布时间越来越小）
                $map['tu.id'] = array('gt',$id);
                $sort = 'tu.id asc';
            }
            return $this->alias('tu')
                ->field('tu.id,tu.title,tu.type,tu.publish_time time,img.src img_path,img.title imgdescription,img.width image_width,img.height image_height,c.name pubcategory_name')
                ->where($map)
                ->join('qz_tu_image img on img.meitu_id = tu.id')
                ->join('qz_tu_category c on c.id = tu.pub_category')
                ->limit($preNum)
                ->order($sort)
                ->select();

        }else{

            if($type == 'pre'){     //查询前面的单图（发布时间越来越大）
                $map['tu.id'] = array('lt',$id);
                $sort = 'tu.id desc';
            }else{  //查询后面的单图（发布时间越来越小）
                $map['tu.id'] = array('gt',$id);
                $sort = 'tu.id asc';
            }
            return $this->alias('tu')
                ->field('tu.id,tu.title,tu.type,tu.publish_time time,img.src img_path,img.title imgdescription,img.width image_width,img.height image_height,c1.name space_name,c2.name style_name,c3.name part_name,c4.name layout_name')
                ->where($map)
                ->join('qz_tu_image img on img.meitu_id = tu.id')
                ->join('qz_tu_category c1 on c1.id = tu.home_space_category')
                ->join('qz_tu_category c2 on c2.id = tu.home_style_category')
                ->join('qz_tu_category c3 on c3.id = tu.home_part_category')
                ->join('qz_tu_category c4 on c4.id = tu.home_layout_category')
                ->limit($preNum)
                ->order($sort)
                ->select();

        }

    }

    /**
     * 获取上下图
     * @param $id [当前图片id]
     * @param $type [1.上一条 2.下一条]
     * @param $meitu_type
     * @return mixed
     */
    public function getTuSingleCases($id,$type,$meitu_type){
        $map = [];
        $map['tu.type'] = array('eq',$meitu_type);
        $map['tu.state'] = array('eq',3);
        switch ($meitu_type){
            case 1:
                //公装
                $map['tu.type'] = array('eq',1);
                break;
            case 2:
                //家装
                $map['tu.type'] = array('eq',2);
                break;
        }
        $sort = 'tu.id desc';
        switch ($type){
            case 1:
                $map['tu.id'] = array('gt',$id);
                $sort = 'tu.id asc';
                break;
            case 2:
                $map['tu.id'] = array('lt',$id);
                $sort = 'tu.id desc';
                break;
        }
        return $this->alias('tu')
            ->field('tu.id,tu.title,tu.type,tu.publish_time time,img.src img_path,img.title img_title')
            ->join('qz_tu_image img on img.meitu_id = tu.id')
            ->where($map)
            ->order($sort)
            ->find();
    }
    /**
     * 根据美图id获取美图表中的基本信息
     * @param $id
     * @return mixed
     */
    public function getMeituBaseInfoById($id)
    {
        $map = [];
        $map['id'] = array('eq',$id);
        return $this->where($map)->find();
    }

    /**
     * 根据公装类型id获取相关美图
     * @param $categoryid      工装类型id
     * @param int $limit       获取数量， 默认为6
     * @return mixed
     */
    public function getPubRelevantMeitu($categoryid,$id,$limit = 6)
    {
        $map['tu.pub_category'] = array('eq',$categoryid);
        $map['tu.id'] = array('neq',$id);
        $map['tu.state'] = array('eq',3);
        $map['tu.type'] = array('eq',1);
        $map['c1.is_enable'] = array('eq',1);
        return $this->alias('tu')->where($map)
            ->join('qz_tu_category c1 on c1.id = tu.pub_category')
            ->join('qz_tu_image img on img.meitu_id = tu.id')
            ->field('tu.id,tu.type,tu.title,tu.publish_time,img.src image_src,img.title image_title,img.width img_width,img.height img_height')
            ->order('tu.publish_time desc,tu.id desc')
            ->limit($limit)
            ->select();
    }


    /**
     * 家装获取四个分类都一样的数据
     * @param $map
     * @param int $limit
     * @return mixed
     */
    public function getMeituSameMeitu($map,$limit = 6)
    {
        return $this->alias('tu')->where($map)
            ->join('qz_tu_category c1 on c1.id = tu.home_space_category')
            ->join('qz_tu_category c2 on c2.id = tu.home_part_category')
            ->join('qz_tu_category c3 on c3.id = tu.home_style_category')
            ->join('qz_tu_category c4 on c4.id = tu.home_layout_category')
            ->join('qz_tu_image img on img.meitu_id = tu.id')
            ->field('tu.id,tu.type,tu.title,tu.publish_time,img.src image_src,img.title image_title,img.width img_width,img.height img_height')
            ->order('tu.publish_time desc,tu.id desc')
            ->limit($limit)
            ->select();
    }


    /**
     * 获取家装美图的分类信息
     * @param $id
     * @return mixed
     */
    public function getMeituCategoryAndCategoryState($id)
    {
        $map = [];
        $map['tu.id'] = array('eq',$id);
        return $this->alias('tu')->where($map)
            ->join('qz_tu_category c1 on c1.id = tu.home_space_category')
            ->join('qz_tu_category c2 on c2.id = tu.home_part_category')
            ->join('qz_tu_category c3 on c3.id = tu.home_style_category')
            ->join('qz_tu_category c4 on c4.id = tu.home_layout_category')
            ->field('tu.id,c1.id c1_id,c1.is_enable c1is_enable,c1.name kj_name,c1.url kj_url,c2.id c2_id,c2.is_enable c2is_enable,c2.name jb_name,c2.url jb_url,c3.id c3_id,c3.is_enable c3is_enable,c3.name fg_name,c3.url fg_url,c4.id c4_id,c4.is_enable c4is_enable,c4.name hx_name,c4.url hx_url')
            ->find();
    }


    /**
     * 获取不全数据
     * @param $map      条件
     * @param $category_type    获取哪一个分类相关
     * @param int $limit
     * @return array
     */
    public function getNewMeituRelevantMeitu($map,$category_type,$limit = 0)
    {
        if($category_type == 1){
            $join = 'qz_tu_category c1 on c1.id = tu.home_space_category';
        }elseif($category_type == 2){
            $join = 'qz_tu_category c1 on c1.id = tu.home_part_category';
        }elseif($category_type == 3){
            $join = 'qz_tu_category c1 on c1.id = tu.home_style_category';
        }elseif($category_type == 4){
            $join = 'qz_tu_category c1 on c1.id = tu.home_layout_category';
        }else{
            return array();
        }
        return $this->alias('tu')->where($map)
            ->join($join)
            ->join('qz_tu_image img on img.meitu_id = tu.id')
            ->field('tu.id,tu.type,tu.title,tu.publish_time,img.src image_src,img.title image_title,img.width img_width,img.height img_height')
            ->order('tu.publish_time desc,tu.id desc')
            ->limit($limit)
            ->select();

    }


    /**
     * 获取公装美图的分类信息
     * @param $id
     * @return mixed
     */
    public function getPubMeituCategoryAndCategoryState($id)
    {
        $map = [];
        $map['tu.id'] = array('eq',$id);
        return $this->alias('tu')->where($map)
            ->join('qz_tu_category c1 on c1.id = tu.pub_category')
            ->field('tu.id,c1.id c1_id,c1.is_enable c1is_enable,c1.name gz_name,c1.url gz_url')
            ->find();
    }


    /**
     * PV值+1
     * @param string $value [description]
     */
    public function meituAddPv($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return $this->where($map)->setInc("pv",1);
    }


    public function getHomeListByIds($ids){
        $map = array(
            "tu.id" => array("IN", $ids),
            "tu.type" => array("EQ", 2)
        );

        return $this->alias("tu")->where($map)
            ->join("left join qz_tu_category c1 on c1.id = tu.home_space_category")
            ->join("left join qz_tu_category c2 on c2.id = tu.home_part_category")
            ->join("left join qz_tu_category c3 on c3.id = tu.home_style_category")
            ->join("left join qz_tu_category c4 on c4.id = tu.home_layout_category")
            ->field([
                "tu.*",
                "c1.`name`as space_name",
                "c2.`name`as part_name",
                "c3.`name`as style_name",
                "c4.`name`as layout_name",
            ])->select();
    }

    public function getPubListByIds($ids){
        $map = array(
            "tu.id" => array("IN", $ids),
            "tu.type" => array("EQ", 1)
        );

        return $this->alias("tu")->where($map)
            ->join('qz_tu_category c1 on c1.id = tu.pub_category')
            ->field([
                "tu.*",
                "c1.`name`as pub_name",
            ])->select();
    }

    public function setLikeInc($id, $num = 1){
        $map = array(
            "id" => array("EQ", $id)
        );
        return $this->where($map)->setInc("likes", $num);
    }

    // 获取家装美图推荐
    public function getTuHomeRecommendList($limit = 20, $offset = 0){
        $map = array(
            "a.type" => array("EQ", 2),
            "a.state" => array("EQ", 3),
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_tu_image as b on b.meitu_id = a.id")
            ->join("inner join qz_tu_category as space on space.id = a.home_space_category")
            ->join("inner join qz_tu_category as part on part.id = a.home_part_category")
            ->join("inner join qz_tu_category as style on style.id = a.home_style_category")
            ->join("inner join qz_tu_category as layout on layout.id = a.home_layout_category")
            ->field([
                "a.id", "a.title", "b.src as image_src",
                "a.home_space_category", "space.`name` as space_name",
                "a.home_part_category", "part.`name` as part_name",
                "a.home_style_category", "style.`name` as style_name",
                "a.home_layout_category", "layout.`name` as layout_name"
            ])
            ->order("a.created_at desc")
            ->limit($offset, $limit)
            ->select();
    }
}
