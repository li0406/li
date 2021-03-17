<?php
/**
 * 后台用户
 */
namespace Common\Model;
use Think\Model;
class AdminuserModel extends Model{
    protected $_validate = array(
        array('user','require','登录账号不能为空',1,"",1), //登陆验证
        array('pass','require','登录密码不能为空',1,"",1), //登陆验证
        array('name','require','昵称不能为空',1,"",2), //编辑个人资料
        array('logo','require','头像不能为空',1,"",4), //修改头像
        array('pass','require','密码不能为空',1,"",5),//修改密码
        array('pass',"6,100",'密码长度不能少于6位',1,'length',5), // 验证确认密码是否和密码一致
        array('confirmpassword','pass','确认密码不正确',1,'confirm',5), //修改密码，验证密码长度
        array('safe_tel','require','电话不能为空',1,"",6), //绑定安全电话/解除绑定安全电话
    );
    /**
     * 根据账号查询账户信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function findUserInfo($name){
        $map = array(
            "a.user"=>array("EQ",$name),
            "a.stat"=>array("EQ",1),
            "a.state" => array("EQ",1)
                     );
        return M("adminuser")->where($map)->alias("a")
                             ->join("left join qz_rbac_role as b on b.id = a.uid")
                             ->join("left join qz_role_department as c on c.role_id = a.uid")
                             ->join("left join qz_department as d on d.id = c.department_id")
                             ->join("left join qz_rbac_node_group as e on e.group_id = b.id")
                             ->field("a.*,b.role_name,b.level,d.name as department,d.id as department_id,e.role_id as groups")
                             ->find();
    }

    /**
     * 根据ID查询用户信息
     * @param  [type] $id        [用户编号]
     * @param  [type] $solutions [VOIP提供商标识]
     * @return [type]            [description]
     */
    public function  findUserInfoById($id,$solutions){
        $map = array(
            "a.id"=>array("EQ",$id)
                     );
        return M("adminuser")->where($map)->alias("a")
                             ->join("left join qz_admin_voip_tels as b on b.use_id = a.id and solutions = '$solutions'")
                             ->field("a.pass,a.tel_work1,a.qq_work1,a.name,a.tel_customer_ser_num,a.safe_tel,a.time,b.voipAccount,b.voipPwd")
                             ->find();
    }

    /**
     * 编辑用户信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editUserInfo($id,$data){
        $map = array(
            "id"=>array("EQ",$id)
                     );
        return M("adminuser")->where($map)->save($data);
    }

    public function getTopDepartment($department_id){
        $map = array(
            "d.id" => array("EQ", $department_id)
        );

        $ret = M("department")->alias("d")->where($map)
            ->join("left join qz_department AS p1 ON p1.id = d.parentid")
            ->join("left join qz_department AS p2 ON p2.id = p1.parentid")
            ->join("left join qz_department AS p3 ON p3.id = p2.parentid")
            ->field(["d.id", "p1.id as p1_id", "p2.id as p2_id", "p3.id as p3_id"])
            ->find();

        if (!empty($ret)) {
            if (!empty($ret["p3_id"])) {
                $ret["top_id"] = $ret["p3_id"];
            } else if (!empty($ret["p2_id"])) {
                $ret["top_id"] = $ret["p2_id"];
            } else if (!empty($ret["p1_id"])) {
                $ret["top_id"] = $ret["p1_id"];
            } else {
                $ret["top_id"] = $ret["id"];
            }
        }

        return $ret;
    }

}