<?php


namespace Home\Model\Db;


use Think\Model;

class UserCompanyRankModel extends Model
{
    protected $tableName = "user_company_rank";

    public function getRankList($company_id)
    {
        if (count($company_id) == 0) {
            return [];
        }
        $where = [
            'day' => ['between', [date('Y-m-d', strtotime('-1day')), date('Y-m-d')]],
            'comid' => ['in', $company_id],
        ];
        $buildSql = $this->field('comid,haoping,casesnum,ping')->where($where)->order('id desc')->buildSql();
        return $this->table($buildSql)->alias('t')
            ->group('t.comid')
            ->select();
    }
}