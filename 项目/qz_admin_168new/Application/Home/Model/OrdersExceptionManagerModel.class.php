<?php

namespace Home\Model;
Use Think\Model;
class OrdersExceptionManagerModel extends Model
{

    public function findUserInfoCount($user_id)
    {
        $map = array(
            "user_id" => array("EQ",$user_id)
        );
        return M("orders_exception_manager")->where($map)->count();
    }

    /**
     * 获取用户信息
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function findUserInfo($user_id)
    {
        $map = array(
            "u.user_id" => array("EQ",$user_id)
        );

        return M("orders_exception_manager")->where($map)->alias("u")
                             ->join("join qz_adminuser a on a.id = u.user_id")
                             ->join("join qz_rbac_role r on r.id = a.uid")
                             ->join("join qz_role_department rd on rd.role_id = a.uid")
                             ->join("join qz_department d on d.id = rd.department_id")
                             ->field("a.id,a.name,d.name as dept_name,r.role_name,u.tel")
                             ->find();
    }

    public function addInfo($data)
    {
         return M("orders_exception_manager")->add($data);
    }

    public function editInfo($id,$data)
    {
        $map = array(
            "user_id" => array("EQ",$id)
        );
        return M("orders_exception_manager")->where($map)->save($data);
    }

    /**
     * 获取管理用户列表
     * @return [type] [description]
     */
    public function getManagerList()
    {
        return M("orders_exception_manager")->alias("u")
                             ->join("join qz_adminuser a on a.id = u.user_id")
                             ->join("join qz_rbac_role r on r.id = a.uid")
                             ->join("join qz_role_department rd on rd.role_id = a.uid")
                             ->join("join qz_department d on d.id = rd.department_id")
                             ->field("a.id,a.name,d.name as dept_name,r.role_name,u.tel,u.enabled")
                             ->select();
    }

    /**
     * 删除数据
     * @param  [type] $id [用户ID]
     * @return BOOLEAN
     */
    public function delInfo($id)
    {
        $map = array(
            "user_id" => array("EQ",$id)
        );
        return M("orders_exception_manager")->where($map)->delete();
    }
}