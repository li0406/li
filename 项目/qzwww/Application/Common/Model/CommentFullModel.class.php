<?php

namespace Common\Model;
use Think\Model;

/**
*
*/
class CommentFullModel extends Model
{
    protected $autoCheckFields = false;
    /**
     * 添加评论
     * @param [type] $data [description]
     */
    public function addComment($data)
    {
        return M("comment_full")->add($data);
    }

    /**
     * 获取评论数量
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getCommentCount($module,$ref_id)
    {
        $map = array(
            "module" => array("EQ",$module),
            "ref_id" => array("EQ",$ref_id)
        );
        return M("comment_full")->where($map)->count();
    }

    /**
     * 获取评论列表数量
     * @param  [type] $module [description]
     * @param  [type] $ref_id [description]
     * @return [type]         [description]
     */
    public function getCommentListCount($module,$ref_id)
    {
        $map = array(
            "module" => array("EQ",$module),
            "ref_id" => array("EQ",$ref_id),
            "reply_id" => array("EQ",0)
        );
        return M("comment_full")->where($map)->count();
    }

    /**
     * 获取评论类表
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $module    [模块名称]
     * @param  [type] $ref_id    [对应的ID]
     * @return [type]            [description]
     */
    public function getCommentList($pageIndex,$pageCount,$module,$ref_id)
    {
        $map = array(
            "module" => array("EQ",$module),
            "ref_id" => array("EQ",$ref_id),
            "reply_id" => array("EQ",0)
        );

        $buildSql = M("comment_full")->where($map)->alias("a")
                                     ->join("join qz_ucenter_profile u on u.uuid = a.userid")
                                     ->field("a.*,u.avatar as logo")
                                     ->limit($pageIndex.",".$pageCount)->order("id desc")->buildSql();

        return  M("comment_full")->table($buildSql)->alias("a")
                                ->join("left join qz_comment_full b on a.id = b.reply_id")
                                ->order("a.id desc,b.id desc")
                                ->field("a.*,b.username reply_name,b.content reply_content,b.likes reply_likes,b.dislike reply_dislike,b.time as reply_time")->select();
    }

    /**
     * 获取最新的评论
     * @param  [type] $module [模块名称]
     * @param  [type] $ref_id [对应的ID]
     * @param  [type] $limit  [description]
     * @return [type]         [description]
     */
    public function getNewCommentsList($module,$ref_id,$limit)
    {
        $map = array(
            "a.module" => array("EQ",$module),
            "a.ref_id" => array("EQ",$ref_id),
            "a.isdelete" => array("EQ",0),
            "a.reply_id" => array("EQ",0)
        );
        $buildSql = M("comment_full")->where($map)->alias("a")
                                     ->join("join qz_ucenter_profile u on u.uuid = a.userid")
                                     ->field("a.*,u.avatar as logo")
                                     ->limit($limit)->order("id desc")->buildSql();

        return  M("comment_full")->table($buildSql)->alias("a")
                                ->join("left join qz_comment_full b on a.id = b.reply_id")
                                ->order("a.id desc,b.id desc")
                                ->field("a.*,b.username reply_name,b.content reply_content,b.likes reply_likes,b.dislike reply_dislike,b.time as reply_time")->select();
    }

    /**
     * 获取回复的数量
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getReplyCount($module,$ref_id,$reply_id)
    {
         $map = array(
            "module" => array("EQ",$module),
            "ref_id" => array("EQ",$ref_id),
            "reply_id" => array("EQ",$reply_id)
        );
        return M("comment_full")->where($map)->count();
    }

    /**
     * 评论顶
     * @param [type] $id [description]
     */
    public function setLikes($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("comment_full")->where($map)->setInc("likes",1);
    }

    /**
     * 评论踩
     * @param [type] $id [description]
     */
    public function setDisLike($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("comment_full")->where($map)->setInc("dislike",1);
    }


    /**
     * 获取评论列表
     * @param  [type] $ref_id [description]
     * @param  [type] $limit  [description]
     * @return [type]         [description]
     */
    public function getNewCommentFullLists($module,$ref_id,$limit)
    {
        $result = $this->getNewCommentsList($module,$ref_id,$limit);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["id"],$list)) {
                $list[$value["id"]] = array(
                    "logo" => $value["logo"],
                    "username" => $value["username"],
                    "time" => $value["time"],
                    "likes" => $value["likes"],
                    "dislike" => $value["dislike"],
                    "id" => $value["id"],
                    "ref_id" => $value["ref_id"],
                    "content" => $value["content"]
                );
            }

            if (!empty($value["reply_name"])) {
                $list[$value["id"]]["reply"][] = array(
                    "username" => $value["reply_name"],
                    "time" => $value["reply_time"],
                    "likes" => $value["reply_likes"],
                    "dislike" => $value["reply_dislike"],
                    "content" => $value["reply_content"]
                );
            }
       }
       return $list;
    }

    /**
     * 获取评论列表
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $module    [description]
     * @param  [type] $ref_id    [description]
     * @return [type]            [description]
     */
    public function getCommentFullLists($pageIndex,$pageCount,$module,$ref_id)
    {
        $count = $this->getCommentListCount($module,$ref_id);
        if ($count > 0) {
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","first","last","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $result = D("CommentFull")->getCommentList(($page->pageIndex-1)*$pageCount,$pageCount,$module,$ref_id);
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]] = array(
                        "logo" => $value["logo"],
                        "username" => $value["username"],
                        "time" => $value["time"],
                        "likes" => $value["likes"],
                        "dislike" => $value["dislike"],
                        "id" => $value["id"],
                        "ref_id" => $value["ref_id"],
                        "content" => $value["content"]
                    );
                }

                if (!empty($value["reply_name"])) {
                    $list[$value["id"]]["reply"][] = array(
                        "username" => $value["reply_name"],
                        "time" => $value["reply_time"],
                        "likes" => $value["reply_likes"],
                        "dislike" => $value["reply_dislike"],
                        "content" => $value["reply_content"]
                    );
                }
           }
           return array("list"=>$list,"page"=>$pageTmp,"count"=>$count);
        }

    }
}