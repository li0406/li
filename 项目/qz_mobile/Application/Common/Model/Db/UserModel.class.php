<?php
namespace Common\Model\Db;

use Think\Model;

class UserModel extends Model
{
	protected $autoCheckFields = false;

    public function getDesignerListCountByCompany($comid){
        $map = array(
            "b.comid"=>array("EQ",$comid)
        );
        return M("user")->where($map)->alias("a")
            ->join("INNER JOIN qz_team as b on b.userid = a.id and b.zt = 2")
            ->field("a.*,b.zw,b.px,b.xs,b.zt")
            ->count();
    }
    /**
     * 获取装修公司首页设计师列表
     * @return [type] [description]
     */
    public function getDesignerListByCompany($comid ='',$limit = 10){
        //排序值倒序排序，其次依次根据案例数、人气值、ID倒序排序
        $map = array(
            "b.comid"=>array("EQ",$comid)
        );
        $buildSql = M("user")->where($map)->alias("a")
            ->join("INNER JOIN qz_team as b on b.userid = a.id and b.zt = 2")
            ->field("a.*,b.zw,b.px,b.xs,b.zt")->limit($limit)
            ->order("b.px desc")
            ->buildSql();
        return M("user")->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_cases as b on t.id = b.userid")
            ->field("count(b.id) as casecount,t.*")
            ->order("t.px desc,casecount desc,t.pv desc,t.id desc")
            ->group("t.id ")
            ->select();
    }

    public function getPackageInfo($company_id)
    {
        $map = array(
            "company_id" => array("IN",$company_id),
            "back_status" => array("NEQ",3)
        );
        $map["_complex"] = array(
            "use_status" => array("IN",array(1,2)),
            array(
                "use_status" => array("EQ",3),
                "back_status" => array("NEQ",3)
            ),
            "_logic" => "OR"
        );
        return M("company_package")->where($map)->field("company_id,sum(deposit_money) as deposit_money")
            ->group("company_id")->select();
    }

    public function getUserInfoById($user_id)
    {
        if (count($user_id) == 0) {
            return [];
        }
        return $this->field('id,logo,name')->where(['id' => ['in', $user_id]])->select();
    }
}