<?php
namespace User\Controller;
use Common\Model\CommentModel;
use User\Common\Controller\CompanyBaseController;
use Common\Enums\ApiConfig;
class CommentController extends CompanyBaseController{
    public function index(){
        //获取装修公司的基本信息
        $info["user"] = $this->baseInfo;

        $pageIndex = 1;
        $pageCount =10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        //获取用户评论列表
        $comments = $this->getCommentList($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],$pageIndex,$pageCount,true);
        $info["commnets"] = $comments['comment'];
        $info["page"] = $comments['page'];
        //侧边栏
        $this->assign("nav",7);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 回复评论
     * @return [type] [description]
     */
    public function recomment(){
        if($_POST){
            //过滤回复
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $data = array(
                    "commid"=>I("post.id"),
                    "rptxt"=>$filter->filter_recomment(I("post.text")),
                    "userid"=>$_SESSION["u_userInfo"]["id"],
                    "name"=>$_SESSION["u_userInfo"]["name"]
                          );
            //查询是否已经回复了
            $count = D("Reply")->getReplyByCommid(I("post.id"),$_SESSION["u_userInfo"]["id"]);
            if($count > 0){
                //编辑评论
                $i = D("Reply")->editReply(I("post.id"),$_SESSION["u_userInfo"]["id"],$data);
            }else{
                //保存评论
                $data["time"] = time();
                $i = D("Reply")->addReply($data);
            }

            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重试！","status"=>0));
        }else{
            $id = I("get.id");
            //查询该评论信息
            $comment = D("Comment")->getCommentInfoById($id,$_SESSION["u_userInfo"]["id"]);
            if(count($comment) > 0){
                $this->assign("comment",$comment);
                $tmp = $this->fetch("recomment");
                $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"该评论不是您公司的,无权评论","status"=>0));
        }
    }

    /**
     * 删除评论回复
     * @return [type] [description]
     */
    public function delcomment(){
        if($_POST){
            //编辑评论
            $i = D("Reply")->delReply(I("post.id"),$_SESSION["u_userInfo"]["id"]);
            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重试！","status"=>0));
        }
    }

    /**
     * 推荐/取消 评论
     * @return [type] [description]
     */
    public function recommend(){
        if($_POST){
            //推荐评论
            $info = D("Comment")->getCommentInfoById(I("post.id"),$_SESSION["u_userInfo"]["id"]);
            //不存在就提示错误
            if (!$info) {
                $this->ajaxReturn(array("data" => "", "info" => "操作失败,请刷新重试！", "status" => 0));
            }
            $i = D("Comment")->recommendComment(I("post.id"), $_SESSION["u_userInfo"]["id"], ['company_recommend' => 2, 'update_time' => time()]);
        } else {
            //取消推荐
            $info = D("Comment")->getCommentInfoById(I("get.id"), $_SESSION["u_userInfo"]["id"]);
            //不存在就提示错误
            if (!$info) {
                $this->ajaxReturn(array("data" => "", "info" => "操作失败,请刷新重试！", "status" => 0));
            }
            $i = D("Comment")->recommendComment(I("get.id"), $_SESSION["u_userInfo"]["id"], ['company_recommend' => 1, 'update_time' => time()]);
        }
        if ($i !== false) {
            $this->ajaxReturn(array("data" => "", "info" => "", "status" => 1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重试！","status"=>0));
    }

    private function getCommentList($comid,$cs,$pageIndex,$pageCount,$reply)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Comment")->getCommentByComIdCount($comid,$cs);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list = D("Comment")->getCommentByComId($comid,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$reply);
            foreach ($list as $key => $val){
                $list[$key]['text'] = mbstr($val['text'],0,60);
            }
            return array("comment"=>$list,"page"=>$pageTmp);
        }
        return null;
    }


    /**
     * 获取评论的内容和评论图片列表
     */
     public function getCommentContengAndImgsById()
     {
         $model = new CommentModel();
         $comment_id = I('get.id');
         $company_id = session("u_userInfo.id");
         if($comment_id){
             $comment_info = $model->getCommentInfo($comment_id,$company_id);

             $imgs= $model->getCommentImgsById($comment_id,$company_id);
             $imglist = [];
             $i=0;
             if($imgs){
                 foreach($imgs as $key => $val){
                     if($val['imgurl']){
                         $imglist[$i++] = changeImgUrl($val['imgurl'],2);
                     }
                 }
                 $comment_info['imgcount'] = count($imglist);
                 $comment_info['imglist'] = $imglist;
             }else{
                 $comment_info['imgcount'] = 0;
                 $comment_info['imglist'] = [];
             }
             $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_SUCCESS,"error_msg"=>"获取成功","data"=>$comment_info));
         }else{
             $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_NODATA,"error_msg"=>"未获取到评价id","data"=>""));
         }

     }


}