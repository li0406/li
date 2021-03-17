<?php
namespace User\Controller;
use User\Common\Controller\UserBaseController;
class MycommentController extends UserBaseController{
    public function index(){
        //获取基本信息
        $info["user"] = $this->baseInfo;
        //获取评论列表
        $pageIndex = 1;
        $pageCount =10;
        if(I("get.p") !== ""){
            $pageIndex =  I("get.p");
        }
        $comment = $this->getMyComment($info["user"]["id"],$pageIndex,$pageCount);
        $info["comments"] = $comment["comments"];
        $info["page"] = $comment["page"];
        $this->assign("info",$info);
        //侧边栏
        $this->assign("nav",3);
        $this->display();
    }

    /**
     * 编辑评论
     * @return [type] [description]
     */
    public function setcomment(){
        if($_POST){
            $id = I("post.id");
            //过滤回复内容
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $content = $filter->filter_text(I("post.content"));
            $data = array(
                    "text"=>$content
                          );
            $i = D("Comment")->editComment($id,$_SESSION["u_userInfo"]["id"],$data);
            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
        }
    }

    /**
     * 获取评论列表
     * @return [type] [description]
     */
    private function getMyComment($id,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Comment")->getCommentInfoByUserIdCount($id);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $comments = D("Comment")->getCommentInfoByUserId($id,($page->pageIndex-1)*$pageCount,$pageCount);

            //过滤回复内容
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $time = time();
            foreach ($comments as $key => $value) {
                $value["rptxt"] = $filter->filter_text($value["rptxt"]);
                $comments[$key]["rptxt"] = $value["rptxt"];
                $comments[$key]["edit"] = false;
                //大于5分钟的评论不允许修改
                if(($time - $value["time"]) <= 300){
                    $comments[$key]["edit"] = true;
                }
            }
            return array("comments"=>$comments,"page"=>$pageTmp);
        }
        return null;
    }
}