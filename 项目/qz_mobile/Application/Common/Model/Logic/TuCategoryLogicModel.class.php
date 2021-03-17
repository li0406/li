<?php

namespace Common\Model\Logic;

use Common\Model\Db\TuCategoryModel;

class TuCategoryLogicModel
{
    protected $tuCategoryModel;

    public $category_pub = 1;
    public $category_home = 2;

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
    public function getHomeCategoryListByType($type = 1, $limit = 0)
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
     * 获取家装/公装的二级分类以及下面的三级分类   （详情页标签使用）
     * @param int $type
     * @return array|mixed
     */
    public function getTuCategoryByType($type = 1)
    {
        $map['parent'] = array('eq', $type);
        $lv2list = $this->tuCategoryModel->getMeituLv2Category($map);
        $lv2list = array_column($lv2list, null, 'id');
        $parent_id = array_column($lv2list, 'id');
        $data = [];
        if (count($parent_id) > 0) {
            $map = [];
            $map['parent'] = array('in', $parent_id);
            $map['is_enable'] = array('eq', 1);
            $child = $this->tuCategoryModel->getMeituLv2Category($map, "id,url,name,parent", 'sort desc,created_at desc,id desc');
            foreach ($child as $k => $v) {
                if (isset($lv2list[$v['parent']])) {
                    $data[$lv2list[$v['parent']]['id']]['id'] = $lv2list[$v['parent']]['id'];
                    $data[$lv2list[$v['parent']]['id']]['name'] = $lv2list[$v['parent']]['name'];
                    $data[$lv2list[$v['parent']]['id']]['url'] = $lv2list[$v['parent']]['url'];
                    $data[$lv2list[$v['parent']]['id']]['child'][] = $v;
                }
            }
        }
        return $data;
    }

    public function neatenMobileTuCategory($category_type,$category)
    {
        foreach ($category as $k=>$v){
            foreach ($v['child'] as $kk=>$vv){
                //添加跳转链接
                if($category_type['type'] == 1){
                    $url = 'gz'.$vv['url'];
                }else{
                    $url = $v['url'].$vv['url'];
                }
                //选择效果
                $active = '';
                if($url == $category_type['original']){
                    $active = 'red';
                }
                $category[$k]['child'][$kk]['url'] = $url;
                $category[$k]['child'][$kk]['active'] = $active;
            }
        }
        return $category;
    }
}
