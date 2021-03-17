<?php

namespace Home\Model;

use Think\Model;

class SubhomeEffectModel extends Model{

    public function getTypeList($type = 1, $cs = ""){
        $map = array(
            "type" => array("EQ", $type)
        );

        if (!empty($cs)) {
            $map["cs"] = array("EQ", $cs);
        }

        return M("subhome_effect")->where($map)
            ->field("id,cs,type,title,img,url,`order`,updated_at")
            ->order("`order` asc")
            ->select();
    }

    // 根据ID查找
    public function getById($id){
        $map = array(
            "id" => array("EQ", $id)
        );

        return M("subhome_effect")->where($map)->find();
    }

    
}