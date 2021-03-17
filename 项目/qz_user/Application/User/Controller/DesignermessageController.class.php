<?php
/**
 * 设计师系统消息管理
 */
namespace User\Controller;
use User\Common\Controller\DesignerBaseController;
class DesignermessageController extends DesignerBaseController{
    public function index(){
        //侧边栏
        $this->assign("nav",8);
        //获取基本信息
        $info["user"] = $this->baseInfo;
        if(I("get.id") !== ""){
            $id = I("get.id");
            $notice = D("Usersystemnotice")->getNoticeInfoById($id,session("u_userInfo.id"));

            if(count($notice) > 0){
                if(!$notice["isread"]){
                    //添加阅读标记
                    D("Usersystemnotice")->setRead($id,session("u_userInfo.id"));
                }
            }
            $this->assign("info",$info);
            $this->assign("back","/desmessage/");
            $this->assign("notice",$notice);
            $this->display("Message/message");
            die();
        }else{
            //获取消息列表
            $pageIndex = 1;
            $pageCount = 10;

            if (I("get.p") !== "") {
                $pageIndex = I("get.p");
            }

            if(I("get.isread") !== ""){
                $isread = 1;
            }

            $notices = $this->getMyNotice(session("u_userInfo.id"),$isread,$pageIndex,$pageCount);
            $info["notices"] = $notices["notices"];
            $info["page"] = $notices["page"];

            $this->assign("info",$info);
            $this->display();
        }
    }

    /**
     * 删除站内信
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function deldesmessage()
    {
        if ($_POST) {
            $id = I("post.id");
            if (!is_array($id)) {
                //单个ID
                $ids[] = $id;
            } else {
                //批量删除
                $ids = $id;
            }
            unset($id);
            $status = 0;
            $ids = array_filter($ids);

            if (count($ids) > 0) {
                //查询是否是自己的站内信
                $count = D("Usersystemnotice")->findMyNoticeCount($ids,session("u_userInfo.id"));
                if ($count == count($ids)) {
                    //删除选择的站内信
                    $i = D("Usersystemnotice")->delNotice($ids,session("u_userInfo.id"));

                    if ($i !== false) {
                        $status = 1;
                    }

                } else {
                    $errMsg = "您不能删除不是您的站内信！";
                }
            } else  {
                $errMsg = "没有相关的站内信信息";
            }

            $this->ajaxReturn(array("info"=>$errMsg, "status"=>$status));
        }
    }

    private function getMyNotice($id,$isread,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Usersystemnotice")->getSystemNoticeCount($id,$isread);

        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list = D("Usersystemnotice")->getSystemNotice($id,$isread,($page->pageIndex-1)*$pageCount,$pageCount);

            return array("notices"=>$list,"page"=>$pageTmp);
        }
        return null;
    }
}