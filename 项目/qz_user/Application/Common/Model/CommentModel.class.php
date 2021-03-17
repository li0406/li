<?php
/**
 * 业主点评表 Comment
 */
namespace Common\Model;
use Think\Model;
class CommentModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * [getRegisterCount 获取业主评论数量]
     * @return [type]      [description]
    */
    public function getCommentCount($cs){
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        return M("comment")->where($map)->count();
    }

    /**
     * 获取最新的业主点评
     * @param  integer $limit     [description]
     * @param  string  $city      [城市编号]
     * @param  string  $userCount [城市会员数量]
     * @return [type]             [description]
     */
    public function getNewComment($limit = 10,$city = '',$userCount = 0){
        $map = array(
                "a.recommend" =>array("EQ",1),
                "b.classid"=>array("EQ","1"),
                "c.on"=>array("EQ",2),
                "e.fake"=>array("EQ",0)
                    );
        if(!empty($city)){
            $map["a.cs"] = array("EQ",$city);
        }
        $buildSql = M("comment")->where($map)->alias("a")
                                 ->join("INNER JOIN qz_user as c on a.comid = c.id")
                                 ->join("INNER JOIN qz_user_company as e on e.userid = c.id")
                                 ->join("INNER JOIN qz_quyu as d on d.cid = c.cs")
                                 ->join("INNER JOIN qz_user as b on a.userid = b.id")
                                 ->order("a.time desc")
                                 ->field("a.*,c.id as cid,b.name as username,b.sex,b.logo,c.jc,c.qc,d.bm")
                                 ->buildSql();

        //根据会员数量,大于5家的取每家最新的
        switch ($userCount) {
            default:
                return   M("comment")->table($buildSql)->alias("t")->limit($limit)->group("t.comid")->order("t.time desc")->select();
                break;
            case "1":
                //1家会员取1家会员的全部评论
                return  M("comment")->table($buildSql)->alias("t")->limit($limit)->order("t.time desc")->select();
                break;
            case "2":
            case "3":
            case "4":
                $result = M("comment")->table($buildSql)->alias("t")->limit($userCount)->group("t.comid")->order("t.time desc")->select();
                $offset = $limit - $userCount;//评论数差
                foreach ($result as $key => $value) {
                    $ids[] = $value['id'];
                }
                if(count($ids) > 0){
                    $map = array(
                            "a.id"=>array("NOT IN",$ids),
                            "a.recommend" =>array("EQ",1),
                            "b.classid"=>array("EQ","1"),
                            "c.on"=>array("EQ",2),
                            "e.fake"=>array("EQ",0)
                                 );
                    if(!empty($city)){
                        $map["a.cs"] = array("EQ",$city);
                    }
                    $buildSql = M("comment")->where($map)->alias("a")
                                     ->join("INNER JOIN qz_user as c on a.comid = c.id")
                                     ->join("INNER JOIN qz_user_company as e on e.userid = c.id")
                                     ->join("INNER JOIN qz_quyu as d on d.cid = c.cs")
                                     ->join("INNER JOIN qz_user as b on a.userid = b.id")
                                     ->order("a.time desc")
                                     ->field("a.*,c.id as cid,b.name as username,b.sex,b.logo,c.jc,c.qc,d.bm")
                                     ->buildSql();

                    $sub = M("comment")->table($buildSql)->alias("t")->limit($offset)->select();
                    $result = array_merge($result,$sub);
                    return $result;
                }
                break;
        }
    }

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
                                ->count();
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
        $buildSql = M("comment")->where($map)->alias("a")
                                ->order("company_recommend desc,update_time desc,time desc")
                                ->limit($pageIndex.",".$pageCount)
                                ->buildSql();

//        $buildSql = M("comment")->table($buildSql)->alias("t")
//                           ->join("LEFT JOIN qz_user as b on t.userid = b.id")
//                           ->field("t.*,b.name as bname,b.logo")
//                           ->buildSql();

        if($reply){
            return M("comment")->table($buildSql)->alias("t1")
                               ->join("LEFT JOIN qz_reply as c on c.commid = t1.id")
                               ->field("t1.*,c.rptxt")
                               ->select();
        }else{
            return M("comment")->table($buildSql)->alias("t1")->select();
        }
    }

    /**
     * 保存评论
     * @param [type] $data [description]
     */
    public function setComment($data){
        return M("comment")->add($data);
    }


    /**
     * 获取评论信息
     * @param  [type] $id    [评论编号]
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function getCommentInfoById($id,$comid){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.comid"=>array("EQ",$comid)
                     );
        return M("comment")->where($map)->alias("a")
                           ->join("LEFT JOIN qz_reply as c on c.commid = a.id")
                           ->field("a.id,a.name,a.sj,a.sg,a.fw,a.count,c.rptxt")
                           ->find();
    }

     /**
     * 获取业主的评论信息的数量
     * @param  [type] $id    [用户编号]
     * @return [type] [description]
     */
    public function getCommentInfoByUserIdCount($id){
        $map = array(
                "userid"=>array("EQ",$id)
                     );
        //1.查询相关的评论信息
       return M("comment")->where($map)
                          ->count();
    }

    /**
     * 获取业主的评论信息
     * @param  [type] $id    [用户编号]
     * @return [type] [description]
     */
    public function getCommentInfoByUserId($id,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "userid"=>array("EQ",$id)
                     );
        //1.查询相关的评论信息
        $buildSql = M("comment")->where($map)
                                ->order("id desc")
                                ->limit($pageIndex.",".$pageCount)
                                ->buildSql();
        //2. 查询和评论相关的其他信息
        return M("comment")->table($buildSql)->alias("a")
                           ->join("INNER JOIN qz_user as u on u.id = a.comid")
                           ->join("INNER JOIN qz_quyu as b on b.cid = a.cs")
                           ->join("LEFT JOIN qz_reply as c on c.commid = a.id")
                           ->field("a.id,a.name,a.text,a.time,a.sj,a.sg,a.fw,a.count,c.rptxt,u.qc,b.bm,u.id as comid")
                           ->select();
    }

    /**
     * 编辑评论
     * @return [type] [description]
     */
    public function editComment($id,$userid,$data){
        $map = array(
                "id"=>array("EQ",$id),
                "userid"=>array("EQ",$userid)
                     );
        return M("comment")->where($map)->save($data);
    }

    /**
     * 推荐/取消 评论
     * @param [type] $data [description]
     */
    public function recommendComment($id, $user, $data)
    {
        $map = [
            'id' => $id,
            'comid' => $user
        ];
        return M("comment")->where($map)->save($data);
    }


    /**
     * 根据评论id获取评论图片
     * @param $id
     * @param $comid
     * @return mixed
     */
    public function getCommentImgsById($id,$comid){
        $map = array(
            "a.id"=>array("EQ",$id),
            "a.comid"=>array("EQ",$comid)
        );
        return M("comment")->where($map)->alias("a")
            ->join("LEFT JOIN qz_comment_img as img on img.commentid = a.id")
            ->field("img.imgurl")
            ->select();
    }

    public function getCommentInfo($id,$comid)
    {
        $map = array(
            "a.id"=>array("EQ",$id),
            "a.comid"=>array("EQ",$comid)
        );
        return M("comment")->where($map)->alias("a")
            ->field("a.id,a.name,a.text content,a.time")
            ->find();

    }
}