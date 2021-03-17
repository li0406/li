<?php
namespace User\Controller;
use User\Common\Controller\CompanyBaseController;
class TeamController extends CompanyBaseController{
    public function index(){
        $info["user"] = $this->baseInfo;
        //获取公司设计师列表
        $pageIndex =1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $designers = $this->getDesignerList($_SESSION["u_userInfo"]["id"],$pageIndex,$pageCount);
        $info["designers"] = $designers["designers"];
        $info["page"] = $designers["page"];

        //侧边栏
        $this->assign("nav",4);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 添加设计师
     * @return [type] [description]
     */
    public function teamup()
    {
        if (IS_POST) {
            $logo = I("post.logo");
            if (check_imgPath($logo)) {
                //导入过滤类 过滤基本信息
                import('Library.Org.Util.Fiftercontact');//导入过滤类
                $filter = new \Fiftercontact();//实例化过滤类

                $fengge = mb_substr(I("post.fengge"), 0, -1, "utf-8");
                $lingyu = mb_substr(I("post.lingyu"), 0, -1, "utf-8");
                $userData = array(
                    "classid" => 2,
                    "name" => $filter->filter_common(I("post.name"), array("Sbc2Dbc", "filter_script", "filter_space", array("filter_sensitive_words", array(2, 3, 5)), "filter_tel", "filter_url", "filter_qq")),
                    "user" => I("post.user"),
                    "pass" => I("post.password"),
                    "sex" => I("post.sex"),
                    "logo" => $logo,
                    "cs" => $_SESSION["u_userInfo"]["cs"],
                    "tel" => I("post.tel"),
                    "mail" => I("post.mail"),
                    "qq" => I("post.qq"),
                    "register_time"=>time()
                );
                $detailsData = array(
                    "jobtime" => I("post.jobtime"),
                    "fengge" => $fengge,
                    "lingyu" => $lingyu,
                    "linian" => $filter->filter_common(I("post.linian"), array("Sbc2Dbc", "filter_script", "filter_space", array("filter_sensitive_words", array(2, 3, 5)), "filter_tel", "filter_url", "filter_qq")),
                    "text" => $filter->filter_common(I("post.text"), array("Sbc2Dbc", "filter_script", "filter_space", array("filter_sensitive_words", array(2, 3, 5)), "filter_tel", "filter_url", "filter_qq")),
                    "school" => $filter->filter_common(I("post.school"), array("Sbc2Dbc", "filter_script", "filter_space", array("filter_sensitive_words", array(2, 3, 5)), "filter_tel", "filter_url", "filter_qq")),
                    "cost" => I("post.cost"),
                    "cases" => $filter->filter_common(I("post.case"), array("Sbc2Dbc", "filter_script", "filter_space", array("filter_sensitive_words", array(2, 3, 5)), "filter_tel", "filter_url", "filter_qq")),
                    "register_time" => time(),
                );
                $teamData = array(
                    "zw" => I("post.zw") == "" ? "设计师" : I("post.zw"),
                    "xs" => I("post.xs"),
                    "px" => I("post.px")
                );
                $userModel = D("User");
                //$userModel->startTrans();
                //关掉事务，主从延迟可能会出现问题
                $msg = "操作失败！";
                if (I("post.id") !== "") {
                    unset($userData["user"]);
                    unset($userData["pass"]);
                    //编辑用户
                    $id = I("post.id");
                    $userModel->edtiUserInfo($id, $userData);
                    D("Userdes")->editDes($id, $detailsData);
                    $result = D("Team")->editTeam($id, $_SESSION["u_userInfo"]["id"], $teamData);
                    $msg = "用户编辑设计师【" . I("post.name") . "】 成功";
                } else {
                    //新增用户
                    if ($userModel->create($userData, 4)) {
                        $userData["pass"] = md5($userData["pass"]);
                        $i = $userModel->addUser($userData);
                        $teamData["comid"] = $_SESSION["u_userInfo"]["id"];
                        $teamData["userid"] = $i;
                        $teamData["zt"] = 2;
                        $detailsData["userid"] = $i;
                        D("Userdes")->addDes($detailsData);
                        $result = D("Team")->addTeam($teamData);
                        $msg = "用户添加设计师【" . I("post.name") . "】 成功";
                    } else {
                        $result = false;
                        $msg = $userModel->getError();
                    }
                }
                if ($result !== false) {
                    //导入扩展文件
                    import('Library.Org.Util.App');
                    $app = new \App();
                    //记录日志
                    $data = array(
                        "username" => $_SESSION["u_userInfo"]["name"],
                        "userid" => $_SESSION["u_userInfo"]["id"],
                        "ip" => $app->get_client_ip(),
                        "user_agent" => $_SERVER["HTTP_USER_AGENT"],
                        "info" => $msg,
                        "time" => date("Y-m-d H:i:s"),
                        "action" => CONTROLLER_NAME . "/" . ACTION_NAME
                    );
                    D("Loguser")->addLog($data);

                    //更新最后操作时间（op_info_last_time字段）
                    $changedata = [];
                    $changedata['op_info_last_time'] = time();
                    D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                    //更新设计师数据
                    $comid = $_SESSION["u_userInfo"]["id"];
                    $tempdata['team_count'] = M('team')->where(array('zt' => '2', 'comid' => $comid))->count();
                    M("user_company")->where(array('userid' => $comid))->save($tempdata);

                    //$userModel->commit();
                    $this->ajaxReturn(array("data" => "", "info" => "", "status" => 1));
                } else {
                    //$userModel->rollback();
                    $this->ajaxReturn(array("data" => "", "info" => $msg, "status" => 0));
                }
            }
            $this->ajaxReturn(array("data" => "", "info" => "操作失败！", "status" => 0));
        } else {
            if (I("get.id") !== "") {
                //编辑用户资料
                $designer = D("User")->getSingeleDesInfoById(I("get.id"));
                $designer["tel"] = empty($designer["tel"]) ? "" : $designer["tel"];
                $info["designer"] = $designer;
            }

            $info["user"] = $this->baseInfo;
            //获取职位信息
            $zw = array("设计师", "首席设计师", "设计总监");
            $info["zw"] = $zw;

            //设计师风格
            $fg = D("Fengge")->getfg();
            $info["fengge"] = $fg;
            //擅长领域
            $lingyu = array("住宅公寓", "写字楼", "别墅", "专卖展示店", "酒店宾馆", "餐饮酒吧", "歌舞迪", "其他");
            $info["lingyu"] = $lingyu;
            //从业时间
            $jobtime = array("应届", "一年", "二年", "三年~五年", "五年~八年", "八年~十年", "十年以上");
            $info["jobtime"] = $jobtime;
            $this->assign("info", $info);
            //侧边栏
            $this->assign("nav", 4);
            //tab栏
            $this->assign("tabNav", 2);
            $this->display();
        }
    }

    /**
     * 搜索设计师
     * @return [type] [description]
     */
    public function teamsearch(){
        if(I("get.keyword") !== ""){
            $keyword = I("get.keyword");
            $info["keyword"] = $keyword;
        }
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex =I("get.p");
        }
        //查询没有装修公司的设计师
        $designers = $this->getDesignerListNoCompany($_SESSION["u_userInfo"]["cs"],$keyword,$pageIndex,$pageCount);
        $info["designers"] = $designers["designers"];
        $info["page"] = $designers["page"];
        //基本信息
        $info["user"] = $this->baseInfo;
        $this->assign("info",$info);
         //侧边栏
        $this->assign("nav",4);
        //tab栏
        $this->assign("tabNav",1);
        $this->display();
    }

    /**
     * 删除设计师
     * @return [type] [description]
     */
    public function removeDes(){
        if($_POST){
            $id = I("post.id");
            $i = D("Team")->deleteTeam($id,$_SESSION["u_userInfo"]["id"]);
            if($i !== false){
                //更新设计师数据
                $comid = $_SESSION["u_userInfo"]["id"];
                $data['team_count'] = M('team')->where(array('zt' => '2','comid' => $comid))->count();
                M("user_company")->where(array('userid' => $comid))->save($data);
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"无权删除该设计师,删除失败！","status"=>"0"));
        }
    }

    /**
     * 取消邀请设计师
     * @return [type] [description]
     */
    public function uninvite(){
        $id = I("post.id");
        $i = D("Team")->deleteTeam($id,$_SESSION["u_userInfo"]["id"],$data);
        if($i !== false){
            $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"邀请设计师失败！","status"=>"0"));
    }

    /**
     * 邀请设计师
     * @return [type] [description]
     */
    public function invite(){
        if($_POST){
            $id = I("post.id");
            //查询该设计师是否被邀请过
            $count =  D("Team")->getTeamInfo($id,$_SESSION["u_userInfo"]["id"]);
            if($count > 0){
                //邀请过修改状态
                $data = array(
                    "zt"=>0,
                    "px"=>0,
                    "zw"=>"设计师"
                          );
                $i = D("Team")->editTeam($id,$_SESSION["u_userInfo"]["id"],$data);
            }else{
                //未邀请过,新增
                $data = array(
                    "userid"=>$id,
                    "comid"=>$_SESSION["u_userInfo"]["id"],
                    "zt"=>0
                          );
                $i = D("Team")->addTeam($data);

            }
            if($i !== false){
                //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                //记录日志
                $data = array(
                  "username"=>$_SESSION["u_userInfo"]["name"],
                  "userid"=>$_SESSION["u_userInfo"]["id"],
                  "ip"=>$app->get_client_ip(),
                  "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                  "info"=>"用户邀请设计师【设计师ID：".$id."】 成功",
                  "time"=>date("Y-m-d H:i:s"),
                  "action"=>CONTROLLER_NAME."/".ACTION_NAME
                );
                D("Loguser")->addLog($data);
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"邀请设计师失败！","status"=>"0"));
    }

    /**
     * 查询当前城市没有装修公司的设计师
     * @return [type] [description]
     */
    private function getDesignerListNoCompany($cs,$name,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("User")->getDesignerListNoCompanyCount($cs,$name);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list = D("User")->getDesignerListNoCompany($cs,$name,($page->pageIndex-1)*$pageCount,$pageCount);

            return array("designers"=>$list,"page"=>$pageTmp);
        }
        return null;
    }

    private function getDesignerList($comid,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("User")->getTeamDesignerListCount($comid);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list = D("User")->getTeamDesignerList($comid,"","",($page->pageIndex-1)*$pageCount,$pageCount);

            return array("designers"=>$list,"page"=>$pageTmp);
        }
        return null;
    }



     /**
     * 获取装修公司基本信息
     * @param  [type] $comid [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    private function getCompanyInfoByAdmin($comid,$cs){
        $user =  D("User")->getCompanyInfoByAdmin($comid,$cs);
        return $user;
    }
}