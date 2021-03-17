<?php

// +----------------------------------------------------------------------
// | Adminuser 管理员数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class Adminuser extends BaseModel {

    // 获取管理员信息
    public function getAdminInfo($user_id){
        $map = new Query();
        $map->where("id", $user_id);

        return $this->link()->name("adminuser")->where($map)
            ->field(["id", "user", "name", "uid", "cs", "css"])
            ->find();
    }

    // 获取管理员信息
    public function getAdminCitys($user_ids){
        $map = new Query();
        $map->where("id", "in", $user_ids);

        return $this->link()->name("adminuser")->where($map)
            ->field(["id", "cs", "css"])
            ->select();
    }

    // 获取角色部门信息
    public function getRoleDeptInfo($role_id){
        $map = new Query();
        $map->where("a.role_id", $role_id);

        return $this->link()->name("role_department")->alias("a")->where($map)
            ->join("department d1", "d1.id = a.department_id", "inner")
            ->join("department d2", "d2.id = d1.parentid", "left")
            ->join("department d3", "d3.id = d2.parentid", "left")
            ->field([
                "a.role_id",
                "d1.id AS dept1_id", "d1.`name` AS dept1_name",
                "d2.id AS dept2_id", "d2.`name` AS dept2_name",
                "d3.id AS dept3_id", "d3.`name` AS dept3_name"
            ])
            ->find();
    }

    /**
     * @des:根据部门id获取子部门
     */
    public function getChildDepartment($parent_id)
    {
        $map = new Query();
        $map->where('parentid','in', $parent_id);
        $map->where('enabled', '=', 0);
        return $this->link()->name('department')->where($map)->field('id,name,parentid,enabled')->select();
    }


    /**
     * @des:获取销售冠军
     * @param $date_begin
     * @param $date_end
     * @param $department_id
     * @return mixed
     */
    public function getSaleMvp($date_begin, $date_end,$department_id)
    {
        $map = new Query();
        $mapDb = new Query();
        $map->where("a.exam_status",  3);
        $map->where("p.share_ratio", ">", 0);
        $map->where('a.payment_time','between',[strtotime($date_begin),strtotime($date_end)]);
        $mapDb->where('d.department_id','in',$department_id);
        $subSql = $this->link()->table('qz_sale_report_payment')->alias('a')->where($map)
            ->join('qz_sale_report_payment_person p', 'p.report_payment_id=a.id', 'inner')
            ->field(["p.saler_id","sum(a.payment_money*p.share_ratio/100) as saler_achievement",
                "sum((a.platform_money + a.deposit_to_platform_money) * p.share_ratio / 100) as platmoney_achievement",
                "sum((a.payment_money - a.platform_money - a.deposit_to_platform_money) * p.share_ratio / 100) as saler_noplat_achievement"])
            ->group("p.saler_id")
            ->buildSql();

        return $this->link()->table('qz_adminuser')->alias('u')->where($mapDb)
            ->join('qz_role_department d', 'd.role_id=u.uid','inner')
            ->join($subSql . ' as t', 't.saler_id=u.id')
            ->order("saler_achievement desc")
            ->field(["t.saler_id","t.saler_achievement","t.platmoney_achievement","t.saler_noplat_achievement","u.user as saler_name","u.name as saler_nickname"])
            ->find();
    }

    // 获取部门员工（不包含下级部门）
    public function getDepartmentUserAll($dept_id){
        $map = new Query();
        $map->where("u.stat", 1);
        $map->where("d.id", $dept_id);

        $list = $this->link()->name("adminuser")->alias("u")->where($map)
            ->join("role_department t", "t.role_id = u.uid", "inner")
            ->join("department d", "d.id = t.department_id", "inner")
            ->field(["u.id", "u.`user` as user_name", "t.role_id", "d.`name` as dept_name"])
            ->select();

        return $list;
    }

}