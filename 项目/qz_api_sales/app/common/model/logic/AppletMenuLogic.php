<?php

/**
 * 小程序菜单配置逻辑层
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-30 13:26:27
 */

namespace app\common\model\logic;

use think\facade\Request;
use app\common\model\db\RbacAppletMenu;
use app\common\model\db\RbacSystemMenu;
use app\index\enum\AppletMenuCodeEnum;

class AppletMenuLogic {

    /**
     * 获取小程序菜单管理列表
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    public function getMenuList($options){

        $rbacAppletMenu = new RbacAppletMenu();
        $list = $rbacAppletMenu->getAppletMenuList($options);

        $list = array_map(function($item){
            $system_menu = array_values(array_filter([
                AppletMenuCodeEnum::getItem("version", $item["version"]),
                $item["p4_name"], $item["p3_name"], $item["p2_name"], $item["p1_name"]
            ]));

            $item["system_menu"] = implode("-", $system_menu);
            unset($item["p4_name"], $item["p3_name"], $item["p2_name"], $item["p1_name"]);
            return $item;
        }, $list->toArray());

        return $list;
    }

    /**
     * 获取详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMenuDetail($id){
        $options = ["id" => $id];
        $rbacAppletMenu = new RbacAppletMenu();
        $list = $rbacAppletMenu->getAppletMenuList($options, 1);

        $detail = [];
        if (count($list) > 0) {
            $detail = $list[0];

            $detail["system_menu"] = array_values(array_filter([
                AppletMenuCodeEnum::getItem("version", $detail["version"]),
                $detail["p4_name"], $detail["p3_name"], $detail["p2_name"], $detail["p1_name"]
            ]));

            unset($detail["p4_name"], $detail["p3_name"], $detail["p2_name"], $detail["p1_name"]);
        }

        return $detail;
    }

    /**
     * 设置小程序菜单启用状态
     * @param [type] $id      [description]
     * @param [type] $enabled [description]
     */
    public function setMenuEnabled($id, $enabled){
        $enabled = $enabled == 1 ? 1 : 2;
        $rbacAppletMenu = new RbacAppletMenu();
        return $rbacAppletMenu->setMenuEnabled($id, $enabled);
    }

    /**
     * 获取系统菜单
     * @param  [type] $version  [description]
     * @param  [type] $parentid [description]
     * @return [type]           [description]
     */
    public function getSystemMenuList($version, $parentid){
        return RbacSystemMenu::getMenuByVersionParentid($version, $parentid);
    }

    /**
     * 保存数据
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveAppletMenu($id, $data){
        $data["enabled"] = $data["enabled"] == 1 ? 1 : 2;

        if (empty($id)) {
            $data["created_at"] = time();
            $data["updated_at"] = time();
            $result = RbacAppletMenu::insert($data);
        } else {
            $data["updated_at"] = time();
            $result = RbacAppletMenu::where("id", $id)->update($data);
        }

        return $result;
    }

    /**
     * 获取权限菜单
     * @param  [type] $tab [description]
     * @return [type]      [description]
     */
    public function getAuthAppletMenus($tab){
        $role_id = Request::instance()->user["role_id"];

        $role_id = $role_id == 1 ? 0 : $role_id;
        $list = RbacAppletMenu::getMenuByRoleId($role_id, $tab);

        return $list->toArray();
    }
}