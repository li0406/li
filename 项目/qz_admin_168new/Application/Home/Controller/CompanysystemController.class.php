<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyStytemLogicModel;
use Home\Validate\SystemMenuValidateModel;

/**
 *
 */
class CompanysystemController extends HomeBaseController
{
    public function index()
    {
        $logic = new CompanyStytemLogicModel();

        //获取新签返菜单
        $list = $logic->getMenuList();
        $this->assign("list",$list);
        $this->display();
    }

    public function commonMenu()
    {
        //获取二级菜单
        $logic = new CompanyStytemLogicModel();
        $list = $logic->getDropDownMenuChild();
        $this->assign("list",$list);
        $tmp = $this->fetch("commonMenu");
        $this->ajaxReturn(["error_code" => 0,"error_msg" => "","data" => $tmp]);
    }

    public function roleup()
    {
        $logic = new CompanyStytemLogicModel();
        if (IS_POST) {
            $id = I("post.id");
            $data = [
                "role_name" => I("post.role_name"),
                "role_group" => I("post.role_group"),
                "description" => I("post.description"),
                "updated_at" => time()
            ];

            $nodes = array_unique( array_filter(explode("," ,I("post.nodes"))));

            $rules = array(
                array('role_name','require','请填写角色名称',1,"",1), //默认情况下用正则进行验证
                array('role_group','require','请填写角色类型',1,"",1), //默认情况下用正则进行验证
                array('description','require','请填写角色描述',1,"",1), //默认情况下用正则进行验证
            );

            $validate = new SystemMenuValidateModel();
            if (!$validate->validate($rules)->create($data,1)) {
                $this->ajaxReturn(["error_code" => 4000002,"error_msg" => $validate->getError()]);
            }

            //角色名称纯中文
            $reg = '/[\x{4e00}-\x{9fa5}]+/u';
            $result = preg_match_all($reg,$data["role_name"]);

            if (!$result) {
                $this->ajaxReturn(["error_code" => 4000002,"error_msg" => '角色名称必须是中文']);
            }

            //查询角色名称是否重复
            $info = $logic->getRoleInfoByName($data["role_name"]);

            if ( ( !empty($id) && !empty($info) && $id != $info["id"]  ) || (empty($id) && count($info) > 0)) {
                $this->ajaxReturn(["error_code" => 4000002,"error_msg" => "角色名称重复"]);
            }

            try {
                if (!empty($id)) {
                    //编辑
                    $logic->updateRole($id,$data);
                } else {
                    //新增
                    $data["created_at"] = time();
                    $id = $logic->addRole($data);
                }

                //修改角色权限
                //删除角色权限

                $logic->delRoleMenu($id,I("post.model"));

                //添加新权限
                $logic->addRoleMenu($id,$nodes,I("post.model"));

                $log = array(
                    'remark' => '装修公司后台角色设置',
                    'logtype' => 'usercompanyrole',
                    'action_id' => $id,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(["error_code" => 0,"error_msg" => "操作成功"]);
            } catch (\Exception $e) {
                $this->ajaxReturn(["error_code" => 4000001,"error_msg" => "操作失败"]);
            }
        } else {
            $id = I("get.id");
            $role_menu = [];
            if (!empty($id)) {
                $info =  $logic->getRoleInfo($id);
                $this->assign("info",$info);
                //获取角色的权限
                $role_menu = $logic->getRoleMenu($id);
            }

            //获取菜单列表
            $menu_list =  $logic->getRoleMenuList($role_menu);

            $this->assign("menu_list",$menu_list);
            $this->display();
        }
    }

    public function role()
    {
        $logic = new CompanyStytemLogicModel();
        $list = $logic->getRoleList();
        $this->assign("list",$list);
        $this->display();
    }

    public function menuup()
    {
        $logic = new CompanyStytemLogicModel();
        if (IS_POST) {
            $id = I("post.id");
            $module = I("post.module");
            $data = [
                "menu_name" => I("post.menu_name"),
                "is_enabled" => I("post.enabled") == 2?2:1,
                "description" => I("post.description"),
                "updated_at" => time(),
                "model" => I("post.model",1),
            ];

            $validate = new SystemMenuValidateModel();
            switch ($module) {
                case 1:
                    $data["icon"] = I("post.icon");
                    $data["px"] = I("post.px");
                    $data["menu_level"] = 1;
                    $data["parent_node_id"] =  0;
                    //目录
                    $rules = array(
                        array('menu_name','require','请填写目录名称',1,"",1), //默认情况下用正则进行验证
                        array('icon','require','请输入图标',1,"",1), //默认情况下用正则进行验证
                        array('px','require','请填写排序',1,"",1), //默认情况下用正则进行验证
                        array('is_enabled','require','请选择状态',1,"",1), //默认情况下用正则进行验证
                    );
                    break;
                case 2:
                    //菜单
                    $data["menu_level"] = 2;
                    $data["link"] = I("post.link");
                    $data["px"] = I("post.px");
                    $data["parent_node_id"] = I("post.parent_node_id");
                    $rules = array(
                        array('menu_name','require','请填写菜单名称',1,"",1), //默认情况下用正则进行验证
                        array('parent_node_id','require','请选择上级目录',1,"",1), //默认情况下用正则进行验证
                        array('link','require','请填写链接',1,"",1), //默认情况下用正则进行验证
                        array('px','require','请填写排序',1,"",1), //默认情况下用正则进行验证
                        array('is_enabled','require','请选择状态',1,"",1), //默认情况下用正则进行验证
                    );
                    break;
                case 3:
                    //按钮
                    $data["menu_level"] = 3;
                    $data["px"] = I("post.px");
                    $data["menu_mark"] = I("post.menu_mark");
                    $data["parent_node_id"] = I("post.parent_node_id");
                    $rules = array(
                        array('menu_name','require','请填写按钮名称',1,"",1), //默认情况下用正则进行验证
                        array('parent_node_id','require','请选择上级目录',1,"",1), //默认情况下用正则进行验证
                        array('is_enabled','require','请选择状态',1,"",1), //默认情况下用正则进行验证
                    );
                    break;
            }

            if (!$validate->validate($rules)->create($data,1)) {
                $this->ajaxReturn(["error_code" => 4000002,"error_msg" => $validate->getError()]);
            }

            //查询新菜单是否存在
            if ($data["menu_level"] == 2) {
                //菜单的名称唯一
                $info =  $logic->getMenuInfoByName($data["menu_name"],$data["menu_level"],$data["model"]);

                if (count($info) > 0 && $info["id"] != $id) {
                    $this->ajaxReturn(["error_code" => 4000002,"error_msg" => "菜单已存在"]);
                }
            }

            //查询编辑的菜单信息
            $menu_info = [];
            if (!empty($id) ) {
                $menu_info = $logic->getMenuInfoById($id);
            }

            if (empty($id) || $menu_info["parent_node_id"] != $data["parent_node_id"]) {
                //获取父类菜单的最小子菜单id
                $last_node_id = $logic->getParentLastChildNodeId($data["parent_node_id"]);
                $data["menu_node_id"] = $last_node_id;
            }

            try {
                if (!empty($id)) {
                    //修改
                    $logic->updateMenu([$id],$data);
                } else {
                    //新增
                    $data["created_at"] = time();
                    $id = $logic->addMenu($data);
                }

                $log = array(
                    'remark' => '装修公司后台菜单设置',
                    'logtype' => 'usercompanymenu',
                    'action_id' => $id,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);

                $this->ajaxReturn(["error_code" => 0,"error_msg" => "操作成功"]);
            } catch (\Exception $e) {
                $this->ajaxReturn(["error_code" => 4000001,"error_msg" => "操作失败"]);
            }

        } else {
            $id = I("get.id");
            $model = I("get.model");
            $menu_title = "新增菜单";
            if (!empty($id)) {
               $info =  $logic->getMenuInfo($id);
               $menu_title = "编辑菜单";

               $this->assign("info",$info);
            }
            //获取顶级菜单
            $dropMenu = $logic->getDropDownMenu($model);

            //获取二级菜单
            $dropMenuchild = $logic->getDropDownMenuChild($model);

            $this->assign("menu_title",$menu_title);
            $this->assign("dropmenu",$dropMenu);
            $this->assign("dropMenuchild",$dropMenuchild);
            $tmp = $this->fetch("menuup");
            $this->ajaxReturn(["error_code" => 0,"error_msg" => "","data" => $tmp]);
        }
    }

    public function menustatusup()
    {
        if (IS_POST) {
            $id = I("post.id");
            $enabled = I("post.enabled");
            $data = [
                "is_enabled" => $enabled
            ];
            $logic = new CompanyStytemLogicModel();
            //获取菜单及子菜单
            $ids = $logic->getNodeIds($id);
            $logic->updateMenu($ids,$data);
            $this->ajaxReturn(["error_code" => 0,"error_msg" => "操作成功"]);
        }
    }

    public function rolestatusup()
    {
        if (IS_POST) {
            $id = I("post.id");
            $enabled = I("post.enabled");
            $data = [
                "is_enabled" => $enabled
            ];
            $logic = new CompanyStytemLogicModel();
            $logic->updateRole($id,$data);
            $this->ajaxReturn(["error_code" => 0,"error_msg" => "操作成功"]);
        }
    }
}