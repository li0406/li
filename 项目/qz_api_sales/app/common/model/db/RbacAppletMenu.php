<?php

/**
 * 小程序菜单配置模型
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-30 13:26:27
 */

namespace app\common\model\db;

use think\db\Query;
use think\Model;

class RbacAppletMenu extends Model {
    
    protected $autoWriteTimestamp = false;

    /**
     * 获取小程序菜单管理列表
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    public function getAppletMenuList($options, $limit = 0){
        $map = new Query();

        // ID
        if (!empty($options["id"])) {
            $map->where("m.id", $options["id"]);
        }

        // 父级菜单
        if (!empty($options["parentid"])) {
            $map->where("m.parentid", $options["parentid"]);
        }

        // 启用状态
        if (!empty($options["enabled"])) {
            $map->where("m.enabled", $options["enabled"]);
        }

        return static::alias("m")
            ->where($map)
            ->join("rbac_system_menu p1", "p1.nodeid = m.system_menu_nodeid and p1.version = m.version", "left")
            ->join("rbac_system_menu p2", "p2.nodeid = p1.parentid and p2.version = p1.version", "left")
            ->join("rbac_system_menu p3", "p3.nodeid = p2.parentid and p3.version = p2.version", "left")
            ->join("rbac_system_menu p4", "p4.nodeid = p3.parentid and p4.version = p3.version", "left")
            ->field([
                "m.id", "m.name", "m.link", "m.parentid", "m.icon", "m.px", "m.enabled", "m.remark", "m.system_menu_nodeid",
                "m.version", "p1.name as p1_name", "p2.name as p2_name", "p3.name as p3_name", "p4.name as p4_name"
            ])
            ->order("m.id desc")
            ->limit($limit)
            ->select();
    }
        
    /**
     * 设置小程序菜单启用状态
     * @param [type] $id      [description]
     * @param [type] $enabled [description]
     */
    public function setMenuEnabled($id, $enabled){
        $data = array("enabled" => $enabled, "updated_at" => time());
        return static::where("id", $id)->data($data)->update();
    }


    /**
     * 获取角色菜单
     * @return [type] [description]
     */
    public static function getMenuByRoleId($role_id = 0, $parentid = 1){
        $map = new Query();
        $map->where("s.enabled", 1);
        $map->where("a.enabled", 1);
        $map->where("a.parentid", $parentid);
        
        $dbQuery = static::alias("a")->where($map)
            ->join("rbac_system_menu s", "s.nodeid = a.system_menu_nodeid")
            ->field(["a.id", "a.name", "a.link", "a.parentid", "a.icon"])
            ->order("a.parentid asc,a.px desc,a.id desc");

        if (!empty($role_id)) {
            $dbQuery->join("rbac_node_role r", "r.node_id = s.nodeid")
                ->where("r.role_id", $role_id);
        }

        return $dbQuery->select();
    }

}