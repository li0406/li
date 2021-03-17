<?php

/**
 * 小程序菜单配置控制器
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-30 13:26:27
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\AppletMenuLogic;

use app\index\enum\AppletMenuCodeEnum;
use app\index\validate\AppletMenu as AppletMenuValidate;

class AppletMenu extends Controller {

    /**
     * 小程序菜单列表
     * @param  Request         $request         [description]
     * @param  AppletMenuLogic $appletMenuLogic [description]
     * @return [type]                           [description]
     */
    public function menuList(Request $request, AppletMenuLogic $appletMenuLogic) {
        $options = $request->param();
        $list = $appletMenuLogic->getMenuList($options);

        $response = sys_response(0, "", ["list" => $list]);
        return json($response);
    }

    /**
     * 小程序菜单详情
     * @param  Request         $request         [description]
     * @param  AppletMenuLogic $appletMenuLogic [description]
     * @return [type]                           [description]
     */
    public function menuDetail(Request $request, AppletMenuLogic $appletMenuLogic) {
        $detail = [];
        $system_menu_list = [];
        if ($id = $request->param("id")) {
            $detail = $appletMenuLogic->getMenuDetail($id);
        } else {
            $system_menu_list = AppletMenuCodeEnum::getVersionList();
        }

        $response = sys_response(0, "", [
            "detail" => $detail,
            "system_menu_list" => $system_menu_list
        ]);
        return json($response);
    }

    /**
     * 小程序菜单新增/编辑
     * @param  Request         $request         [description]
     * @param  AppletMenuLogic $appletMenuLogic [description]
     * @return [type]                           [description]
     */
    public function menuSave(Request $request, AppletMenuLogic $appletMenuLogic) {
        $data = array(
            "name" => $request->param("name"),
            "link" => $request->param("link"),
            "parentid" => $request->param("parentid"),
            "version" => $request->param("version"),
            "system_menu_nodeid" => $request->param("nodeid"),
            "icon" => $request->param("icon"),
            "remark" => $request->param("remark", ""),
            "enabled" => $request->param("enabled", 1),
            "px" => $request->param("px", 0)
        );

        // 验证
        $appValidate = new AppletMenuValidate();
        if ($appValidate->scene("edit")->check($data) == false) {
            return json(sys_response(4000002, $appValidate->getError()));
        }

        // 验证目标菜单
        $nodeid = $request->param("nodeid");
        $result = $appValidate->validateNodeid($nodeid);
        if ($result !== true) {
            return json(sys_response(4000002, $result));
        }

        // 保存数据
        $id = $request->param("id");
        $result = $appletMenuLogic->saveAppletMenu($id, $data);
        if ($result == false) {
            return json(sys_response(5000002));
        }

        return json(sys_response(0));
    }

    /**
     * 小程序菜单启用/停用
     * @param  Request         $request         [description]
     * @param  AppletMenuLogic $appletMenuLogic [description]
     * @return [type]                           [description]
     */
    public function menuEnabled(Request $request, AppletMenuLogic $appletMenuLogic) {
        $id = $request->param("id");
        $enabled = $request->param("enabled");

        $result = $appletMenuLogic->setMenuEnabled($id, $enabled);
        if (empty($result)) {
            return json(sys_response(5000002));
        }

        return json(sys_response(0));
    }

    /**
     * 获取平台菜单
     * @param  Request         $request         [description]
     * @param  AppletMenuLogic $appletMenuLogic [description]
     * @return [type]                           [description]
     */
    public function sysmenu(Request $request, AppletMenuLogic $appletMenuLogic) {
        $version = $request->param("version");
        $parentid = $request->param("parentid");
        $menuList = $appletMenuLogic->getSystemMenuList($version, $parentid);

        $response = sys_response(0, "", [
            "list" => $menuList,
            "count" => count($menuList)
        ]);
        return json($response);
    }

    /**
     * 权限菜单
     * @param  Request         $request         [description]
     * @param  AppletMenuLogic $appletMenuLogic [description]
     * @return [type]                           [description]
     */
    public function menus(Request $request, AppletMenuLogic $appletMenuLogic) {
        $tab = $request->param("tab");
        $list = $appletMenuLogic->getAuthAppletMenus($tab);
        $response = sys_response(0, "", ["list" => $list]);
        return json($response);
    }

}