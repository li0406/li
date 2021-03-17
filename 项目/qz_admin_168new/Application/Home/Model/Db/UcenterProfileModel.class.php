<?php


namespace Home\Model\Db;


use Think\Model;

class UcenterProfileModel extends Model
{
    protected $tableName = "ucenter_profile";

    /**
     * 获取满足条件的会员总数量
     * @param $map
     * @return int
     */
    public function getAllUserByMapCount($map)
    {
        $count = $this->alias('p')
            ->where($map)
            ->field('p.uuid')
            ->select();

        return count($count);
    }

    /**
     * 获取会员列表
     * @param array $map
     * @param string $order
     * @return mixed
     */
    public function get_member($map = [], $order = 'sum_score desc,first_score_time desc', $pageIndex = 0, $pageCount = 20)
    {
        $startime = strtotime(date('Y-m-d', time()));
        $endtime = strtotime(date('Y-m-d', time()) . ' 23:59:59');

        $buildsql = $this->alias('p')
            ->where($map)
            ->field('p.uuid,p.score,p.all_score,p.level,p.growth,p.first_score_time,p.avatar,p.nickname,CASE WHEN sum(srr.score) >5000 THEN 99 ELSE 10 END AS sum_score')
            ->join('left join qz_score_receive_record srr on srr.uuid = p.uuid and (srr.created_at >= ' . $startime . ' and srr.created_at <= ' . $endtime . ')')
            ->group('p.uuid')
            ->order('sum_score desc')
            ->buildsql();

//        if ($order == 'sum_score desc,meo_count asc' || $order == 'sum_score desc,meo_count desc') {
        if (strpos($order, 'meo_count') !== false) {
            $buildsql = $this->table($buildsql)->alias('p')
                ->where($map)
                ->field('p.*,count(meo.id) as meo_count')
                ->join('left join qz_mall_exchange_order meo on meo.uuid = p.uuid')
                ->limit($pageIndex, $pageCount)
                ->group('p.uuid')
                ->order($order)
                ->buildsql();
            return $this->table($buildsql)->alias('t')
                ->field('t.*,count(sr.id) as sr_count')
                ->join('left join qz_score_recommend sr on sr.inviter = t.uuid and sr.status = 1')
                ->group('t.uuid')
                ->order($order)
                ->select();
//        } elseif ($order == 'sum_score desc,sr_count asc' || $order == 'sum_score desc,sr_count desc') {
        } elseif (strpos($order, 'sr_count') !== false) {
            $buildsql = $this->table($buildsql)->alias('p')
                ->where($map)
                ->field('p.*,count(sr.id) as sr_count')
                ->join('left join qz_score_recommend sr on sr.inviter = p.uuid and sr.status = 1')
                ->limit($pageIndex, $pageCount)
                ->group('p.uuid')
                ->order($order)
                ->buildsql();
            return $this->table($buildsql)->alias('t')
                ->field('t.*,count(meo.id) as meo_count')
                ->join('left join qz_mall_exchange_order meo on meo.uuid = t.uuid')
                ->group('t.uuid')
                ->order($order)
                ->select();

        } else {
            $buildsql = $this->table($buildsql)->alias('p')
                ->where($map)
                ->field('p.*')
                ->limit($pageIndex, $pageCount)
                ->order($order)
                ->buildsql();
            $buildsql1 = $this->table($buildsql)->alias('t')
                ->field('t.*,count(sr.id) as sr_count')
                ->join('left join qz_score_recommend sr on sr.inviter = t.uuid and sr.status = 1')
                ->group('t.uuid')
                ->order($order)
                ->buildsql();

            return $this->table($buildsql1)->alias('t2')
                ->field('t2.*,count(meo.id) as meo_count')
                ->join('left join qz_mall_exchange_order meo on meo.uuid = t2.uuid')
                ->group('t2.uuid')
                ->order($order)
                ->select();

        }
    }


    /**
     *获取所有满足条件的会员
     */
    public function getAllMember($map = [], $order = 'sum_score desc,first_score_time desc')
    {
        $startime = strtotime(date('Y-m-d', time()));
        $endtime = strtotime(date('Y-m-d', time()) . ' 23:59:59');

        $buildsql = $this->alias('p')
            ->where($map)
            ->field('p.uuid,p.score,p.all_score,p.level,p.growth,p.first_score_time,p.avatar,p.nickname,CASE WHEN sum(srr.score) >5000 THEN 99 ELSE 10 END AS sum_score')
            ->join('left join qz_score_receive_record srr on srr.uuid = p.uuid and (srr.created_at >= ' . $startime . ' and srr.created_at <= ' . $endtime . ')')
            ->group('p.uuid')
            ->order('sum_score desc')
            ->buildsql();

        if (strpos($order, 'meo_count') !== false) {
            $buildsql = $this->table($buildsql)->alias('p')
                ->where($map)
                ->field('p.*,count(meo.id) as meo_count')
                ->join('left join qz_mall_exchange_order meo on meo.uuid = p.uuid')
                ->group('p.uuid')
                ->order($order)
                ->buildsql();
            return $this->table($buildsql)->alias('t')
                ->field('t.*,count(sr.id) as sr_count')
                ->join('left join qz_score_recommend sr on sr.inviter = t.uuid and sr.status = 1')
                ->group('t.uuid')
                ->order($order)
                ->select();
        } elseif (strpos($order, 'sr_count') !== false) {
            $buildsql = $this->table($buildsql)->alias('p')
                ->where($map)
                ->field('p.*,count(sr.id) as sr_count')
                ->join('left join qz_score_recommend sr on sr.inviter = p.uuid and sr.status = 1')
                ->group('p.uuid')
                ->order($order)
                ->buildsql();
            return $this->table($buildsql)->alias('t')
                ->field('t.*,count(meo.id) as meo_count')
                ->join('left join qz_mall_exchange_order meo on meo.uuid = t.uuid')
                ->group('t.uuid')
                ->order($order)
                ->select();

        } else {
            $buildsql = $this->table($buildsql)->alias('p')
                ->where($map)
                ->field('p.*')
                ->order($order)
                ->buildsql();
            $buildsql1 = $this->table($buildsql)->alias('t')
                ->field('t.*,count(sr.id) as sr_count')
                ->join('left join qz_score_recommend sr on sr.inviter = t.uuid and sr.status = 1')
                ->group('t.uuid')
                ->order($order)
                ->buildsql();

            return $this->table($buildsql1)->alias('t2')
                ->field('t2.*,count(meo.id) as meo_count')
                ->join('left join qz_mall_exchange_order meo on meo.uuid = t2.uuid')
                ->group('t2.uuid')
                ->order($order)
                ->select();

        }
    }


    //添加积分
    public function adduserScore($uuid, $score)
    {
        $map = [];
        $map["uuid"] = array("eq", $uuid);
        return $this->where($map)->setInc("score", intval($score));
    }

    //添加总积分
    public function addUserAllScore($uuid, $score)
    {
        $map = [];
        $map["uuid"] = array("eq", $uuid);
        return $this->where($map)->setInc("all_score", intval($score));
    }

    //添加成长值
    public function addUserGrowth($uuid, $growth)
    {
        $map = [];
        $map["uuid"] = array("eq", $uuid);
        return $this->where($map)->setInc("growth", intval($growth));
    }


    public function getUserScoreInfo($uuid)
    {
        $map = [];
        $map["uuid"] = array("eq", $uuid);
        return $this->where($map)->field('uuid,score,level,growth')->find();

    }

    /**
     * 更新用户信息表
     * @param $uuid
     * @param $savedata
     * @return UcenterProfile
     */
    public function updateUserProfile($map, $savedata)
    {
        return $this->where($map)->save($savedata);
    }

}