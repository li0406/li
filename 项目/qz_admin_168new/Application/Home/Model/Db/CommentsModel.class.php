<?php
// +----------------------------------------------------------------------
// | CommentsLogicModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class CommentsModel extends Model
{
    protected $tableName = 'comment';

    public function statisticsByCompanyCount($map = [])
    {
        $monthStart = strtotime(date('Y-m-01'));
        $todayStart = strtotime('today');
        $nowTime = time();
        $buildSql = M('comment')
            ->field('count(id) all_count,count(IF(time between ' . $monthStart . ' and ' . $nowTime . ' ,1,null)) month_count,count(IF(time between ' . $todayStart . ' and ' . $nowTime . ' ,1,null)) day_count,comid')
            ->group('comid')
            ->buildSql();
        return M()->table($buildSql)->alias('t')
            ->join('qz_user u on u.id = t.comid and u.classid in (3,6)')
            ->join('qz_quyu q on u.cs = q.cid and q.is_open_city = 1')
            ->join('LEFT JOIN qz_user_company c ON c.userid = u.id')
            ->where($map)
            ->field('count(u.id) count,sum(t.day_count) all_day_count,sum(t.month_count) all_month_count,sum(t.all_count) all_all_count')
            ->find();
    }

    public function statisticsByCompany($map = [], $offset = 0, $length = 20)
    {
        $monthStart = strtotime(date('Y-m-01'));
        $todayStart = strtotime('today');
        $nowTime = time();
        $buildSql = M('comment')
            ->field('count(id) all_count,count(IF(time between ' . $monthStart . ' and ' . $nowTime . ' ,1,null)) month_count,count(IF(time between ' . $todayStart . ' and ' . $nowTime . ' ,1,null)) day_count,comid')
            ->group('comid')
            ->buildSql();
        return M()->table($buildSql)->alias('t')
            ->field('t.*,u.`name`,u.qc,u.jc,u.`user`,u.cs,q.bm,q.cname,u.`on`,c.`fake`')
            ->join('qz_user u on u.id = t.comid and u.classid in (3,6)')
            ->join('qz_quyu q on u.cs = q.cid and q.is_open_city = 1')
            ->join('LEFT JOIN qz_user_company c ON c.userid = u.id')
            ->where($map)
            ->limit($offset, $length)
            ->order('day_count DESC,month_count DESC,q.px_abc,t.comid')
            ->select();
    }

    //获取评论中userid在ucenter_user表中数据
    public function checkCommentUserid($map)
    {
        return $this->alias('c')
            ->where($map)
            ->join('qz_ucenter_user u on c.userid = u.uuid')
            ->field('c.id,c.userid')
            ->select();
    }


}