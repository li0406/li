<?php
/**
 * 后台登录日志
 */
namespace Common\Model;
use Think\Model;
class RbacModel extends Model{
    protected $tableName = "rbac_role";

    public function _initialize(){
        //获取可使用的角色
        //$this->role = S("Cache:adminrole");
        if(!$this->role){
            $map = array(
                "stat"=>array("EQ",1),
                "id"=>array("NEQ",1)
                         );
            $this->role = M("rbac_role")->field("id,role_name")->where($map)->order("id")->select();
            S("Cache:adminrole",$this->role,3600*24);
        }

        //获取部门及部门包含的角色
        $this->department = S("Cache:department");
        if(! $this->department){
            $map = array(
                "enabled"=>array("EQ",0)
                         );
             $this->department = M("department")->where($map)->alias("a")
                           ->join("inner join qz_role_department as b on b.department_id = a.id")
                           ->field("a.id,a.name,group_concat(b.role_id) as roles")
                           ->group("a.id")
                           ->order("a.id,b.role_id")
                           ->select();
            //S("Cache:department", $this->department,3600*24);
        }
    }

   /**
    * 获取部门的信息及部门的角色信息
    * @param  [type] $depid [部门编号]
    * @return [type]        [description]
    */
    public function getDepartmentAndRole($depid){
        if(!empty($depid)){
            foreach ($this->department as $key => $value) {
                if($depid == $value["id"]){
                    $department[] = $value;
                    break;
                }
            }
        }else{
             $department = $this->department;
        }

        foreach ($department as $key => $value) {
            $roles = array_filter(explode(",", $value["roles"]));
            unset($department[$key]["roles"]);
            foreach ($this->role as $k => $val) {
                if(in_array($val["id"],$roles)){
                    $department[$key]["roles"][] = $val;
                }
            }
        }
        return $department;
    }

   /**
   * 获取权限的角色列表
   * @param  [type] $nodeid [description]
   * @return [type]         [description]
   */
    public function getRbacRole($nodeid){
        $map = array(
            "node_id"=>array("EQ",$nodeid)
                     );
        return M("uc_rbac_node")->where($map)->select();
    }

    /**
     * 获取用户的菜单权限信息
     * @return [type] [description]
     */
    public function getRbacRoleByUid($uid){
        $map = array(
            "role_id"=>array("EQ",$uid)
                     );
        return M("uc_rbac_node")->where($map)->select();
    }


    /**
     * 删除权限记录
     * @return [type] [description]
     */
    public function delRoleNode($nodeid){
        $map = array(
            "node_id"=>array("EQ",$nodeid)
                     );
        return M("uc_rbac_node")->where($map)->delete();
    }

    /**
     * 添加权限
     */
    public function addRoleNode($data){
        return M("uc_rbac_node")->addAll($data);
    }
}