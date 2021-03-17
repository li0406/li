<?php
/**
 *  公司排名信息每日记录表
 */

namespace Common\Model\Db;

use Think\Model;

class UserCompanyRankModel extends Model
{
    protected $tableName = 'user_company_rank';
    protected $autoCheckFields = false;

    /**
     * 根据案例数获取装公司列表
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getUserYesterdayRank($page = 0, $limit = 30)
    {
        //数据库中存储的是时间格式
        $yesterday = date('Y-m-d', strtotime('-1day'));
        $where = [
            'r.`day`' => ['between', "$yesterday,$yesterday"],
            'u.on' => ['eq', 2],
            'u.classid ' => ['eq', 3],
        ];
        $sql = $this->alias('r')
            ->field('u.id,u.jc,u.qc,u.cs,u.logo,uc.cooperate_mode,r.casesnum case_count,r.haoping comment_count')
            ->join('qz_user u on u.id = r.comid')
            ->join('qz_user_company uc on uc.userid = r.comid')
            ->where($where)
            ->order('r.casesnum desc')
            ->limit($page, $limit)
            ->buildSql();
        return $this->table($sql)->alias('t')
            ->field('t.*,q.bm,q.cname')
            ->join('qz_quyu q on q.cid = t.cs')
            ->order('t.case_count desc')
            ->select();
    }
}