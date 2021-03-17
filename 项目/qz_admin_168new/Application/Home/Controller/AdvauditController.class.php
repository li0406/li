<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
* 装修公司广告审核
*/
class AdvauditController extends HomeBaseController
{
    public function index() {
        if(count($this->city) > 0){
            $citys = getUserCitys();
            //获取管辖城市的广告申请列表
            $list = $this->getAuditList($this->city);
            $this->assign("list",$list);
            $this->assign("citys",$citys);
        }
        $this->display();
    }

    public function audit() {
        if ($_POST) {
            $id = I("post.id");
            $info = D("AdvCompanyAudit")->getAuditInfo($id,$this->city);
            if (count($info) > 0) {
                $data = array(
                    "status" => I("post.status"),
                    "uptime" => time(),
                    "op_uid" => $this->User["id"],
                    "op_uname" => $this->User["name"]
                );

                if (I("status") == 2) {
                    $data["remark"] = I("post.remark");
                }
                $i = D("AdvCompanyAudit")->editAudit($id,$data);
                if ($i !== false) {
                    switch ($info["module"]) {
                        case 'home_advbanner':
                            $module = "轮播广告";
                            break;
                        case 'home_bigbanner_a':
                            $module = "通栏A";
                            break;
                        case 'home_bigbanner_b':
                            $module = "通栏B";
                            break;
                    }

                    if (I("status") == 2) {
                        $status = 3;
                        $message = "尊敬的用户您好，您提交的 $module 的广告审核不通过，可前往【广告管理】中查看具体原因";
                    } else {
                        $status = 2;
                        $message = "尊敬的用户您好，您提交的 $module 的广告审核通过并且上线，可前往【广告管理】中查看具体信息";
                    }

                    //修改装修公司广告的审核状态
                    $data = array(
                        "audit" => $status
                    );
                    $i = D("AdvCompany")->editInfo($info['id'],$data);
                    if ($i !== false) {
                        //发送站内信
                        R("Home/Api/sendMessage",array("广告审核结果通知",$message,3,$info["company_id"]));
                        //添加操作日志
                        $logData = array(
                                        "remark"=>"审核了装修公司广告【".$id."】",
                                        "action_id"=>$id,
                                        "info"=>$_POST,
                                        "logtype"=>"audit_adv"
                                         );
                        D('LogAdmin')->addLog($logData);
                        $this->ajaxReturn(array("status"=>1));
                    }
                }
            }
            $this->ajaxReturn(array("info"=>"审核失败！","status"=>0));
        } else {
            if (I("get.id") !== "") {
                $info = D("AdvCompanyAudit")->getAuditInfo(I("get.id"),$this->city);
                if (count($info) > 0) {
                    $this->assign("info",$info);
                    $tmp = $this->fetch("audit");
                    $this->ajaxReturn(array("data"=>$tmp,"status"=>1));
                }
            }
        }
    }

    /**
     * 获取审核列表
     * @return [type] [description]
     */
    private function getAuditList($citys) {
        $count = D("AdvCompanyAudit")->getAuditListCount($citys);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $list = D("AdvCompanyAudit")->getAuditList($citys,($pageIndex-1) * $pageCount,$pageCount);
            foreach ($list as $key => $value) {
                $list[$key]["preview"] = "http://".C("QINIU_DOMAIN")."/".$value["img"];
            }
            return array("page"=>$show,"list"=>$list);
        }
    }

}