<?php
/**
 * 设计师发布/修改案例控制器
 */
namespace User\Controller;
use User\Common\Controller\DesignerBaseController;
class DesignercaseController extends DesignerBaseController{
    public function _initialize(){
        parent::_initialize();
        //侧边栏
        $this->assign("nav",3);
    }

    public function index(){
        $info["user"]= $this->baseInfo;
        //获取设计师的案例列表
        $pageIndex = 1;
        $pageCount = 9;
        if(I("get.p") !== ""){
            $pageIndex =  I("get.p");
        }
        $cases = $this->getCasesList($_SESSION["u_userInfo"]["id"],$pageIndex,$pageCount);
        $info["cases"] = $cases["cases"];
        $info["page"] = $cases["page"];
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 删除设计师案例
     * @return [type] [description]
     */
    public function deldescase(){
        if($_POST){
            $id = I("post.id");
            //查询该案例是否是该设计师的作品
            $case = D("Cases")->getSingleById($id,"",$_SESSION["u_userInfo"]["id"]);
            if(count($case) > 0){
                $data = array(
                        "isdelete"=>3,
                        "last_opid"=>$_SESSION["u_userInfo"]["id"],
                        "update_time"=>time()
                              );
                $i = D("Cases")->editCase($id,$data);
                if($i !== false){
                    D('cases')->relationXiaoQuDel($id);
                    $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                }
                $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"该案例不是您发布的,无权删除","status"=>0));
        }
    }

    /**
     * 设计师发布/修改案例
     * @return [type] [description]
     */
    public function descaseup(){
        if($_POST){
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            //获取新添加的图片信息
            $newData = json_decode($_POST["newData"],true);
            $flag = true;
            $data = array(
                    "classid"=>I("post.class"),
                    "qx"=>I("post.qx"),
                    "cs"=>I("post.cs"),
                    "title"=> $filter->filter_common(remove_xss(I("post.xiaoqu")),array("Sbc2Dbc","filter_script","filter_link",array("filter_sensitive_words",array(2,3,5)),"filter_400","filter_mobile","filter_tel","filter_url")),
                    "huxing"=>I("post.huxing"),
                    "mianji"=>remove_xss(I("post.mianji")),
                    // "fangshi"=>I("post.fangshi"),
                    "fengge"=>I("post.fengge"),
                    "zaojia"=>I("post.zaojia"),
                    "start"=>I("post.start"),
                    "end"=>I("post.end"),
                    "userid"=>$_SESSION["u_userInfo"]["id"],
                    "text"=>$filter->filter_common(remove_xss(I("post.text")),array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_400","filter_mobile","filter_tel","filter_url")),
                    "shi"=>I("post.shi"),
                    "ting"=>I("post.ting"),
                    "wei"=>I("post.wei"),
                    "type"=>"1",
                    "leixing"=>I("post.leixing")
                          );
            //如果是公装案例,去除户型值
            if($data["classid"] == 2){
                $data["huxing"] ="";
            }else{
                //去除类型值
                $data["leixing"] ="";
            }
            $case = D("Cases");
            $case->startTrans();
            if(I("post.caseid") !== ""){
                //编辑提交
                $caseid = I("post.caseid");
                $imgs = json_decode($_POST["imgs"],true);
                //修改原有图片的排序状态
                foreach ($imgs as $key => $value) {
                    $subData = array(
                        "px"=>$value["index"],
                        "img_on"=>$value["on"]
                                 );
                    $i = D("Caseimg")->editCase($value["id"],$subData);
                    if($i === false){
                        $flag = false;
                    }
                }
                //编辑时间
                $data["update_time"] = time();
                //最后编辑人
                $data["last_opid"] = $_SESSION["u_userInfo"]["id"];
                $case->editCase($caseid,$data);
                D('cases')->relationXiaoQuEdit($caseid,I("post.xiaoquid"));
                //操作日志
                $logData = array(
                        "company_id"=>$company["id"],
                        "company_name"=>$company["jc"],
                        "case_id"=>$caseid,
                        "case_title"=>I("post.xiaoqu"),
                        "case_city"=>I("post.cs"),
                        "act_time"=>time(),
                        "act_name"=>"update"
                                 );
                $msg = "用户【".$_SESSION["u_userInfo"]["name"]."】编辑案例作品【".$data["title"]."】 成功";
            }else{
                //新增提交
                //查询该用户是否有装修公司
                $team = D("Team")->getUserTeamInfo($_SESSION["u_userInfo"]["id"]);
                $on = 1;
                if(count($team) > 0){
                    //获取装修公司的编号
                    $comid = $team["comid"];
                    //获取装修公司的信息
                    $company = D("User")->getSingleUserInfoById($comid);

                    if( $company["on"] == 2 ){
                        //非会员公司
                        $on = 1;
                    }
                    //装修公司的状态
                    $data["uon"] = $company["on"];
                    //装修公司编号
                    $data["uid"] = $company["id"];
                }
                //上传身份标识
                $data["identity"] = 2;
                //案例审核的状态
                $data["on"] = $on;
                $data["time"] = time();
                //添加数据
                $caseid = $case->addCase($data);
                D('cases')->relationXiaoQuAdd($caseid,I("post.xiaoquid"));
                $msg = "用户【".$_SESSION["u_userInfo"]["name"]."】添加案例作品【".$data["title"]."】 成功";
                if($caseid !== false){
                    //如果有装修公司,先判断案例数是否一致
                    if(!empty($data["uid"])){
                        $casecount = $case->getCasesCount($company["cs"],$company["id"]);
                        //如果两个案例数不一致，实际案例数大于case_count的数时,更新case_count,uptime
                        if($casecount >= $company['case_count']){
                            $subData = array(
                                    "case_count"=>$casecount,
                                    "uptime"=>time()
                                        );
                            D("User")->edtiUserInfo($company["id"],$subData);
                        }
                        //操作日志
                        $logData = array(
                            "company_id"=>$company["id"],
                            "company_name"=>$company["jc"],
                            "case_id"=>$caseid,
                            "case_title"=>I("post.xiaoqu"),
                            "case_city"=>I("post.cs"),
                            "act_time"=>time(),
                            "act_name"=>"add"
                        );
                    }
                }
            }

            //如果有装修公司
            if(!empty($company["id"])){
                //写入日志
                D("Logcase")->addLog($logData);
            }

            if(count($newData) > 0){
                //添加新增的案例图片
                foreach ($newData as $key => $value) {
                    $subData = array(
                            "caseid"=>$caseid,
                            "img"=>$value["img"],
                            "img_path"=>$value["path"],
                            "img_host"=>"qiniu",
                            "img_on"=>$value["on"],
                            "px"=>$value["tabIndex"]
                                     );
                    $i = D("Caseimg")->addImg($subData);
                    if($i === false){
                        $flag = false;
                    }
                }
            }

            if($flag){
                 //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                //写入用户记录日志
                $data = array(
                  "username"=>$_SESSION["u_userInfo"]["name"],
                  "userid"=>$_SESSION["u_userInfo"]["id"],
                  "ip"=>$app->get_client_ip(),
                  "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                  "info"=>$msg,
                  "time"=>date("Y-m-d H:i:s"),
                  "action"=>CONTROLLER_NAME."/".ACTION_NAME
                );
                D("Loguser")->addLog($data);
                $case->commit();
                $this->ajaxReturn(array("data"=>"","info"=>$caseid,"status"=>1));
            }else{
                $case->rollback();
                $this->ajaxReturn(array("data"=>"","info"=>"添加失败","status"=>0));
            }


        }else{
            if(I("get.id") !== ""){
                $id = I("get.id");
                $case = $this->getCaseInfoAndImgs($id,"",true);
                if($case["userid"] == $_SESSION["u_userInfo"]["id"]){
                    $info["case"] = $case;
                }
            }

            $info["user"]= $this->baseInfo;
             //案例类型
            $class = array(
                    array("name"=>"家装案例","value"=>"1"),
                    array("name"=>"公装案例","value"=>"2"),
                    array("name"=>"在建工地","value"=>"3")
                           );
            $info["class"] = $class;
            //区域信息
            //获取当前城市
            $citys = D("Area")->getCityArray($_SESSION["u_userInfo"]["cs"]);
            $citys["shen"] = $citys["shen"][0];
            $citys["shi"] = $citys["shi"][$_SESSION["u_userInfo"]["cs"]];
            $info["citys"] = $citys;
            //获取户型
            $hx = D("Common/Huxing")->gethx();
            $info["huxing"] = $hx;
            //获取室厅卫
            $info["shi"] = array(1,2,3,4,5,6);
            $info["ting"] = array(0,1,2);
            $info["wei"] = array(0,1,2,3);
            //获取装修方式
            $fangshi  =  D("Common/Fangshi")->getfs();
            $info["fangshi"] = $fangshi;
             //获取装修风格列表
            $fg = D("Common/Fengge")->getfg();
            $info["fengge"] = $fg;
            //获取价格列表
            $jiage = D("Common/jiage")->getJiage();
            $info["jiage"] = $jiage;
            //获取设计师列表
            $designers = D('User')->getDesignerNameList($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
            $info["designers"] = $designers;
            //获取装修类型
            $leixing = D("Common/Leixing")->getlx();
            $info["leixing"] = $leixing;

            $this->assign("info",$info);
            $this->display();
        }
    }

    private function getCasesList($id,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //查询是否有关联的装修公司
        $team = D("Team")->getUserTeamInfo($_SESSION["u_userInfo"]["id"]);
        if(count($team) > 0){
            $comid = $team["comid"];
        }
        $count = D("Cases")->getDesinerCaseListCount($id,$comid);

        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $cases = D("Cases")->getDesinerCaseList($id,$comid,($page->pageIndex-1)*$pageCount,$pageCount);

            return array("cases"=>$cases,"page"=>$pageTmp);
        }
        return null;
    }

    private function getCaseInfoAndImgs($id){
        $result = D("Cases")->getCaseInfoAndImgs($id,"",true);
        $cases = array();
        foreach ($result as $key => $value) {
            if($key == 0){
                $cases = $value;
            }
            $img = array(
                    "id" => $value["img_id"],
                    "img"=>$value["img"],
                    "img_host"=>$value["img_host"],
                    "img_path"=>$value["img_path"],
                    "img_on"=>$value["img_on"]
                         );
            $cases["imgs"][] =$img;
        }
        $cases["imgs"] = json_encode($cases["imgs"]);
        return $cases;
    }
}