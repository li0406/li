<?php

/**
 * 小程序菜单验证
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-31 09:46:30
 */

namespace app\index\validate;

use think\Validate;

class AppletMenu extends Validate {

    protected $rule = [
    ];

    protected $message = [
        "name.require" => "请输入菜单名称",
        "link.require" => "请输入路径",
        "parentid.require" => "请选择父级菜单",
        "parentid.notIn" => "请选择父级菜单",
        "version.require" => "请选择目标功能",
        "version.notIn" => "请选择目标功能",
        "system_menu_nodeid.require" => "请选择目标功能",
        "system_menu_nodeid.notIn" => "请选择目标功能",
        "icon.require" => "请上传图标"
    ];

    public function sceneEdit() {
        return $this->only([
                "name", "link", "parentid", "version", "system_menu_nodeid", "icon"
            ])
            ->append("name", "require")
            ->append("link", "require")
            ->append("version", "require|notIn:0")
            ->append("parentid", "require|notIn:0")
            ->append("system_menu_nodeid", "require|notIn:0")
            ->append("icon", "require");
    }

    // 验证目标菜单
    public function validateNodeid($nodeid){
        $menu = model("db.rbacSystemMenu")->getByNodeid($nodeid);
        if (empty($menu)) {
            return "目标菜单不存在";
        } else if ($menu->enabled == 0) {
            return "目标菜单未启用";
        }
        
        return true;
    }
}