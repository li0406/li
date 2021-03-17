<?php

namespace Common\Model\Db;

use Think\Model;

class TuCategoryModel extends Model
{
    protected $tableName = 'tu_category';


    /**
     * 根据条件获取二级分类
     * @param $map
     * @return mixed
     */
    public function getMeituLv2Category($map , $field = 'id,name,url' , $sort = 'sort desc,created_at desc,id desc',$limit = '')
    {
        if(!$map['is_enable']){
            $map['is_enable'] = array('EQ',1);
        }
        return $this->where($map)->field($field)->order($sort)->limit($limit)->select();
    }



}
