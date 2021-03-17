<?php

/**
 * 用户收藏
 */

namespace Common\Model;

use Think\Model;

class CollectModel extends Model
{

    //文章枚举
    const ARTICLESHOW = 1; //未审核
    const ARTICLEHIDE = 2; //发布
    const ARTICLEDEL = -1; //删除
    protected $autoCheckFields = false;

    /**
     * 获取用户收藏的公司
     * @param $where
     * @return mixed
     */
    public function getUserCollectCompany($where, $pageIndex, $pageCount)
    {
        return M('user_collect')->alias('s')
            ->field("s.*,a.`id` as company_id,a.on,a.jc qc,a.cs,a.qx,a.logo,q.comment_score,a.case_count as anli_count")
            ->join("INNER JOIN qz_user_company as q on q.userid = s.classid")
            ->join("INNER JOIN qz_user as a on q.userid = a.id")
            ->where($where)
            ->order('s.time desc')
            ->page($pageIndex, $pageCount)
            ->select();
    }

    /**
     * 获取用户收藏的效果图
     * @param $where
     * @return mixed
     */
    public function getUserCollectMeitu($where, $pageIndex, $pageCount)
    {
        $builds = M('user_collect')->alias('s')
            ->field("s.*,m.id mid,m.title,m.description,i.img_path as img_path,m.pv,m.location,m.fengge,m.huxing,m.color")
            ->join("INNER JOIN qz_meitu as m on s.classid = m.id")
            ->join("LEFT JOIN qz_meitu_img as i on i.caseid = m.id")
            ->where($where)
            ->page($pageIndex, $pageCount)
            ->group("s.id")
            ->buildSql();
        return M()->table($builds)
            ->alias('m1')
            ->field("m1.*,b.name as wz,c.name as fg,d.name as hx,e.name as ys")
            ->join("left JOIN qz_meitu_location as b on find_in_set(b.id,m1.location)")
            ->join("left JOIN qz_meitu_fengge as c on find_in_set(c.id,m1.fengge) ")
            ->join("left JOIN qz_meitu_huxing as d on find_in_set(d.id,m1.huxing) ")
            ->join("left JOIN qz_meitu_color as e on find_in_set(e.id,m1.color) ")
            ->order('m1.id desc')
            ->group("m1.id")
            ->select();
    }

    /**
     * 获取用户收藏的攻略
     * @param $where
     * @return mixed
     */
    public function getUserCollectArticle($where, $pageIndex, $pageCount)
    {
        $where['a.state'] = array('eq', self::ARTICLEHIDE);
        return M('user_collect')->alias('s')
            ->field('s.*,a.id as article_id,a.title,a.face,a.pv,r.class_id')
            ->join('inner JOIN qz_www_article as a on s.classid = a.id')
            ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
            ->where($where)
            ->order('s.time desc')
            ->group('s.id')
            ->page($pageIndex, $pageCount)
            ->select();
    }

    public function saveCollect($data)
    {
        return M('user_collect')->add($data);
    }

    public function delCollect($where)
    {
        return M('user_collect')->where($where)->delete();
    }

    public function getCollectInfo($where)
    {
        return M('user_collect')->where($where)->field('id')->find();
    }
}