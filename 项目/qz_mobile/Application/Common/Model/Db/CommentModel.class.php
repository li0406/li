<?php
namespace Common\Model\Db;

use Think\Model;

class CommentModel extends Model
{
	protected $autoCheckFields = false;


    /**
     * 获取装修公司评论数
     * @param  string $comid [公司编号]
     * @param  string $cs    [所在城市]
     * @return [type]        [description]
     */
    public function getCommentByComIdCount($comid="",$cs=""){
        $map = array(
            "a.comid"=>array("EQ",$comid),
            "a.cs"=>array("EQ",$cs),
            "a.isveritfy"=>array("EQ",0)
        );
        return M("comment")->where($map)->alias("a")
            ->join("LEFT JOIN qz_ucenter_profile as b on a.userid = b.uuid")
            ->count();
    }

    //获取装修公司评论的step列表
    public function getCommentStepList($comid="",$cs="")
    {
        $map = array(
            "a.comid"=>array("EQ",$comid),
            "a.cs"=>array("EQ",$cs),
            "a.isveritfy"=>array("EQ",0),
            "a.step"=>array("NEQ",''),
        );
        return M("comment")->where($map)->alias("a")
            ->field('a.step')
            ->join("INNER JOIN qz_user as ax on a.userid = ax.id")
            ->group('a.step')
            ->select();

    }

    /**
     *  获取装修公司的评论
     * @param  string  $comid [公司编号]
     * @param  string  $cs    [所在城市]
     * @param  integer $limit [显示数量]
     * @param  boolean $reply [是否显示回复]
     * @return [type]         [description]
     */
    public function getCommentByComId($comid="",$cs="",$pageIndex =0,$pageCount=10,$reply = false)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
            "a.comid"=>array("EQ",$comid),
            "a.cs"=>array("EQ",$cs),
            "a.isveritfy"=>array("EQ",0)
        );

        $buildSql = M("comment")->alias("a")
            ->join("LEFT JOIN qz_ucenter_profile as b on a.userid = b.uuid")
            ->limit($pageIndex.",".$pageCount)
            ->order("company_recommend desc,time desc")
            ->field("a.*,a.name as bname,b.avatar as logo")
            ->where($map)
            ->buildSql();

        if($reply){
            return M("comment")->table($buildSql)->alias("t1")
                ->join("LEFT JOIN qz_reply as c on c.commid = t1.id")
                ->field("t1.*,c.rptxt,c.time as rptxt_time")
                ->select();
        }else{
            return M("comment")->table($buildSql)->alias("t1")->select();
        }
    }

    /*
     * 根据评价id获取评价图片
     * @param  mixed $commentid 评论id
     * @return array
     */
    public function getCommentImgsByCommentid($commentid)
    {
        if($commentid){
            $map = [];
            if (is_array($commentid)) {
                $map['commentid'] = ['IN', $commentid];
            } else {
                $map['commentid'] = ['EQ', $commentid];
            }
            return M("comment_img")
                ->where($map)
                ->field('imgurl')
                ->select();
        }else{
            return [];
        }

    }
}
