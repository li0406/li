<?php

namespace Common\Model\Db;

use Think\Model;

class UserCompanyEmployeeModel extends Model
{
    public function getNewDesignerListCountByCompany($comid)
    {
        $where = [
            'u.company_id' => ['eq', $comid],
            'u.position' => ['in', [2, 3, 4]],
            'u.state' => ['eq', 1],
            'd.company_id' => ['eq', $comid],
        ];
        return $this->alias('u')
            ->where($where)
            ->join("INNER JOIN qz_user_des as d on d.userid = u.id")
            ->join("left JOIN qz_user_company_designer_sorting as s on s.employee_id = u.id")
            ->field("u.id")
            ->count();
    }

    /**
     * 获取装修公司首页设计师列表
     * @return [type] [description]
     */
    public function getNewDesignerListByCompany($comid ='',$offect = 0,$limit = 10){

        $where = [
            'u.company_id' => ['eq', $comid],
            'u.position' => ['in', [2, 3, 4]],
            'u.state' => ['eq', 1],
            'd.company_id' => ['eq', $comid],
        ];
        $buildSql = $this->where($where)->alias("u")
            ->join("INNER JOIN qz_user_des as d on d.userid = u.id")
            ->join("left JOIN qz_user_company_designer_sorting as s on s.employee_id = u.id and s.company_id = u.company_id")
            ->field("u.*,u.nick_name `name`,s.sort,d.popularity,d.jobtime,if(s.id is null, 2, 1) as hassort")
            ->limit($offect,$limit)
            ->order("hassort,s.sort,u.id desc")
            ->buildSql();
        $case_map = ' and b.isdelete = 1 and b.on = 1 and b.status in (1,2) and b.classid in (1,2,3) and b.uid = t.company_id';
        return M("user")->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_cases as b on t.id = b.userid" . $case_map)
            ->field("count(b.id) as casecount,t.*")
            ->order("t.hassort,t.`sort`,t.id desc")
            ->group("t.id")
            ->select();
    }
}
