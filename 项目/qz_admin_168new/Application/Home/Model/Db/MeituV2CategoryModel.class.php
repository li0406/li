<?php


namespace Home\Model\Db;


use Think\Model;

class MeituV2CategoryModel extends Model
{
    protected $tableName = "tu_category";

    /**
     * 根据条件获取二级分类
     * @param $map
     * @return mixed
     */
    public function getMeituLv2Category($map , $field = 'id,name' , $sort = 'sort desc,created_at desc,id desc')
    {
        return $this->where($map)->field($field)->order($sort)->select();
    }

    /**
     * 获取三级分类数量
     * @param $map
     * @return mixed
     */
    public function getMeituCategoryListCount($map)
    {
        return $this->alias('lv3')->where($map)->count();
    }

    /**
     * @param $map
     * @param int $pageIndex
     * @param int $pageCount
     * @return mixed
     */
    public function getMeituCategoryList($map,$pageIndex = 0,$pageCount = 10)
    {
        return $this->alias('lv3')
            ->where($map)
            ->field('lv3.id,lv3.name,lv3.sort,lv3.is_recommend,lv3.is_enable,lv3.created_at,lv2.name parent_name')
            ->join('qz_tu_category lv2 on lv2.id = lv3.parent')
            ->order('lv3.created_at desc,lv3.id desc')
            ->limit($pageIndex,$pageCount)
            ->select();

    }

    /**
     * 根据id获取分类信息
     * @param $id
     * @return mixed
     */
    public function getCategoryInfoById($id)
    {
        $map = [];
        $map['id'] = $id;
        return $this->where($map)->find();
    }

    /**
     * 添加分类
     * @param $data
     */
    public function addCategory($data)
    {
        return $this->add($data);
    }

    /**
     * 编辑分类
     * @param $id
     * @param $data
     */
    public function editCategory($id,$data)
    {
        $map = [];
        $map['id'] = $id;
        return $this->where($map)->save($data);
    }

    /**
     * 删除分类
     * @param $id
     */
    public function deleteCategory($id){
        $map = [];
        $map['id'] = $id;
        return $this->where($map)->delete();
    }



}