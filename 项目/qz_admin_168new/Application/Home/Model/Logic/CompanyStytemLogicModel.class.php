<?php
namespace Home\Model\Logic;
use Common\Enums\CompanyRoleGroup;
use Home\Model\Db\UserCompanyMenuModel;
use Home\Model\Db\UserCompanyRoleMenuMapModel;
use Home\Model\Db\UserCompanyRoleModel;
use Home\Validate\SystemMenuValidateModel;

/**
 *
 */
class CompanyStytemLogicModel
{
    public function getDropDownMenu($menu_model)
    {
        $model = new UserCompanyMenuModel();
        return $model->getDropDownMenu($menu_model);
    }

    public function getDropDownMenuChild($menu_model)
    {
        $model = new UserCompanyMenuModel();
        $result = $model->getDropDownMenuChild($menu_model);

        $list = [];
        foreach ($result as $item) {
           if (!array_key_exists($item["menu_node_id"],$list)) {
               $list[$item["menu_node_id"]] = [
                   "menu_name" => $item["menu_name"],
                   "menu_node_id" => $item["menu_node_id"],
                   "child" => []
               ];
           }
            $list[$item["menu_node_id"]]["child"][] = [
                "id" => $item["c_id"],
                "menu_name" => $item["c_menu_name"],
                "menu_node_id" => $item["c_menu_node_id"],
                "is_pulbic" => $item["c_is_pulbic"]
            ];
        }


        return $list;
    }

    public function getMenuInfo($id)
    {
        $model = new UserCompanyMenuModel();
        return $model->getMenuInfo($id);
    }

    public function getMenuInfoByName($menu_name,$level,$model)
    {
        $userModel = new UserCompanyMenuModel();
        return $userModel->getMenuInfoByName($menu_name,$level,$model);
    }

    public function getParentLastChildNodeId($node_id)
    {
        $model = new UserCompanyMenuModel();
        $result = $model->getParentLastChildNodeId($node_id);

        if (count($result) == 0) {
            //没有子菜单
            $last_node_id = $node_id.".1";
        } else {
            //有子菜单
            if (empty($result["parent_node_id"])) {
                //一级菜单
                $parent_node = $result["menu_node_id"];
                $last_node_id  = $parent_node + 1;
            } else {
                $parent_node = $result["parent_node_id"];
                //二级菜单
                $reg = '/(?<!pattern\.)[0-9]+$/';
                preg_match($reg, $result["menu_node_id"],$m);

                if(count($m) > 0){
                    $last_node_id  = $parent_node.".".($m[0]+1);
                } else {
                    $last_node_id = $parent_node.".1";
                }
            }
        }
       
        return $last_node_id;
    }

    public function addMenu($param)
    {
        $model = new UserCompanyMenuModel();
        return $model->addMenu($param);
    }

    public function updateMenu($id,$data)
    {
        $model = new UserCompanyMenuModel();
        return $model->updateMenu($id,$data);
    }

    public function getMenuList()
    {
        $model = new UserCompanyMenuModel();
        $result = $model->getMenuList();
        $tree = [
            "normal" => [],
            "qian" => [],
            "three" => [],
            "four" => []
        ];
        $list = [];
        //处理树状结构
        foreach ($result as $key => $item) {
            if ($item["menu_level"] == 1) {
                $item["child"] = $this->getMenuTree($result,$item["menu_node_id"]);
                $list[] = $item;
            }
        }
        $tree = [];
        //将新签返和常会菜单分开
        foreach ($list as $item) {
            //新签返
            if ($item["model"] == 2) {
                $tree["qian"][] = $item;
            } elseif ($item["model"] == 1) {
                $tree["normal"][] = $item;
            } elseif ($item["model"] == 3) {
                $tree["three"][] = $item;
            } elseif ($item["model"] == 4) {
                $tree["four"][] = $item;
            }
        }

        return $tree;
    }


    public function getRoleList()
    {
        $model = new UserCompanyRoleModel();
        $result =  $model->getRoleList();
        foreach ($result as &$item) {
            $item["role_group"] = CompanyRoleGroup::getRoleGroup($item["role_group"]);
        }
        return $result;
    }

    public function getRoleInfo($role_id)
    {
        $model = new UserCompanyRoleModel();
        return  $model->getRoleInfo($role_id);
    }

    public function getRoleMenuList($role_menu)
    {
        $model = new UserCompanyMenuModel();
        $result = $model->getRoleMenuList();
        $list = [];

        foreach ($result as $key => $item) {
            $result[$key]["menu_check"] = 2;
            if (in_array($item["menu_node_id"],$role_menu)) {
                $result[$key]["menu_check"] = 1;
            }
        }

        //处理树状结构
        foreach ($result as $key => $item) {
            if ($item["menu_level"] == 1) {
                $item["child"] = $this->getMenuTree($result,$item["menu_node_id"]);
                $list[] = $item;
            }
        }
        
        $tree = [];
        //将新签返和常会菜单分开
        foreach ($list as $item) {
            //新签返
            if ($item["model"] == 2) {
                $tree["qian"][] = $item;
            } elseif ($item["model"] == 1) {
                $tree["normal"][] = $item;
            }  elseif ($item["model"] == 3) {
                $tree["three"][] = $item;
            }  elseif ($item["model"] == 4) {
                $tree["four"][] = $item;
            }
        }

        return $tree;
    }

    public function getRoleMenu($id)
    {
        $model = new UserCompanyRoleMenuMapModel();
        $result = $model->getRoleMenu($id);
        if (count($result) > 0) {
            return array_column($result,"menu_id");
        }
        return [];
    }

    public function getRoleInfoByName($role_name)
    {
        $model = new UserCompanyRoleModel();
        return  $model->getRoleInfoByName($role_name);
    }

    public function updateRole($id,$data)
    {
        $model = new UserCompanyRoleModel();
        return  $model->updateRole($id,$data);
    }

    public function addRole($data)
    {
        $model = new UserCompanyRoleModel();
        return  $model->addRole($data);
    }

    public function delRoleMenu($user_id,$model)
    {
        $roleModel = new UserCompanyRoleMenuMapModel();
        return $roleModel->delRoleMenu($user_id,$model);
    }

    public function addRoleMenu($id,$roles,$model)
    {
        $all = [];
        foreach ($roles as $role) {
            $all[] = [
                "role_id" => $id,
                "menu_id" => $role,
                "model" => $model
            ];
        }

        if (count($all) > 0) {
            $roleModel = new UserCompanyRoleMenuMapModel();
            $roleModel->addAllInfo($all);
        }
        return false;
    }

    private function getMenuTree($data,$parentid)
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item["parent_node_id"] == $parentid) {
                //获取当前节点的子节点
                $child = $this->getMenuTree($data,$item["menu_node_id"]);
                if (count($child) > 0) {
                    $item["child"] = $child;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }

    public function updateCommonMenu($id,$data)
    {
        $model = new UserCompanyMenuModel();
        return $model->updateCommonMenu($id,$data);
    }

    public function getParentNodeId($menu_id)
    {
        $model = new UserCompanyMenuModel();
        return $model->getParentNodeId($menu_id);
    }

    public function getMenuInfoById($id)
    {
        $model = new UserCompanyMenuModel();
        return $model->getMenuInfoById($id);
    }

    public function getNodeIds($id)
    {
        $model = new UserCompanyMenuModel();
        $ids = [];
        $result = $model->getNodeIds($id);

        if (count($result) > 0) {
            foreach ($result as $item) {
                if (!in_array($item["first"],$ids)) {
                    $ids[] = $item["first"];
                }

                if (!in_array($item["second"],$ids)) {
                    $ids[] = $item["second"];
                }

                if (!in_array($item["third"],$ids)) {
                    $ids[] = $item["third"];
                }
            }
        }
        $ids = array_filter($ids);
        return $ids;
    }
}