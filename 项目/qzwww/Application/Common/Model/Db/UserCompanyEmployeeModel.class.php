<?php


namespace Common\Model\Db;


use Think\Model;

class UserCompanyEmployeeModel extends Model
{
    protected $tableName = 'user_company_employee';
    protected $autoCheckFields = false;

    // 获取新签返会员设计师列表，仅新签返会员公司
    public function getDesignerListByCompany($id, $limit)
    {
        $id = intval($id);
        $map = array(
            'ce.company_id' => array('eq', $id),
            'ce.position' => array('in', [2, 3, 4]),
            'ce.state' => array('eq', 1),
        );


        $buildSql = $this->alias('ce')
            ->field('ce.id, ce.account as user,ce.nick_name as name,ce.logo,ce.description,ce.position as zw,ce.experience,ce.honor,ce.state,ce.created_at,ds.sort px,if(ds.id is null, 2, 1) as hassort')
            ->where($map)
            ->join("INNER JOIN qz_user_des as d on d.userid = ce.id and d.company_id = ce.company_id")
            ->join('LEFT JOIN qz_user_company_designer_sorting ds on ds.employee_id = ce.id and ds.company_id = ' . $id)
            ->order('hassort,ds.sort,ce.id desc')
            ->limit($limit)
            ->buildSql();

        return $this->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_cases as b on t.id = b.userid and b.uid =" . $id)
            ->field("count(b.id) as casecount,t.*")
            ->order("t.hassort,t.px,t.id desc")
            ->group("t.id ")
            ->select();

    }


    // 获取新签返装修公司职位信息
    public function getZwInfoByComId($comId)
    {
        $map = array(
            'company_id' => array('eq', $comId),
            'position' => array('in', [2, 3, 4]),
        );

        return $this->where($map)
            ->field("count(id) as count,position as zw")
            ->group('position')
            ->select();
    }

    // 获取新签返装修公司的在职设计师数量
    public function getTeamDesignerListCount($comId)
    {
        $map = array(
            'company_id' => array('eq', $comId),
            'position' => array('in', [2, 3, 4]),
            'state' => array('eq', 1)
        );

        return $this->where($map)
            ->count();

    }

    public function getTeamDesignerList($comid, $pageIndex = 0, $pageCount = 10)
    {
        $map = array(
            'ce.company_id' => array('eq', $comid),
            'ce.position' => array('in', [2, 3, 4]),
            'ce.state' => array('eq', 1),
        );

        $buildSql = $this->alias('ce')
            ->field('ce.id, ce.account as user,ce.nick_name as name,ce.logo,ce.description,ce.position as zw,ce.experience,ce.honor,ce.state,ce.created_at,ds.sort px,if(ds.id is null, 2, 1) as hassort')
            ->where($map)
            ->join("INNER JOIN qz_user_des as d on d.userid = ce.id and d.company_id = ce.company_id")
            ->join('LEFT JOIN qz_user_company_designer_sorting ds on ds.employee_id = ce.id and ds.company_id = ' . $comid)
            ->order('hassort,ds.sort,ce.id desc')
            ->limit($pageIndex . "," . $pageCount)
            ->buildSql();

        return $this->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_cases as b on t.id = b.userid and b.isdelete = 1 and b.uid = " . $comid)
            ->join("LEFT JOIN qz_user_des as d on t.id = d.userid")
            ->field("count(b.id) as case_counts,d.jobtime,d.linian,d.cost,t.*")
            ->group("t.id ")
            ->order("t.hassort,t.px,t.id desc")
            ->select();

    }

    /**
     * 获取新签返公司设计师信息
     * @param  string $id  [设计师编号]
     * @param  string $cid [公司编号]
     * @return [type]      [description]
     */
    public function getQianfanSignerInfo($id='',$cs =''){
        $map = array(
            "ce.id"=>array("EQ",$id),
            "d.cs"=>array("EQ",$cs),    //  公司城市？
            "ce.position"=>array("IN",array(2,3,4)),
            'ce.state' => array('EQ', 1)    // 1在职
        );

        $buildSql = $this->alias('ce')
            ->where($map)
            ->field('ce.id as uid,ce.nick_name as name,ce.logo,ce.position as zw,d.qc,d.id as comid,e.bm')
            ->join('INNER JOIN qz_user as d on d.id = ce.company_id')
            ->join('LEFT JOIN qz_quyu as e on e.cid = d.cs')
            ->buildSql();

        $buildSql = $this->table($buildSql)->alias('s')
            ->join('LEFT JOIN qz_user_des as c on c.userid = s.uid and c.company_id = s.comid')
            ->field('s.*,c.id,c.userid,c.company_id,c.job,c.jobtime,c.fengge,c.lingyu,c.linian,c.text,c.school,c.cost,c.cases,c.popularity as pv')
            ->buildSql();

        return  M("user")->table($buildSql)->alias("t")
            ->join("left join qz_article as e on e.userid = t.userid")
            ->field("count(e.id) as acount,t.*")
            ->find();
    }


    /**
     * 获取新签返公司的设计师详情页中的其他设计师推荐列表
     * @param $userId
     * @param $companyId
     * @return array
     */
    public function otherDesignerByQianfanCompanyAndUserId($userId, $companyId)
    {
        $map = array(
            "ce.id"=>array("NEQ",$userId),
            "ce.company_id"=>array("EQ",$companyId),
            "ce.position"=>array("IN",array(2,3,4)),
            'ce.state' => array('EQ', 1)    // 1在职
        );

        $list = $this->alias("ce")
            ->field('ce.id,ce.logo,ce.nick_name as name,ce.position as zw,user_des.jobtime,user_des.fengge,count(cases.id) countcases')
            ->where($map)
            ->join('INNER JOIN qz_user as user on user.id = ce.company_id')
            ->join('INNER JOIN qz_user_des as user_des on user_des.userid = ce.id')
            ->join('LEFT JOIN qz_user_company_designer_sorting ds on ds.employee_id = ce.id and ds.company_id = ce.company_id')
            ->join('LEFT JOIN qz_cases as cases on cases.userid = ce.id AND cases.isdelete = 1 AND cases.uid = ce.company_id AND cases.`status` = 2 AND cases.classid < 3')
            ->limit(4)
            ->order('ds.sort,ce.id desc')
            ->group('ce.id')
            ->select();
        return $list ? $list : [];

    }

    // 根据装修公司id获取装修公司员工数量
    public function getEmployeeCount($comid)
    {
        $map = array(
            "company_id" => array("EQ", $comid)
        );
        return $this->where($map)->count();

    }

    // 根据城市获取首页人气设计师
    public function getPopularDesigner($cs = "", $limit = 6)
    {
        $map = array(
            "ce.position"=>array("IN",array(2,3,4)),
            'ce.state' => array('EQ', 1),    // 1在职
            'user.on' => array('EQ', 2),
            'user.classid' => array('IN', array(3,6)),
            'userc.fake' => array('EQ', 0),
        );
        if (!empty($cs)) {
            $map["user.cs"] = array("EQ", $cs);
        }

        $buildSql = $this->alias("ce")
            ->where($map)
            ->field("ce.id,ce.logo,ce.nick_name as name,ce.position as zw,ce.company_id,2 as company_type,user.cs")
            ->join('INNER JOIN qz_user as user on user.id = ce.company_id')
            ->join("INNER JOIN qz_user_company as userc on userc.userid = user.id")
            ->buildSql();

        $buildSql = $this->table($buildSql)->alias("t1")
            ->field("t1.*,user_des.jobtime,user_des.fengge,user_des.cost,convert(user_des.popularity,UNSIGNED) pv,q.bm")
            ->join('INNER JOIN qz_quyu as q on q.cid = t1.cs')
            ->join('INNER JOIN qz_user_des as user_des on user_des.userid = t1.id')
            ->limit($limit)
            ->order("convert(user_des.popularity,UNSIGNED) desc,t1.id desc")
            ->buildSql();

        $list = $this->table($buildSql)->alias("t")
            ->field("t.*,count(cases.id) case_count")
            ->join('LEFT JOIN qz_cases as cases on cases.userid = t.id AND cases.isdelete = 1 AND cases.uid = t.company_id AND cases.`status` = 2 AND cases.classid < 3')
            ->order('t.pv desc,t.id desc')
            ->group("t.id")
            ->select();

        return $list ? $list : [];
    }

}