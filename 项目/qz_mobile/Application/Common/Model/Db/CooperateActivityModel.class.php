<?php


namespace Common\Model\Db;


use Think\Model;

class CooperateActivityModel extends Model
{
    protected $tableName = "cooperate_activity";

    public function getCooperateActivityListCount($map)
    {

        $all = $this->alias("activity")
            ->where($map)
            ->field('activity.id')
            ->join("LEFT JOIN qz_cooperate_activity_city city on city.aid = activity.id")
            ->group("activity.id")
            ->select();
        return count($all);
    }


    //获取活动列表
    public function getCooperateActivityList($map, $pageIndex = 0, $pageCount = 10)
    {

        $buildsql1 = $this->alias("activity")
            ->where($map)
            ->field('activity.id,activity.name,activity.prize,activity.add_num,activity.start_at,activity.end_at,activity.px,activity.created_at,activity.business_name,activity.business_logo')
            ->join("LEFT JOIN qz_cooperate_activity_city city on city.aid = activity.id")
            ->group("activity.id")
            ->buildSql();

        return $this->table($buildsql1)->alias('t')
            ->field('t.*,GROUP_CONCAT(quyu.cname) as citynames')
            ->join("LEFT JOIN qz_cooperate_activity_city c on c.aid = t.id")
            ->join("LEFT JOIN qz_quyu quyu on quyu.cid = c.cid")
            ->group("t.id")
            ->order('t.px desc,t.created_at desc')
            ->limit($pageIndex, $pageCount)
            ->select();

    }


    public function getCooperateActivityInfoById($id)
    {

        $map = [];
        $nowtime = time();
        $map['activity.id'] = array('eq', $id);
        $map['activity.start_at'] = array("elt", $nowtime);
        $map['activity.end_at'] = array("egt", $nowtime);
        $map['activity.state'] = array('eq', 1);

        $buildsql1 = $this->alias("activity")
            ->where($map)
            ->field('activity.id,activity.name,activity.prize,activity.add_num,activity.start_at,activity.end_at,activity.px,activity.created_at,activity.business_name,activity.business_logo,activity.detail')
            ->join("LEFT JOIN qz_cooperate_activity_city city on city.aid = activity.id")
            ->group("activity.id")
            ->buildSql();

        return $this->table($buildsql1)->alias('t')
            ->field('t.*,GROUP_CONCAT(quyu.cname) as citynames')
            ->join("LEFT JOIN qz_cooperate_activity_city c on c.aid = t.id")
            ->join("LEFT JOIN qz_quyu quyu on quyu.cid = c.cid")
            ->group("t.id")
            ->order('t.px desc,t.created_at desc')
            ->find();

    }

    //获取活动页面顶部广告位 banner图片
    public function getCooperateActivityBanner()
    {
        $map = [];
        $map['type'] = array('eq',1);
        return M('cooperate_activity_banner')->where($map)->find();
    }


}