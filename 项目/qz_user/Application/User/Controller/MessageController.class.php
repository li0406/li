<?php
namespace User\Controller;
use User\Common\Controller\CompanyBaseController;
class MessageController extends CompanyBaseController{
    public function index(){
        //侧边栏
        $this->assign("nav",2);
        //获取装修公司的基本信息
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
            $this->assign("notice",$notice);
            $this->display("message");
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

    public function history(){
        $this->_error();
        // //获取装修公司的基本信息
        // $info["user"] = $this->baseInfo;
        // //搜索操作记录
        //  //默认1个月
        // $begin = date("Y-m-d H:i:s",strtotime("-1 month"));
        // $end = date("Y-m-d H:i:s",time());
        // if(I("get.begin") !== ""){
        //     $begin = I("get.begin");
        //     $info["begin"] = $begin;
        // }
        // if(I("get.end") !== ""){
        //     $end = I("get.end");
        //     $info["end"] = $end;
        //     $end = date("Y-m-d H:i:s",strtotime("+1 day",strtotime($end)));
        // }

        // //限定逻辑 筛选时间，起止时间最多只能选一个月
        // if (
        //     (strtotime($end)- strtotime($begin)) > 3600 * 24 * 31
        //     ) {
        //     $this->_error();
        // }

        // $logs = $this->getLogs($_SESSION["u_userInfo"]["id"],$begin,$end);
        // $info["logs"] = $logs;
        // $this->assign("info",$info);
        // //侧边栏
        // $this->assign("nav",2);
        // //侧边栏
        // $this->assign("tabNav",1);
        // $this->display();
    }

    /**
     * 删除站内信
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delmessage()
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

    private function getLogs($uid,$begin,$end){
        $result = D("Loguser")->getLogList($uid,$begin,$end);
        $logs = array();
        foreach ($result as $key => $value) {
            $time = date("Y-m-d",strtotime($value["time"]));
            if(!array_key_exists($time, $logs)){
                $logs[$time]["date"] = strtotime($value["time"]);
            }
            $value["time"] = strtotime($value["time"]);
            $logs[$time]["child"][] = $value;
        }
        return $logs;
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