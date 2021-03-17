<?php
// +----------------------------------------------------------------------
// | AdminuserLogic 管理员逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use think\facade\Request;
use app\index\model\adb\Adminuser;
use app\index\enum\CacheKeyCodeEnum;
use app\index\model\db\RbacSystemMenu;

class AdminuserLogic {

    protected $adminuserModel;

    public function __construct(){
        $this->adminuserModel = new Adminuser();
    }

    // 获取角色所属部门
    public static function getRoleDepartmentInfo($role_id){
        $cachekey = sprintf(CacheKeyCodeEnum::ADMINUSER_ROLE_DEPTMENT, $role_id);
        $deptInfo = cache($cachekey) ? : [];
        if (empty($deptInfo)) {
            $adminuserModel = new Adminuser();
            $deptInfo = $adminuserModel->getRoleDeptInfo($role_id);
            if (!empty($deptInfo)) {
                if (!empty($deptInfo["dept3_id"])) {
                    $deptInfo["dept_top_id"] = $deptInfo["dept3_id"];
                    $deptInfo["dept_top_name"] = $deptInfo["dept3_name"];
                } else if (!empty($deptInfo["dept2_id"])) {
                    $deptInfo["dept_top_id"] = $deptInfo["dept2_id"];
                    $deptInfo["dept_top_name"] = $deptInfo["dept2_name"];
                } else if (!empty($deptInfo["dept1_id"])) {
                    $deptInfo["dept_top_id"] = $deptInfo["dept1_id"];
                    $deptInfo["dept_top_name"] = $deptInfo["dept1_name"];
                } else {
                    $deptInfo["dept_top_id"] = 0;
                    $deptInfo["dept_top_name"] = "";
                }

                cache($cachekey, $deptInfo, rand(300, 600));
            }
        }

        return $deptInfo;
    }

    public function getMenu($role_id)
    {
        $model = new RbacSystemMenu();
        $list = [];
        //非管理员获取菜单节点
        if ($role_id != 1) {
            $result = $model->getRoleMenu($role_id);
        } else {
            //获取所有菜单
            $result = $model->getAllMenu();
        }

        $tree = $this->getMenuTree($result,0);
        return $tree;
    }


    protected function getMenuTree($data,$node_id)
    {
        $tree = [];
        foreach ($data as $value) {
            if ($value["parentid"] == $node_id) {
                $sub = [
                    "path" => $value["link"],
                    "name" => $value["link"],
                    "component" => '@/layout',
                    "redirect" => "",
                    "meta" => [
                        "rights" => [],
                        "title" => $value["name"],
                        "icon" => empty($value["icon"]) ? "document-copy" : $value["icon"],
                    ],
                    "children" => []
                ];

                if ($value["level"] != 3) {
                    $result = $this->getMenuTree($data, $value["nodeid"]);
                    if (count($result) > 0) {
                        $sub["children"] = $result;
                    }
                }

                if ($value["level"] == 2) {
                    $sub["component"] = $value["component"];
                    $sub["meta"]["rights"] = array_column($sub["children"], "path");
                    unset($sub["children"]);
                }

                if ($value["level"] == 1) {
                    if (count($sub["children"]) > 0) {
                        $sub["redirect"] = "/" . $value["link"] . "/" . $sub["children"][0]["path"];
                    }
                }

                $tree[] = $sub;
            }
        }
        return $tree;
    }
}