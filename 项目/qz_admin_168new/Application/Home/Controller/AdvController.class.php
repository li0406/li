<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  广告辅助
*/
class AdvController extends HomeBaseController
{
    public function index() {
        //获取所有城市
        $citys = R("Api/getAllCityInfo");
        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs",$cs);
        }

        if (I("get.query") !== "") {
            $query = I("get.query");
            $this->assign("query",$query);
        }

        //获取辅助设计需求列表
        $list = $this->getAdvList($cs,$query);
        $this->assign("list",$list);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * 获取广告的辅助信息
     * @return [type] [description]
     */
    public function information() {
        if ($_POST) {
            $id = I("post.id");
            $info = D("AdvCompanyDesigner")->getAdvInfo($id);
            $this->assign("info",$info);
            $tmp = $this->fetch("info");
            $this->ajaxReturn(array("data"=>$tmp,"status"=>1));
        }
    }

    /**
     * 接受广告
     * @return [type] [description]
     */
    public function accept() {
        if ($_POST) {
            $id = I("post.id");
            $info = D("AdvCompanyDesigner")->getAdvInfo($id);
            if (count($info) > 0) {
                if ($info["status"] == 1) {
                    $this->ajaxReturn(array("info"=>"该广告已经在设计中,请刷新页面...","status"=>0));
                }

                $data = array(
                    "op_uname" => $this->User["name"],
                    "op_uid" => $this->User["id"],
                    "status" => 1
                              );
                $i = D("AdvCompanyDesigner")->editAdvInfo($id,$data);
                if ($i !== false) {
                    //添加操作日志
                    $logData = array(
                                    "remark"=>"接受装修公司广告【".$id."】",
                                    "action_id"=>$id,
                                    "info"=>$_POST,
                                    "logtype"=>"advaccept"
                                     );
                    D('LogAdmin')->addLog($logData);
                    $this->ajaxReturn(array("status"=>1));
                }
            }
        }
    }

    /**
     * 上传图片
     * @return [type] [description]
     */
    public function upload() {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "url" => I("post.img"),
                "value" => I("post.value"),
                "status" => 2
                          );
            //修改图片状态
            $i = D("AdvCompanyDesigner")->editAdvInfo($id,$data);
            if ($i !== false) {
                //推送给装修公司,改变辅助设计状态
                $data = array(
                    "assistance" => 2
                          );
                $i = D("AdvCompany")->editAdv($id,$data);
                if ($i !== false) {
                    $info = D("AdvCompany")->findAdvInfo($id);
                    //发送站内信
                    $message = "尊敬的用户您好，您委托设计师设计的广告图片已经设计完成，可前往【广告管理】中查看";
                    R("Home/Api/sendMessage",array("广告辅助设计通知",$message,3,$info["company_id"]));
                    //添加操作日志
                    $logData = array(
                                    "remark"=>"上传装修公司广告【".$id."】",
                                    "action_id"=>$id,
                                    "info"=>$_POST,
                                    "logtype"=>"upload_adv"
                                     );
                    D('LogAdmin')->addLog($logData);
                    $this->ajaxReturn(array("status"=>1));
                }
            }
        }
    }

    /**
     * 获取辅助设计广告列表
     * @return [type] [description]
     */
    private function getAdvList($cs,$query) {
        $count = D("AdvCompanyDesigner")->getAdvListCount();
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $list = D("AdvCompanyDesigner")->getAdvList($p->firstRow,$p->listRows,$cs,$query);

            return array("page"=>$show,"list"=>$list);
        }
    }
}