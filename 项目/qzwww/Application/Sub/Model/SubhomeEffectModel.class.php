<?php

namespace Common\Model;
use Think\Model;

class SubhomeEffectModel extends Model{

    protected $autoCheckFields = false;

    public function getSubEffect($cs){
        $map = array(
            "cs" => array("EQ", $cs)
        );

        return M("subhome_effect")
            ->where($map)
            ->field(["id", "cs", "type", "title", "img", "url"])
            ->order("type asc,`order` asc")
            ->select();
    }
}