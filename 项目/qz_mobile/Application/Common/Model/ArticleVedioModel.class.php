<?php

namespace Common\Model;

use Think\Model;

/**
 * 攻略视频表
 */
class ArticleVedioModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取视频列表
     * @param  [type] $type  [视频类型]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getVedioList($type, $limit)
    {
        $map = array(
            "type" => array("EQ", $type),
            "isdelete" => array("EQ", 0),
            "istop" => array("EQ", 1)
        );
        return M("article_vedio")->where($map)->field("id,title,cover_img,author,time,pv")->order("id desc")->limit($limit)->select();
    }

    /**
     * [getArticleVedioById 通过ID获取视频]
     * @param  [type] $id [ID]
     * @return [type]     [description]
     */
    public function getArticleVedioById($id)
    {
        if (empty($id)) {
            return false;
        }
        $result = M('article_vedio')->where(['id' => $id])->find();
        return $result;
    }

    /**
     * [getNearArticleVedio 获取相邻资源]
     * @param  [type] $type  [类型]
     * @param  [type] $id    [id]
     * @param  [type] $extra [prev:上一个,next:下一个]
     * @return [type]        [description]
     */
    public function getNearArticleVedio($type, $id, $extra)
    {
        if ($extra == 'prev') {
            $result = M('article_vedio')->where(['id' => array('LT', $id), 'type' => $type])->order('id DESC')->find();
        } else {
            $result = M('article_vedio')->where(['id' => array('GT', $id), 'type' => $type])->order('id')->find();
        }
        return $result;
    }

    /**
     * [getRecommendArticleVedio 获取推荐视频]
     * @param  [type]  $type    [类型]
     * @param  integer $start [开始页]
     * @param  integer $end [结束页]
     * @param  string $keyword [关键字]
     * @return [type]           [description]
     */
    public function getRecommendArticleVedio($type, $start = 0, $end = 1, $keyword = '')
    {
        $map['istop'] = 1;
        if (!empty($keyword)) {
            $map["_complex"] = array(
                "v.title" => array("LIKE", "%$keyword%"),
                "v.description" => array("LIKE", "%$keyword%"),
                "_logic" => "OR"
            );
        }
        if (!empty($type)) {
            $map['type'] = $type;
        }

        $result = M('article_vedio')->alias('v')
            ->where($map)
            ->order('v.id DESC')
            ->limit($start, $end)
            ->select();
        if (count($result) == 1) {
            $result = $result[0];
        }
        return $result;
    }

    /**
     * [getArticleVedioList 获取视频列表]
     * @param  [type] $type    [类型]
     * @param  [type] $start   [开始页面]
     * @param  [type] $end     [结束页]
     * @param  [type] $keyword [关键字]
     * @return [type]          [description]
     */
    public function getArticleVedioList($type, $start, $end, $keyword, $order)
    {
        if (!empty($keyword)) {
            $map["_complex"] = array(
                "v.title" => array("LIKE", "%$keyword%"),
                "v.description" => array("LIKE", "%$keyword%"),
                "_logic" => "OR"
            );
        }
        if (!empty($type)) {
            $map['type'] = $type;
        }

        if (empty($order)) {
            $order = 'v.id DESC';
        }

        $map['isdelete'] = ['NEQ', 1];

        $result['count'] = M('article_vedio')->alias('v')->where($map)->count();
        $result['list'] = M('article_vedio')->alias('v')
            ->where($map)
            ->order($order)
            ->limit($start, $end)
            ->select();
        return $result;
    }

    /**
     * 获取首页列表页
     * @param  integer $pid 大分类ID
     * @param  integer $cid 子分类ID
     * @param  integer $start 开始页
     * @param  integer $end 查询个数
     * @return array
     */
    public function getArticleVedioListByCategoryForIndex($pid = 0, $cid = 0, $start = 0, $end = 10, $orderby = '')
    {
        $map = [];
        if (!empty($pid)) {
            $map['pid'] = $pid;
        }
        if (!empty($cid)) {
            $map['cid'] = $cid;
        }
        $build = M('article_video_category')->field('vid')->where($map)->group('vid')->buildSql();
        return M()->table($build)->alias('c')
            ->field('v.id, v.title, v.time, v.uname, v.cover_img,v.author')
            ->join('qz_article_vedio AS v ON v.id = c.vid')
            ->where(array('v.isdelete' => '0'))
            ->order($orderby)
            ->limit($start, $end)
            ->select();
    }

    /**
     * [addArticleVedioLikesById 通过ID增加喜欢量]
     * @param [type] $id [description]
     */
    public function addArticleVedioLikesById($id)
    {
        if (!empty($id)) {
            return M('article_vedio')->where(array('id' => $id))->setInc('likes');
        }
        return false;
    }

    /**
     * [addArticleVedioPvById 通过ID增加PV]
     * @param [type] $id [description]
     */
    public function addArticleVedioPvById($id)
    {
        if (!empty($id)) {
            return M('article_vedio')->where(array('id' => $id))->setInc('pv');
        }
        return false;
    }

    /**
     * 获取最新的视频
     * @return [type] [description]
     */
    public function getTopNewVideoInfo($type = "")
    {
        if (!empty($type)) {
            $map["type"] = array("EQ", $type);
        }
        return M('article_vedio')->where($map)->field("id,title,time,mobile_url,cover_img,likes")->order("id desc")->find();
    }
}