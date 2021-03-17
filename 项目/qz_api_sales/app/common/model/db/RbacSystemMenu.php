<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class RbacSystemMenu extends Model {

    protected $autoWriteTimestamp = false;

    public function getByNodeid($nodeid, $field = "*"){
        return static::where("nodeid", $nodeid)->field($field)->find();
    }

    /**
     * 根据版本和父ID查询菜单
     * @param  [type]  $version  [description]
     * @param  integer $parentid [description]
     * @return [type]            [description]
     */
    public static function getMenuByVersionParentid($version, $parentid = 0){
        $map = new Query();
        $map->where("enabled", 1);
        $map->where("version", $version);

        if (!empty($parentid)) {
            $map->where("parentid", $parentid);
        } else {
            $map->where("parentid", "0");
        }

        return static::where($map)
            ->field("nodeid,name,name as value")
            ->order("px,id desc")
            ->select();
    }
    
}