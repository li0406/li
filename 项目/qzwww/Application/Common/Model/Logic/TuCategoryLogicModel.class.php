<?php

namespace Common\Model\Logic;

use Common\Model\Db\TuCategoryModel;

class TuCategoryLogicModel
{
    protected $tuCategoryModel;

    public function __construct()
    {
        $this->tuCategoryModel = new TuCategoryModel();
    }

    /**
     * 获取值
     * @param $id
     * @param $field
     * @return \Think\Model
     */
    public function value($id, $field)
    {
        $map['id'] = $id;
        $map['is_enable'] = 1;
        $value = $this->tuCategoryModel->where($map)->getField($field);
        return $value;
    }

    /**
     * 获取值根据url
     * @param $id
     * @param $field
     * @return \Think\Model
     */
    public function valueByURL($url, $field)
    {
        $map['url'] = $url;
        $map['is_enable'] = 1;
        $value = $this->tuCategoryModel->where($map)->getField($field);
        return $value;
    }

    /**
     * 查找某一行数据
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $map['id'] = $id;
        $map['is_enable'] = 1;
        $data = $this->tuCategoryModel->where($map)->order('sort desc')->find();
        return $data;
    }

    /**
     * 根据url查找行数据
     * @param $url
     * @param $field
     * @return mixed
     */
    public function findByURL($url,$field)
    {
        $map['url'] = $url;
        $map['is_enable'] = 1;
        $data = $this->tuCategoryModel->field($field)->where($map)->order('sort desc')->find();
        return $data;
    }

    /**
     * 获取首页分类
     * @param int $type
     */
    public function getHomeCategoryListByType($type = 1)
    {
        $map = [];
        $map['parent'] = array('eq', $type);
        $lv2list =  $this->tuCategoryModel->getMeituLv2Category($map);
        if($type == 2){
            foreach ($lv2list as $key => $value){
                $map = [];
                $map['parent'] = array('EQ',$value['id']);
                $map['is_enable'] = array('eq', 1);
                $map['is_recommend'] = array('eq', 1);
                $limit = '0,16';
                $lv2list[$key]['child'] = $this->tuCategoryModel->getMeituLv2Category($map,"id,url,name",'sort desc,created_at desc,id desc',$limit);
            }
        }else{
            $parentlist = [];
            foreach ($lv2list as $key => $value){
                $parentlist[] = intval($value['id']);
            }
            $map = [];
            $map['parent'] = array('in',$parentlist);
            $map['is_enable'] = array('eq', 1);
            $map['is_recommend'] = array('eq', 1);
            $limit = '0,16';
            $lv2list = $this->tuCategoryModel->getMeituLv2Category($map,"id,url,name",'sort desc,created_at desc,id desc',$limit);
        }

        return $lv2list ? $lv2list : array();

    }


    /**
     * 获取家装/公装的二级分类以及下面的三级分类   （详情页标签使用）
     * @param int $type
     * @return array|mixed
     */
    public function getMeituLv2CategoryByType($type = 1)
    {
        $map = [];
        $map['parent'] = array('eq', $type);
        $lv2list =  $this->tuCategoryModel->getMeituLv2Category($map);
        foreach ($lv2list as $key => $value){
            $map = [];
            $map['parent'] = array('EQ',$value['id']);
            $map['is_enable'] = array('eq', 1);
            // $map['is_recommend'] = array('eq', 1);
            $child = $this->tuCategoryModel->getMeituLv2Category($map,"id,url,name",'sort desc,created_at desc,id desc');
            $lv2list[$key]['child_count'] = count($child);
            $lv2list[$key]['child'] = $child;
        }
        return $lv2list ? $lv2list : array();

    }

    /**
     * 获取家装美图的一个二级分类以及下面的三级分类 （按顺序，空间、风格、户型、局部 顺序  7，9，8，10）
     */
    public function getMeituOneLv2Category(){
        $map = [];
        $map['parent'] = array('eq', 2);
        $lv2list =  $this->tuCategoryModel->getMeituLv2Category($map);
        $returnlist = [];
        foreach ($lv2list as $key => $value){
            $map = [];
            $map['parent'] = array('EQ',$value['id']);
            $map['is_enable'] = array('eq', 1);
            $lv2list[$key]['child'] = $this->tuCategoryModel->getMeituLv2Category($map,"id,url,name",'sort desc,created_at desc,id desc');
            if($value['id'] == 7 && count($lv2list[$key]['child']) > 0){
                $returnlist = $lv2list[$key];
                break;
            }elseif($value['id'] == 9 && count($lv2list[$key]['child']) > 0){
                $returnlist = $lv2list[$key];
                break;
            }elseif($value['id'] == 8 && count($lv2list[$key]['child']) > 0){
                $returnlist = $lv2list[$key];
                break;
            }elseif($value['id'] == 10 && count($lv2list[$key]['child']) > 0){
                $returnlist = $lv2list[$key];
                break;
            }
        }
        return $returnlist ? $returnlist : [];

    }

}
