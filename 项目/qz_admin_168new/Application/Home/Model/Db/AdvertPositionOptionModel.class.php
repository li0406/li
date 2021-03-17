<?php

namespace Home\Model\Db;
use Think\Model;

class AdvertPositionOptionModel extends Model {

    /**
     * 验证名称唯一性(相同父类下不重复)
     * @param  [type]  $name [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function validateByName($name, $id = 0, $parentid = 0){
        $map = array(
            "parentid" => array("EQ", $parentid),
            "name" => array("EQ", $name),
            "id" => array("NEQ", $id)
        );

        return M("advert_position_option")->where($map)->field("id")->find();
    }

    /**
     * 验证标识唯一性
     * @param  [type]  $code [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function validateByCode($code, $id = 0){
        $map = array(
            "code" => array("EQ", $code),
            "id" => array("NEQ", $id)
        );

        return M("advert_position_option")->where($map)->field("id")->find();
    }

    /**
     * 获取广告位置下级数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getChildrenNum($id){
        $map = array(
            "parentid" => array("EQ", $id)
        );
        return M("advert_position_option")->where($map)->count("id");
    }

}