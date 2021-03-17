<?php

namespace Home\Model;

use Think\Model;

/**
*   主站文章表
*/
class MeituDesignerModel extends Model
{
    /**
     * [addMeituDesigner 新增美图大师]
     * @param [type] $save [存储内容]
     */
    public function addMeituDesigner($save){
        if (empty($save)) {
            return false;
        }
        $result = M('meitu_designer')->add($save);
        return $result;
    }

    /**
     * [editMeituDesigner 编辑美图大师]
     * @param  [type] $id   [ID]
     * @param  [type] $save [存储内容]
     * @return [type]       [description]
     */
    public function editMeituDesigner($id, $save){
        if (empty($id)) {
            return false;
        }
        $map['id'] = $id;
        $result = M('meitu_designer')->where($map)->save($save);
        return $result;
    }


    /**
     * [getMeituDesignerById description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function getMeituDesignerById($id = 0){
        if (empty($id)) {
            return false;
        }
        $result = M('meitu_designer')->where(array('id' => $id))->find();
        return $result;
    }

    /**
     * [deleteMeituDesigner 删除美图大师]
     * @param  [type] $id [美图大师ID]
     * @return [type]     [description]
     */
    public function deleteMeituDesigner($id){
        if (empty($id)) {
            return false;
        }
        $map['id'] = $id;
        $result = M('meitu_designer')->where($map)->delete();
        return $result;
    }

    /**
     * [getMeituDesignerCount description]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getMeituDesignerCount($condition){
        if(!empty($condition)){
            $map =array(
                "comname" =>array("LIKE","%$condition%"),
                "name"    =>array("LIKE","%$condition%"),
                "_logic"  =>"OR"
            );

        }
        $result = M("meitu_designer")->where($map)->count();
        return $result;
    }

    /**
     * 获取设计师列表
     * @return [type] [description]
     */
    public function getMeituDesignerList($condition,$pageIndex,$pageCount){
        if(!empty($condition)){
            $map =array(
                "comname" =>array("LIKE","%$condition%"),
                "name"    =>array("LIKE","%$condition%"),
                "_logic"  =>"OR"
            );
        }
        $result = M("meitu_designer")->where($map)->order("enabled desc,px desc")->limit($pageIndex,$pageCount)->select();
        return $result;
    }
}