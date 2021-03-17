<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class OrdermanageController extends HomeBaseController
{
    private $dept = array(
        "总裁办",
        "新媒体部",
        "推广一部",
        "渠道部",
        "公装部",
        "推广二部",
        "线下广告",
        "品牌部"
    );


    public function index()
    {
        //获取部门列表
        $list = $this->getDeptList();
        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }

    public function deptUp()
    {
        if ($_POST) {
            $data = array(
                "name" => I("post.dept"),
                "dept_belong" => I("post.deptbelong")
            );
            $id = I("post.id");
            if (!empty($id)) {
               $i = D("DepartmentIdentify")->editDept($id,$data);
            } else {
               $id = $i = D("DepartmentIdentify")->addDept($data);
            }

            if ($i !== false) {
                $ids = array_filter(explode(",", I("post.ids")));
                foreach ($ids as $key => $value) {
                    $subData[] = array(
                        "user_id" => $value,
                        "department_id" => $id
                    );
                }
                //删除原有部门关联
                D("DepartmentIdentify")->delDeptRelate($id);
                //添加部门关联
                D("DepartmentIdentify")->addDeptRelate($subData);
                //添加操作日志
                $logData = array(
                    "remark"=>$action ."标识部门管理【".$id."】",
                    "action_id"=>$id,
                    "info"=>I("post."),
                    "logtype"=>"ordersource"
                );
                D('LogAdmin')->addLog($logData);
                $this->ajaxReturn(array("status"=> 1));
            }
            $this->ajaxReturn(array("info"=>"操作失败，数据连接异常！","status"=> 0));
        } else {
            if (I("get.id") !== "") {
                $info =  D("DepartmentIdentify")->findDeptInfo(I("get.id"));
                $info["userid"] = array_filter(explode(",",$info["userid"]));
                $this->assign("info",$info);
            }

            //获取项目中心、市场中心所有角色
            $result = D("RbacRole")->getRoleListByDept(15);
            foreach ($result as $key => $value) {
                $uids[] = $value["role_id"];
            }

            $result = D("RbacRole")->getRoleListByDept(25);
            foreach ($result as $key => $value) {
                $uids[] = $value["role_id"];
            }

            //获取部门人员信息
            $result = D("Adminuser")->getAdminuserListByUid($uids,1);
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["deptid"],$users)) {
                    $users[$value["deptid"]]["name"] = $value["deptname"];
                }
                $users[$value["deptid"]]["child"][] = $value;
            }

            $this->assign("users",$users);
            $this->assign("dept",$this->dept);
            $this->display();
        }
    }

    public function delete()
    {
        if ($_POST) {
            $id = I("post.id");
            //查询部门下是否有渠道
            $count = D("OrderSource")->findDeptSourceCount($id);
            if ($count > 0) {
                $this->ajaxReturn(array("status" => 0,"info"=>"此标识部门下有所属的渠道标识，无法删除！"));
            }

            //删除部门
            $i = D("DepartmentIdentify")->delDept($id);
            if ($i !== false) {
                //删除角色和部门关联
                D("DepartmentIdentify")->delDeptRelate($id);
                $this->ajaxReturn(array("status"=> 1));
            }
            $this->ajaxReturn(array("status"=> 0,"info"=>"删除失败，网络连接错误！"));
        }
    }

    /**
     * 获取部门列表
     * @return [type] [description]
     */
    private function getDeptList()
    {
        $count = D("DepartmentIdentify")->getDeptListCount();
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $result = D("DepartmentIdentify")->getDeptList();
        }
        return array("list"=>$result,"page"=>$show);
    }

}