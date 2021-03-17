<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Think\Hook;

class RbacController extends HomeBaseController
{
	public function _initialize()
	{
		parent::_initialize();
		$this->checkmenu(1);
	}

	public function index()
	{
		//获取部门及各部门的角色信息
		$department = D("Rbac")->getDepartmentAndRole();
		$this->assign("department", $department);
		//获取权限菜单的用户组信息
		$result = D("Rbac")->getRbacRole(1);
		if (count($result) > 0) {
			foreach ($result as $key => $value) {
				$roles[] = $value["role_id"];
			}
			$roles = array_flip($roles);
			$this->assign("roles", $roles);
		}
		$sysId = 2;
		if (I("get.id") !== "") {
			if (in_array($sysId, array(2, 3, 4))) {
				$sysId = I("get.id");
			}
		}

		$this->assign("navIndex", 1);
		$this->display();
	}

	/**
	 * 系统权限
	 * @return [type] [description]
	 */
	public function systemrole()
	{
		if ($_POST) {
			$sysId = I("post.id");
			$status = 0;
			if (in_array($sysId, array(2, 3, 4, 5, 6, 7, 8, 9, 10,11,12,13,14,15))) {
				$ids = I("post.ids");
				$i = $this->addRoleNode($sysId, $ids);
				if ($i !== false) {
					foreach ($ids as $key => $value) {
						S("Cache:uc:menu:" . $value, null);
					}
					$status = 1;
					$errMsg = "操作成功";
				} else {
					$errMsg = "操作失败,请联系技术部门！";
				}
				$this->ajaxReturn(array("data" => "", "info" => $errMsg, "status" => $status));
			}
		} else {
			$sysId = I("get.id");
			if (in_array($sysId, array(2, 3, 4, 5, 6, 7, 8, 9, 10,11,12,13,14,15))) {
				//获取部门及各部门的角色信息
				$department = D("Rbac")->getDepartmentAndRole();
				$this->assign("department", $department);
				//获取系统菜单的用户组信息
				$result = D("Rbac")->getRbacRole($sysId);
				if (count($result) > 0) {
					foreach ($result as $key => $value) {
						$roles[] = $value["role_id"];
					}
					$roles = array_flip($roles);
					$this->assign("sys_roles", $roles);
				}

				$this->assign("sysId", $sysId);
				$tmp = $this->fetch("tabTmp");
				$this->ajaxReturn(array("data" => $tmp, "info" => "", "status" => 1));
			}
		}
	}

	/**
	 * 保存权限
	 * @return [type] [description]
	 */
	public function saverole()
	{

		if ($_POST) {
			$ids = I("post.ids");
			$status = 0;
			if (count($ids) > 0) {
				$i = $this->addRoleNode(1, $ids);
				if ($i !== false) {
					$status = 1;
					$errMsg = "操作成功";
				} else {
					$errMsg = "操作失败,请联系技术部门！";
				}
			} else {
				$errMsg = "选择的用户组数据为空,请重新选择";
			}

			$this->ajaxReturn(array("data" => "", "info" => $errMsg, "status" => $status));
		}
	}

	private function addRoleNode($id, $ids)
	{
		//获取权限菜单的用户组信息
		$result = D("Rbac")->getRbacRole($id);
		//删除原有的缓存
		foreach ($result as $key => $value) {
			S("Cache:uc:menu:" . $value["role_id"], null);
		}
		//删除原有的记录
		D("Rbac")->delRoleNode($id);
		foreach ($ids as $key => $value) {
			$data[] = array(
				"role_id" => $value,
				"node_id" => $id
			);
		}
		if (count($data) > 0) {
			//添加新的记录
			$i = D("Rbac")->addRoleNode($data);
		} else {
			$i = true;
		}

		return $i;
	}
}