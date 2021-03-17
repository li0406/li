<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 10:09
 */
namespace Home\Model;
Use Think\Model;

class DepartmentIdentifyModel extends Model
{
    public function getAllDepartment(){
        return M('department_identify')->field('id,name')->select();
    }

    public function getDepartment($id){
        return M("department_identify")->find($id);
    }
    
    /**
     * 添加部门
     * @param [type] $data [description]
     */
    public function addDept($data)
    {
        return M("department_identify")->add($data);
    }

    /**
     * 编辑部门
     * @param  [type] $id   [部门ID]
     * @param  [type] $data [部门信息]
     * @return [type]       [description]
     */
    public function editDept($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("department_identify")->where($map)->save($data);
    }

    /**
     * 删除部门信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delDept($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("department_identify")->where($map)->delete();
    }

    /**
     * 根据ID查询渠道部门信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findDeptInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        return M("department_identify")->where($map)->alias("a")
                                       ->join("left join qz_order_source_relate as b on b.department_id = a.id")
                                       ->field("a.*,GROUP_CONCAT(b.user_id) as userid")
                                       ->group("a.id")
                                       ->find();
    }

    /**
     * 删除部门和用户的关联
     * @param  [type] $id [部门ID]
     * @return bool
     */
    public function delDeptRelate($id)
    {
        $map = array(
            "department_id" => array("EQ",$id)
        );
        return M("order_source_relate")->where($map)->delete();
    }

    /**
     * 添加部门和用户的关联
     * @param string $value [description]
     */
    public function addDeptRelate($data)
    {
        return M("order_source_relate")->addAll($data);
    }

    /**
     * 获取部门列表
     * @return [type] [description]
     */
    public function getDeptListCount()
    {
        return M("department_identify")->count();
    }

    /**
     * 获取部门列表
     * @return [type] [description]
     */
    public function getDeptList()
    {
        return M("department_identify")->order("id desc")->select();
    }

    /**
     * 获取角色部门
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findMyDept($id)
    {
        $map = array(
            "a.user_id" => array("EQ",$id)
        );
        return M("order_source_relate")->where($map)->alias("a")
                                       ->join("left join qz_order_source b on b.dept = a.department_id and b.type = 1")
                                       ->join("left join qz_department_identify c on c.id = a.department_id")
                                       ->field("b.id,a.department_id as dept,c.name as deptname,c.dept_belong")
                                       ->select();
    }

    /**
     * 获取所有部门
     * @return [type] [description]
     */
    public function findAllDept()
    {
        return M("department_identify")->select();

    }

    public function getDepartmentListWithBelong($belong)
    {
        $map["dept_belong"] = array("EQ",$belong);
        return M("department_identify")->where($map)->select();
    }
}