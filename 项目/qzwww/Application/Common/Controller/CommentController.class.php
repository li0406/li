<?php
namespace Common\Controller;
use Think\Controller;

/**
 * 评论回复
 */

class CommentController extends Controller
{
    /**
     * 评论回复
     * @return [type] [description]
     */
    public function reply()
    {
        if ($_POST) {
            $user = session("u_userInfo");
            //判断用户是否登陆
            if (!session("?u_userInfo")) {
                //未登录返回status 为2
                $this->ajaxReturn(array("status"=>2));
            }

            // 只有业主可以评论/收藏
            if($_SESSION['u_userInfo']['classid'] != 1){
                $this->ajaxReturn(['data' => '', 'info' => '该账号无操作权限~', 'status' => 0]);
            }

            if (I("post.content") == "") {
                $this->ajaxReturn(array("info"=>"请填写您的评论！","status"=>4));
            }

            $name = $user["name"];
            if ($user["classid"] == 3) {
                $name = $user["jc"];
            }

            //如果有回复ID，查询该回复的楼层
            $floor = 1;
            if (I("post.reply_id") !== "") {
               $count = D("CommentFull")->getReplyCount("wwwarticle",I("post.rel_id"),I("post.reply_id"));
               $floor = $count + 1;
            }

            //过滤回复内容
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $content = $filter->filter_common(I("post.content"),array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));

            $data = array(
                "module" => I("post.module"),
                "userid" => $user["id"],
                "username" => $name,
                "cs" => $user["cs"],
                "ref_id" => I("post.rel_id"),
                "content" => $content,
                "floor" => $floor,
                "reply_id" => I("post.reply_id"),
                "time" => time()
            );

            $i = D("CommentFull")->addComment($data);

            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"回复失败！","status"=>0));
        }
    }

    /**
     * 评论顶
     * @return [type] [description]
     */
    public function replyup()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("CommentFull")->setLikes($id);
            if ($i !== false) {
               $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"","status"=>0));
        }
    }

    /**
     * 评论踩
     * @return [type] [description]
     */
    public function replydown()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("CommentFull")->setDisLike($id);
            if ($i !== false) {
               $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"","status"=>0));
        }
    }
}