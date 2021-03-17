<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
 * 权限控制器
 */
class AuthController extends HomeBaseController{

    public function index(){
        if(I("get.id") !== ""){
            $id = I("get.id");
            //获取我的管辖范围
            if($this->User["uid"] != 1 && !in_array($id,$this->User["groups"])){
                $this->_error("您无权设置该用户");
            }

            //获取该用户组的权限
            $result = D("RbacNodeRole")->getUserRoleNode($id);

            foreach ($result as $key => $value) {
               $nodes[$value["node_id"]] = $value["node_id"];
            }

            //查询用户信息
            $role = D("RbacRole")->getRoleInfo($id);
            $this->assign("role",$role);
            $this->assign("nodes",$nodes);
        }

        if($this->User["uid"] != 1 && count($this->User["groups"]) == 0){
            $this->_error("请先设置管辖的角色范围！");
        }

        //获取部门信息
        $departments = $this->getDepartments();

        //获取菜单
        $result = $this->getMyMenuList();

        //根据系统重新划分
        foreach ($result as $key => $value) {
            $treemenu[$key]= $value;
        }

        $this->assign("auth_treemenu",$treemenu);
        $this->assign("auth_departments",$departments);
        $this->display();
    }

    public function menu(){
        //加载系统菜单
        $list = $this->getTreeMenu();

        $levels = [1,2,3,4,5,6,7,8,9,10];
        foreach ($levels as $key => $level) {
            if (!array_key_exists($level, $list)) {
                $list[$level] = [];
            }
        }

        $this->assign("auth_list",$list);
        $this->display('menu_20190518');
    }

    public function menuUp(){
        if($_POST){
			$model = D("SystemMenu");
            $id = I("post.id");
            $status = 0;
            $errMsg = "操作失败！请联系技术部门！";
            $link = I("post.link");

            //添加全路径URL
            if(I("post.version") == 1){
                $host = C('168_URL');
            }elseif(I("post.version") == 2){
                $host = C('168NEW_URL');
            }elseif(I("post.version") == 3){
                $host = C('168YY');
            }elseif(I("post.version") == 4){
                $host = C('168CPA');
            } elseif(I("post.version") == 5) {
                $host = C('168JIAJU');
            } else {
                $host = '';
            }
            $link = ($link == '#') ? str_replace('#', '', $link) : $link;

            $linktype = I("post.linktype", 1); // 链接类型
            if ($linktype == 1 && (
                strpos($link, "http://") !== 0 &&
                strpos($link, "https://") !== 0 &&
                strpos($link, "//") !== 0)
            ) {
                $link = $host . $link;
            }

            $data = array(
                "name" => I("post.name"),
                "link" => $link,
                "link_type" => $linktype,
                "px" => I("post.px"),
                "enabled" => I("post.enabled"),
                "remark" => I("post.remark"),
                "icon" => I("post.icon"),
                "version" => I("post.version"),
                "component" => I("post.component"),
            );

            $pid = I("post.parent") == "" ? 0 : I("post.parent");
            //父类菜单信息
            $parent = $model->getMenuInfoById($pid);
            $parent["nodeid"] = ($parent["nodeid"] == "") ? 0 : $parent["nodeid"];
            $data["parentid"] = $parent["nodeid"];
            $data["level"] = $parent["level"] = $parent["level"] + 1;

            if(!empty($id)){
                //编辑
                //查询菜单信息
                $menu = $model->getMenuInfoById($id);
                if(count($menu) > 0){
                    $parentid = $menu["parentid"];
                    //判断是否改变父节点
                    if($parentid != $parent["nodeid"]){
                        //如果父节点改变
                        //1.获取在新的父节点的下的节点编号
                        $node_id = $this->getNodeId(I("post.parent"));
                        $data["nodeid"] = $node_id;
                        $data["time"] = time();
                        $ids[] = array(
                            "old" => $menu["nodeid"],
                            "new" => $node_id
                        );
                    }

                    $i = $model->editMenu($id,$data);

                    if($i !== false){
                        $status = 1;
                        if($parentid != $parent["nodeid"]){
                            //当父节点改变的时候需要同步修改权限菜单及该菜单下的所有子菜单的信息
                            //1.查询所有的二级/三级子菜单
                            $list = $model->getChildNodeList($menu["nodeid"]);
                            if(count($list) > 0){
                                $reg = '/(?<!pattern\.)[0-9]+$/';
                                foreach ($list as $key => $value) {
                                    //获取当前节点的节点ID,替换节点
                                    if(empty($value["level"])){
                                        //2级菜单
                                        $currentNodeId = $value["pnodeid"];
                                        $currentId = $value["pid"];
                                        $level = $data["level"]+1;
                                    }else{
                                        //3级菜单
                                        $currentNodeId = $value["nodeid"];
                                        $currentId = $value["id"];
                                        $level = $data["level"]+2;
                                    }
                                    //替换ID
                                    preg_match($reg,$currentNodeId,$m);
                                    $replaceNodeId = $node_id.".".$m[0];
                                    //去掉最后一个点
                                    $replaceParentNodeId = preg_replace($reg, "", $replaceNodeId);
                                    $replaceParentNodeId = rtrim($replaceParentNodeId,".");
                                    $subData = array(
                                            "nodeid" => $replaceNodeId,
                                            "parentid" => $replaceParentNodeId,
                                            "level" => $level,
                                            "time" => time()
                                                     );
                                    $model->editMenu($currentId,$subData);
                                    $ids[] = array(
                                        "old" => $currentNodeId,
                                        "new" => $replaceNodeId
                                    );
                                }
                            }
                            $rbacModel =  D("RbacNodeRole");
                            //2.修改权限菜单，替换原权限菜单的nodeid
                            foreach ($ids as $key => $value) {
                                $subData = array(
                                        "node_id" => $value["new"]
                                );
                                $rbacModel->editRoleNode($value["old"],$subData);
                            }
                        }
                    }
                }
                $remark = $this->User["name"]."编辑了菜单 ".$data["name"];
            }else{
                //新增
                $node_id = $this->getNodeId($parent["id"]);
                $data["nodeid"] = $node_id;
                $data["time"] = time();
                $id = $i = $model->addMenu($data);
                if($i !== false){
                    $status = 1;
                }
                $remark = $this->User["name"]."新增了菜单 ".$data["name"];
            }
            //添加操作日志
            $log = array(
                'remark' => $remark,
                'logtype' => 'auth',
                'action_id' => $id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            S("Cache:system_menus",null);
//			//菜单被修改，重置装修说菜单权限缓存
			$redis_logic = D('Home/Logic/RedisLogic');
			$redis_logic->del('ZXS-MENU');
            $this->ajaxReturn(array("info"=>"", "status"=>$status));

        }else{
			if(I("get.id") != ""){
                $id = I("get.id");
                $menu = D("SystemMenu")->getMenuInfoById($id);
                $this->assign("auth_menu",$menu);
            }
            $this->display();
        }
    }

    //暂停菜单
    public function edit(){
        if($_POST){
            $id = I("post.id");
            $status = 0;
            $model = D("SystemMenu");
            $data = array(
                "enabled" => 0,
                "px" =>"9999"
                          );
            $i = $model->editMenu($id,$data);
            if($i !== false){
                $status = 1;
                S("Cache:system_menus",null);
            }
            //添加操作日志
            $log = array(
                'remark' => $this->User["name"]."删除了菜单  菜单ID： ".$id,
                'logtype' => 'auth',
                'action_id' => $id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);
			//			//菜单被停用，重置装修说菜单权限缓存
			$redis_logic = D('Home/Logic/RedisLogic');
			$redis_logic->del('ZXS-MENU');
            $this->ajaxReturn(array("info"=>"", "status"=>$status));
        }
    }

    public function tree(){
        $list = $this->getParentMenu(I("get.id"),I("get.menuid"));
        $this->assign("auth_tree",$list);
        $tmp = $this->fetch("treemenu");
        $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>1));
    }

    //公司人事结构
    public function structure(){
        if (IS_POST) {
            //新增
            if ('1' == I('post.type')) {
                $save = array(
                    'parentid'      => intval(I('post.parent')),
                    'name'        => trim(I('post.name')),
                    'enabled'      => 0,
                    'time'    => time()
                );

                if( !trim($save['name'])){
                    $this->ajaxReturn(array('status' => 0, 'info' => '抱歉,输入不能为空'));
                }
                //判断该分类是否已存在
                $info = D('Department')->getDepartmentByName($save['name']);

                if (count($info) > 0) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，部门名称已存在，请重新输入'));
                }

                //新增
                $result = D('Department')->addNewDepartment($save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功'));
                }
                $this->ajaxReturn(array('data' => $save,'status' => 0, 'info' => '新增失败'));
            }
            //编辑
            if ('2' == I('post.type')) {
                $id = I('post.id');
                $save = array(
                    'name'        => trim(I('post.name')),
                    "parentid"    => intval(I('post.parent')),
                );
                if (empty($id)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
                }

                if( !trim($save['name'])){
                    $this->ajaxReturn(array('status' => 0, 'info' => '抱歉,输入不能为空'));
                }

                //判断该分类是否已存在
                $info = D('Department')->getDepartmentByName($save["name"]);

                if (count($info) > 0 && $id != $info['id']) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '该部门已存在'));
                }

                //编辑
                $result = D('Department')->editDepartment($id, $save);
                if ($result !== false) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功'));
                }
                $this->ajaxReturn(array('data' => $info,'status' => 0, 'info' => '编辑失败'));
            }
            //删除
            if ('3' == I('post.type')) {
                $id = I('post.id');
                $level = I('post.level');

                if (empty($id) || empty($level)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '删除失败'));
                }

                //查询该部门是否有子部门
                $hasChildDept = D('Department')->getDepartmentChildCount($id);
                if($hasChildDept > 0){
                    $this->ajaxReturn(array('status' => 0, 'info' => '该部门有子部门,无法删除'));
                }

                //查询该部门下是否有人员
                $hasChildUser = D('Department')->getDepartmentUserCount($id);

                if($hasChildUser > 0){
                    $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '该部门有所属人员,无法删除'));
                }
                $save = array(
                    'enabled'      => '1'
                );
                $result = D('Department')->editDepartment($id, $save);
                if ($result !== false) {
                    $this->ajaxReturn(array('data' => $result,'status' => 1, 'info' => '删除成功'));
                }
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '删除失败'));
            }
        }
        //分类列表
        $category = $this->getDepartmentList();
        $vars['info'] = $category;

        $this->assign('vars', $vars);
        $this->display();
    }

    //角色编辑
    public function role(){
        if ($_POST) {
            $id = I("post.id");
            $department_id = I("post.dept");

            $data = array(
                "role_name" => I("post.name")
            );

            if (empty($data["role_name"])) {
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '请填写角色名称'));
            }

            if (empty($department_id)) {
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '请选择所属部门'));
            }

            $result = D("RbacRole")->checkRoleExist($data["role_name"]);

            if (count($result) > 0 && $id != $result["id"]) {
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '该角色已存在'));
            }

            if (!empty($id)) {
               $i = D("RbacRole")->saveRole($data,$id);
            } else {
               $id = $i = D("RbacRole")->addRole($data);
            }

            if ($i !== false ) {
                //添加部门角色关联
                //删除原来的关联
                D("RoleDepartment")->deleteRole($id);
                $data = array(
                    "role_id" => $id,
                    "department_id" => $department_id
                );
                D("RoleDepartment")->addRole($data);
                S("Cache:adminrole",null);
                $this->ajaxReturn(array('status' => 1,'info' => '操作成功！'));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        } else {
            $tab = I("get.tab");
            //获取顶级部门
            $topDept = D("Department")->getDepartmentByParentId(0);
            if (empty($tab)) {
                $tab = $topDept[0]["id"];
            }

            //获取角色数据
            $list = $this->getDepartmentRoleList($tab,I("get.name"),I("get.dept"));

            $this->assign("tab",$tab);
            $this->assign("list",$list);
            $this->assign("topDept",$topDept);
            $this->display();
        }
    }

    // 新版角色编辑（包含上下级）
    public function rolenew(){
        if ($_POST) {
            $id = I("post.id");
            $department_id = I("post.dept_id");

            $data = array(
                "role_name" => I("post.role_name", '', 'trim'),
                "updated_at" => time()
            );

            if (empty($data["role_name"])) {
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '请填写角色名称'));
            }

            if (empty($department_id)) {
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '请选择所属部门'));
            }

            $result = D("RbacRole")->checkRoleExist($data["role_name"]);

            if (count($result) > 0 && $id != $result["id"]) {
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '该角色已存在'));
            }

            if (!empty($id)) {
               $i = D("RbacRole")->saveRole($data,$id);
            } else {
                $parentid = I("post.parentid");
                if (empty($parentid)) {
                    $data['grade'] = 1;
                    $data['parentid'] = 0;
                } else {
                    $parentInfo = D("RbacRole")->getInfo($parentid);
                    if (empty($parentInfo)) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '上级角色不存在'));
                    }

                    if ($parentInfo['grade'] >= 4) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '级别不能超过4级'));
                    }

                    $data['grade'] = $parentInfo['grade'] + 1;
                    $data['parentid'] = $parentInfo['id'];
                }

                $data["created_at"] = time();
                $id = $i = D("RbacRole")->addRole($data);
            }

            if ($i !== false ) {
                //添加部门角色关联
                //删除原来的关联
                D("RoleDepartment")->deleteRole($id);
                $data = array(
                    "role_id" => $id,
                    "department_id" => $department_id,
                    "time" => time()
                );
                D("RoleDepartment")->addRole($data);
                S("Cache:adminrole",null);
                $this->ajaxReturn(array('status' => 1,'info' => '操作成功！'));
            }

            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        }

        $tab = I("get.tab");
        //获取顶级部门
        $topDept = D("Department")->getDepartmentByParentId(0);
        if (empty($tab)) {
            $tab = $topDept[0]["id"];
        }

        //获取角色数据
        $list = $this->getDepartmentRoleTree($tab);

        $this->assign("tab", $tab);
        $this->assign("list", $list);
        $this->assign("topDept",$topDept);
        $this->display();
    }


    public function removeRole()
    {
        if ($_POST) {
            $id = I("post.id");

            // 查询该角色下的子级角色数量
            $count = D("RbacRole")->getChildrenCountById($id);
            if ($count > 0) {
                $this->ajaxReturn(array('status' => 0, 'info' => '该角色下还有子级角色用户,无法删除'));
            }

            //查询该角色下的用户
            $count = D("Adminuser")->getUserCountByRoleId($id);
            if ($count > 0) {
                $this->ajaxReturn(array('status' => 0, 'info' => '该角色下还有用户,无法删除'));
            }

            $data = array(
                "stat" => 0
            );
            $i = D("RbacRole")->saveRole($data,$id);
            if ($i !== false) {
                $this->ajaxReturn(array('status' => 1,'info' => '操作成功！'));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        }
    }

    //用户组管辖管理
    public function roleGroup(){
        //获取角色组列表
        $list = $this->getRoleGroupList(I("get.name"));
        $this->assign("list",$list);
        $this->display();
    }

    //用户组编辑操作
    public function roleGroupUp(){
        if ($_POST) {
            $ids = I("post.ids");
            $id = I("post.id");

            if (I("post.roleId") == "") {
               $this->ajaxReturn(array('status' => 0, 'info' => '请选择角色'));
            }

            $data = array(
                "group_id" => I("post.roleId"),
                "group_name" => I("post.name"),
                "role_id" => ""
            );

            if (count($ids) > 0) {
               $data["role_id"] = implode(",",$ids);
            }

            if (!empty($id)) {
                $i = D("RbacNodeGroup")->editRoleGroup($id,$data);
            } else {
                $i = D("RbacNodeGroup")->addRoleGroup($data);
            }

            if ($i !== false) {

                $this->ajaxReturn(array('status' => 1));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败！'));

        } else {
            if (I("get.id") !== "") {
                $id = I("get.id");
                $info = D("RbacNodeGroup")->getRoleGroupInfo( $id);
                $info["role_id"] = array_filter(explode(",",$info["role_id"]));
                $this->assign("info",$info);
            }
            //获取部门信息
            $list = $this->getAllDeptRoles();
            //获取角色信息
            $roles = D("RbacRole")->getAllRoleByEnabled();
            $this->assign("roles",$roles);
            $this->assign("depts",$list);
            $this->display();
        }
    }

    //更改用户组状态是否可用
    public function setRoleGroup(){
       if ($_POST) {
            $id = intval($_POST['id']);
            $i = D('RbacNodeGroup')->delRbacGroup($id);
            if ($i !== false) {
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
       }
    }

    public function roleGroupDetails()
    {
        $id = I("get.id");
        $list = $this->findRoleGroupInfo($id);

        if (count($list) == 0) {
            $this->_error("无效的用户组数据！");
        }
        $this->assign("list",$list);
        $this->display();
    }

    public function authup(){
        if($_POST){
            $id = I("post.id");
            $status = 0;
            //角色在我的管辖范围内
            if($this->User["uid"] == 1 || in_array($id,$this->User["groups"])){
                $ids = array_filter(I("post.ids"));
                foreach ($ids as $key => $value) {
                    $ids[$key] = array_unique($value);
                }

                // $idsToRoleListFunc 表单提交的ids成rolelist数组 闭包函数
                $idsToDataFunc = function($ids) {
                    $data = [];
                    foreach ($ids as $key => $value) {
                        $data[] = $key;
                        foreach ($value as $val) {
                            $data[] = $val;
                        }
                    }
                    return $data;
                };
                $idsRoleList = $idsToDataFunc($ids);

                // $idsToDataFunc 处理成ORM适配的待插入数据 闭包函数
                $roleListToDataFunc = function($roleList, $rid) {
                    $data = [];
                    foreach ($roleList as $value) {
                        $data[] = array(
                            "node_id" => $value,
                            "role_id" => $rid
                        );
                    }
                    return $data;
                };

                $status = 1;
                //添加新的权限
                if(!empty($ids)){

                    $mergeIdsList = [];
                    if ($this->User["uid"] != 1) {
                        // 非管理员做权限设置操作的时候
                        // 保留被操作角色超出操作角色的权限
                        // 获取当前操作人角色权限
                        $roleListOp = D("RbacNodeRole")->getUserRoleNode(session("uc_userinfo.uid"));
                        $roleListOpIds = [];
                        foreach ($roleListOp as $key => $value) {
                            $roleListOpIds[] = $value["node_id"];
                        }
                        // 获取被操作角色已有权限
                        $roleListTarget = D("RbacNodeRole")->getUserRoleNode($id);
                        $roleListTargetIds = [];
                        foreach ($roleListTarget as $key => $value) {
                            $roleListTargetIds[] = $value["node_id"];
                        }
                        // 保留被操作角色比操作角色多出来的权限 并 和表单提交ids做合并
                        // 也就是被操作角色比操作角色多出来的权限做保留
                        if (count($roleListOpIds) > 0 && count($roleListTargetIds) >0) {
                            $diffIds = [];
                            $diffIds = array_diff($roleListTargetIds, $roleListOpIds);
                            if ( count($diffIds) > 0 ) {
                                $mergeIdsList = array_merge($idsRoleList, $diffIds);
                                $mergeIdsList = array_unique($mergeIdsList);
                            }
                        }
                    }

                    if (count($mergeIdsList) < 1) {
                        // 默认给表单ids
                        $mergeIdsList = $idsRoleList;
                    }
                    // 转成ORM用data
                    $roleListdata = $roleListToDataFunc($mergeIdsList, $id);

                    //删除原来的权限菜单
                    D("RbacNodeRole")->delUserRoleNode($id);
                    //清除当前的菜单信息
                    session("uc_userinfo.auth_menu",null);
                    unset($_SESSION["rbac_node_list"]);
                    S("Cache:system_menus",null);

                    $status = 0;
                    $i = D("RbacNodeRole")->addAllRoleNode($roleListdata);
                    if($i !== false){
                        $status = 1;
                        $errMsg = "";
                    }
                    //装修说模块,权限变更,删除当前角色缓存的菜单
					$redis_logic = D('Home/Logic/RedisLogic');
					$redis_logic->hDel('ZXS-MENU',$id);
                }else{
                    $errMsg = "设置权限菜单失败,错误码:no ids！";
                }
            }else{
               $errMsg = "您无权设置该用户！";
            }

            $this->ajaxReturn(array('data'=>'','info'=>$errMsg,'status'=>$status));
        }
    }

    //获取节点编号
    private function getNodeId($parentid){
        //1.查询父节点的最新的子节点信息
        if($parentid != 0){
            $node = D("SystemMenu")->getMenuInfoById($parentid);
            $id = $node["nodeid"];
        }else{
            $id = 0;
        }

        $childNode = D("SystemMenu")->getMenuNodeMaxId($id);

        if(count($childNode) > 0){
            //如果菜单有子节点
            $reg = '/(?<!pattern\.)[0-9]+$/';
            preg_match($reg, $childNode["nodeid"],$m);
            if(count($m) > 0){
                $lastId  = $m[0]+1;
            }else{
                $lastId = $childNode["nodeid"]+1;
            }
            //拼接ID
            $node_id = preg_replace($reg, $lastId, $childNode["nodeid"]);
        }else{
            //没有子节点
            if(!empty($id)){
                $node_id = $id.".1";
            }else{
                $node_id = "1";
            }
        }

        return $node_id;
    }

    private function getParentMenu($id,$version){
        //获取全部菜单
        $menus = getMenuList();
        foreach ($menus as $key => $value) {
            if($value["level"] == 3){
                unset($menus[$key]);
            }
            //过滤掉自己本身
            if($id == $value["id"]){
                unset($menus[$key]);
            }

            if(!$value["enabled"]){
                unset($menus[$key]);
            }

            if (!empty($version)) {
                if ($value["version"] != $version) {
                    unset($menus[$key]);
                }
            }
        }

        $list["root"] = array(
                "name" => "根菜单",
                "id" => "0"
        );

        //合并为树形菜单
        foreach ($menus as $key => $value) {
            if($value["level"] == 1){
                $list["root"]["child"][$key] = $value;
                $arr = $this->getChildMenuList($value["nodeid"],$menus);
                if(count($arr) > 0){
                    $value["child"] = $arr[$value["nodeid"]];
                    $list["root"]["child"][$key] = $value;
                }
            }
        }
        return $list;
    }

    //获取树形菜单
    private function getTreeMenu($all = true) {
        //获取全部菜单
        $menus = getMenuList($all);

        //合并为树形菜单
        foreach ($menus as $key => $value) {
            if($value["level"] == 1){
                $list[$key] = $value;
                $arr = $this->getChildMenuList($value["nodeid"],$menus);
                if(count($arr) > 0){
                    $value["child"] = $arr[$value["nodeid"]];
                    $list[$key] = $value;
                }
            }
        }

        foreach ($list as $key => $value) {
            $treeList[$value["version"]][] = $value;
        }

        return $treeList;
    }

    private function getDepartments(){
        //获取管辖的角色信息
        $roles = $this->getRoleList();
        foreach ($roles as $key => $value) {
            $ids[] = $value["id"];
            $names[$value["id"]] = $value["role_name"];
        }
        if(count($ids) > 0){
            $result = D("Department")->getDepartmentsByRoleId($ids);

            foreach ($result as $key => $value) {
                if(!array_key_exists($value["id"], $departments)){
                    $departments[$value["id"]]["id"] = $value["id"];
                    $departments[$value["id"]]["name"] = $value["name"];
                }
                $departments[$value["id"]]["child"][] = array(
                                                        "name" => $names[$value["role_id"]],
                                                        "id" => $value["role_id"]
                                                        );
            }
        }
        return $departments;
    }

    private function getMyMenuList(){
        //获取技术部的所有角色ID
        $skill = D("Department")->getDepartmentUidById(7);
        $skillRole = array_filter(explode(",",$skill["roles"]));
        $list = $this->getTreeMenu(false);
        $authMenu = session("uc_userinfo.auth_menu");

        //获取菜单
        if($this->User["uid"] != 1 && !in_array($this->User["uid"],$skillRole)){
            //不是管理员和技术部人员的，获取自己菜单
           foreach ($list as $key => $val) {
                foreach ($val as $value) {
                    if(array_key_exists($value["nodeid"], $authMenu)) {
                        $treemenu[$value["version"]][$value["id"]]["id"] = $value["id"];
                        $treemenu[$value["version"]][$value["id"]]["nodeid"] = $value["nodeid"];
                        $treemenu[$value["version"]][$value["id"]]["name"] = $value["name"];
                        $treemenu[$value["version"]][$value["id"]]["version"] = $value["version"];
                        foreach ($value["child"] as $k => $val) {
                            if(array_key_exists($val["nodeid"], $authMenu)) {
                                $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["id"] = $val["id"];
                                $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["nodeid"] = $val["nodeid"];
                                $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["name"] = $val["name"];
                                foreach ($val["child"] as $v) {
                                    if(array_key_exists($val["nodeid"], $authMenu)) {
                                        $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["child"][$v["id"]]["id"] = $v["id"];
                                        $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["child"][$v["id"]]["nodeid"] = $v["nodeid"];
                                        $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["child"][$v["id"]]["name"] = $v["name"];
                                        $treemenu[$value["version"]][$value["id"]]["child"][$val["id"]]["child"][$v["id"]]["version"] = $v["version"];
                                    }
                                }
                            }
                        }
                   }
                }
           }
        }else{
            $treemenu = $list;
        }

        return $treemenu;
    }

    //Rbac 对像
    public function rbacobject(){

        $this->assign('info',$info);
        $this->display();
    }


    private function getDepartmentList()
    {
        $result = D('Department')->getDepartment();
        return array("tree"=> build_tree(0,$result,"parentid"),"list" => $result);
    }

    /**
     * 获取部门角色信息
     * @DateTime 2019-05-28T15:21:38+0800
     */
    private function getDepartmentRoleTree($tab){
        //获取部门ID
        $result = D("Department")->getDepartmentIds($tab);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value['id'], $departments)) {
                $departments[$value['id']] = array(
                    "id" => $value["id"],
                    "name" => $value["name"]
                );
                $ids[] = $value['id'];
            }

            if (!empty($value['second_id'])  &&  !array_key_exists($value['second_id'], $departments)) {
                $departments[$value['second_id']] = array(
                    "id" => $value["second_id"],
                    "name" => $value["name"]."/".$value["second_name"]
                );
                $ids[] = $value['second_id'];
            }

            if (!empty($value['three_id'])) {
                if (!array_key_exists($value['three_id'], $departments)) {
                    $departments[$value['three_id']] = array(
                        "id" => $value["three_id"],
                        "name" => $value["name"]."/".$value["second_name"]."/".$value["three_name"]
                    );
                    $ids[] = $value['three_id'];
                }
            }
        }

        $result = D("RbacRole")->getDpartmentGradeRoles($ids);
        foreach ($result as $key => $value) {
            $result[$key]["dept_name"] = $departments[$value["department_id"]]["name"];
            $result[$key]["created_at"] = $value["created_at"] ? date("Y-m-d", $value["created_at"]) : "-";

            $pnames = array($value["p3_name"], $value["p2_name"], $value["p1_name"]);
            $pnames = array_values(array_filter($pnames));
            $result[$key]["pnames"] = implode(",", $pnames);

            $parentids = explode(",", $value["parentids"]);
            $parentids = array_filter($parentids);

            $result[$key]["class_tag"] = "";
            foreach ($parentids as $k => $pid) {
                $result[$key]["class_tag"] .= " children_" . $pid;
            }
        }

        import('Library.Org.Util.DpTree');
        $dpTree = new \DpTree(array(
            "id" => "id",
            "pid" => "parentid",
            "name" => "role_name",
            "fullname" => "sname"
        ));

        $roles = $dpTree->getTree($result);


        //获取全部部门
        // $result = D("Department")->getAllDepartmentList();
        // foreach ($result as $key => $value) {
        //     if (!array_key_exists($value["id"],$department) && !empty($value["id"])) {
        //         $department[$value["id"]] = array(
        //             "id" => $value["id"],
        //             "name" => $value["name"]
        //         );
        //     }

        //     if (!array_key_exists($value["second_id"],$department) && !empty($value["second_id"])) {
        //         $department[$value["second_id"]] = array(
        //             "id" => $value["second_id"],
        //             "name" => $value["name"]."/".$value["second_name"]
        //         );
        //     }

        //     if (!array_key_exists($value["three_id"],$department)  && !empty($value["three_id"])) {
        //         $department[$value["three_id"]] = array(
        //             "id" => $value["three_id"],
        //             "name" => $value["name"]."/".$value["second_name"]."/".$value["three_name"]
        //         );
        //     }
        // }


        return array("roles" => $roles, "departments" => $departments);
    }

    /**
     * 获取部门角色信息
     * @param  [type] $tab [中心部门ID]
     * @return [type]       [description]
     */
    private function getDepartmentRoleList($tab,$name,$deptid)
    {
        //获取部门ID
        $result = D("Department")->getDepartmentIds($tab);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value['id'], $departments)) {
                $departments[$value['id']] = array(
                    "id" => $value["id"],
                    "name" => $value["name"]
                );
                $ids[] = $value['id'];
            }

            if (!empty($value['second_id'])  &&  !array_key_exists($value['second_id'], $departments)) {
                $departments[$value['second_id']] = array(
                    "id" => $value["second_id"],
                    "name" => $value["name"]."/".$value["second_name"]
                );
                $ids[] = $value['second_id'];
            }

            if (!empty($value['three_id'])) {
                if (!array_key_exists($value['three_id'], $departments)) {
                    $departments[$value['three_id']] = array(
                        "id" => $value["three_id"],
                        "name" => $value["name"]."/".$value["second_name"]."/".$value["three_name"]
                    );
                    $ids[] = $value['three_id'];
                }
            }
        }

        //获取部门角色信息
        if (count($ids) > 0) {
            $result = D("RbacRole")->getDpartmentRoles($ids,$name,$deptid);
            foreach ($result as $key => $value) {
                $dept_name = $departments[$value["department_id"]]["name"];
                $roles[] = array(
                        "id" => $value["id"],
                        "role_name" => $value["role_name"],
                        "dept_name" =>  $dept_name,
                        "dept_id" => $departments[$value["department_id"]]["id"]
                );
            }
        }


        //获取全部部门
        $result = D("Department")->getAllDepartmentList();
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["id"],$department) && !empty($value["id"])) {
                $department[$value["id"]] = array(
                    "id" => $value["id"],
                    "name" => $value["name"]
                );
            }

            if (!array_key_exists($value["second_id"],$department) && !empty($value["second_id"])) {
                $department[$value["second_id"]] = array(
                    "id" => $value["second_id"],
                    "name" => $value["name"]."/".$value["second_name"]
                );
            }

            if (!array_key_exists($value["three_id"],$department)  && !empty($value["three_id"])) {
                $department[$value["three_id"]] = array(
                    "id" => $value["three_id"],
                    "name" => $value["name"]."/".$value["second_name"]."/".$value["three_name"]
                );
            }
        }

        return array("roles" => $roles,"departments" => $department,"myDepartment"=>$departments);
    }

    private function getAllDeptRoles()
    {
        $result = D("RbacRole")->getRoleListByDept();
        foreach ($result as $key => $value) {
            $list[$value["id"]]["name"] = $value["name"];
            $list[$value["id"]]["child"][] = array(
                "id" => $value["role_id"],
                "name" => $value["role_name"]
            );
        }
        return $list;
    }

    private function getRoleGroupList($name = "")
    {
        $result = D("RbacNodeGroup")->getRoleGroupList($name,$id);
        foreach ($result as $key => $value) {
           $value["count"] = count(array_filter(explode(",",$value["role_id"])));
           $value["role"] = array_filter(explode(",",$value["role_name"]));
           $list[] = $value;
        }
        return $list;
    }

    public function findRoleGroupInfo($id)
    {
        $count = D("RbacNodeGroup")->findRoleGroupInfoCount($id);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D("RbacNodeGroup")->findRoleGroupInfo($id);
            foreach ($list as $key => $value) {
                $list[$key]["department"] = $value["first_name"];
                if (!empty($value["second_name"])) {
                    $list[$key]["department"] = $value["second_name"]."/".$list[$key]["department"];
                }

                if (!empty($value["three_name"])) {
                    $list[$key]["department"] = $value["three_name"]."/".$list[$key]["department"];
                }
            }
        }


        return array("list" => $list,"page"=>$show);
    }

    /**
     * 小区落户
     */
    public function area(){
        $post = I('get.');
        //获取城市小区信息
        $citys = D('Home/Logic/AuthLogic')->getCityArray('',true);
        //获取最近一周数据
        //获取最近一个礼拜的落库数据
        $post['start']  = strtotime("-1 month");
        $post['end']  = time();
        $type =  I('get.type');
        $type = empty($type)?1:$type;
        if($type == 1){
            $weeklist = D('Home/Logic/AuthLogic')->getCommunityNew($post);
            $this->assign('weeklist',$weeklist);
        }else{
            //获取个数
            $count = D('Home/Logic/AuthLogic')->getCommunityCount($post);
            //获取列表
            $list = D('Home/Logic/AuthLogic')->getCommunity($post,$count,1);

            $this->assign('list',$list["list"]);
            $this->assign('page',$list["page"]);
        }
        //物业类型
        $wuyeType = D('Home/Logic/AuthLogic')->getCommunityWuyeType();

        $shi = $post['city'];
        $area = $post['area'];
        $zuobiao = $post['zuobiao'];
        $this->assign('wuyetype',$wuyeType);
        //获取所有落库数据
        $this->assign('citys',$citys);
        $this->assign('shi',$shi);
        $this->assign('area',$area);
        $this->assign('zuobiao',$zuobiao);
        $this->assign('type',$type);
        $this->display();
    }

    public function editArea(){
        if (IS_POST) {
            $post = I('post.');
            //城市区域小区不能为空
            $post['city'] =  $post['model-city'] ;
            $post['area'] =  $post['model-area'] ;
            if(empty($post['city'])||empty($post['area'])||empty($post['xiaoqu'])||empty($post['leixing'])){
                $this->ajaxReturn(array("info"=>"必填项不能为空", "status"=>1));
            }
            //经纬度不能为空
            $jingwei = explode(',',$post["jingwei"]);
            if(empty($jingwei[0])||empty($jingwei[1])){
                $this->ajaxReturn(array("info"=>"坐标填写错误!", "status"=>1));
            }else{
                if(count($jingwei)>2){
                    $this->ajaxReturn(array("info"=>"坐标填写错误!", "status"=>1));
                }
                $lng = '/^(\-|\+)?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180)$/';
                $lan = '/^(\-|\+)?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/';
                if(!preg_match($lng,$jingwei[0])||!preg_match($lan,$jingwei[1])){
                    $this->ajaxReturn(array("info"=>"坐标填写错误!", "status"=>1));
                }
            }

            //验证小区唯一性 若小区名+城市号已存在 , 提示错误信息
            $count = D('Home/Logic/AuthLogic')->selectCommunity($post['xiaoqu'],$post['city'],$post['id']);
            if($count>0){
                $this->ajaxReturn(array("info"=>"该城市有该小区,请勿重复添加", "status"=>1));
            }

            $data["name"] = trim($post['xiaoqu']);
            $data["type"] = $post['leixing']; //类型:小区,道路
            $data["city"] = (int)($post['city']);
            $data["area"] = (int)($post['area']); //
            $data["address"] = trim($post['address']);
            $data["latitude"] = $jingwei[1];
            $data["longitude"] = $jingwei[0];
            $data["wuye_type"] = $post["wuye_type"];
            $data["wuye_money"] = trim($post["wuye_money"]);
            $data["size"] = trim($post["size"]);
            $data["houses"] = (int)($post["houses"]); //数字
            $data["year"] = (int)($post["year"]); //数字
            $data["parking"] =(int)($post["parking"]); //数字
            $data["volume"] = trim($post["volume"]);
            $data["greening"] = trim($post["greening"]);
            $data["producer"] = trim($post["producer"]);
            $data["management"] = trim($post["management"]);
            $data["school"] = trim($post["school"]);
            $data["info"] = trim($post["info"]);

            if(!empty($post['id'])&&isset($post['id'])){
                //编辑
                $data["edit_time"] = time();
                $data["read"] = 2; //编辑数据标记成已读
                $result = D('Home/Logic/AuthLogic')->editCommunity($data,$post['id']);
            }else{
                $data["add_time"] = time();
                $result = D('Home/Logic/AuthLogic')->addCommunity($data);
            }

            if($result>0){
                $this->ajaxReturn(array("info"=>"操作成功", "status"=>0));
            }else{
                $this->ajaxReturn(array("info"=>"操作失败", "status"=>1));
            }
        }
    }

    public function getOneArea(){
        if($_POST){
            $id = I('post.id');
            $info = D('Home/Logic/AuthLogic')->getOneArea($id);
            $this->ajaxReturn(array("info"=>"操作成功", "status"=>0,'data'=>$info));
        }
    }

    //批量标记数据为已读
    public function editRead(){
        if (IS_POST) {
            $ids = I('post.ids');
            if(!empty($ids)&&isset($ids)){
                $result = D('Home/Logic/AuthLogic')->editRead($ids);
                if($result){
                    $this->ajaxReturn(array("status"=>0));
                }
            }
        }
    }

    public function deleteArea(){
        if (IS_POST) {
            $id = I('post.id');
            $info = D('Home/Logic/AuthLogic')->deleteArea($id);
            if($info){
                $this->ajaxReturn(array("info"=>"删除成功", "status"=>0));
            }
        }
    }



}
