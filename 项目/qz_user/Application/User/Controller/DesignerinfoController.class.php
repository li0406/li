<?php
/**
 * 设计师信息页面控制器
 */
namespace User\Controller;
use User\Common\Controller\DesignerBaseController;
class DesignerinfoController extends DesignerBaseController{
    public function index(){
        if($_POST){
            $logo = I("post.logo");
            if(check_imgPath($logo)){
                $id = I("post.id");
                $fengge = mb_substr(I("post.fengge"),0,-1,"utf-8");
                $lingyu = mb_substr(I("post.lingyu"),0,-1,"utf-8");
                $userData = array(
                        "sex"=>I("post.sex"),
                        "name"=>I("post.name"),
                        "logo"=>$logo,
                        "tel"=>I("post.tel"),
                        "mail"=>I("post.mail"),
                        "qq"=>I("post.qq"),
                        "qx"=>I("post.qx")
                                  );
                $detailsData = array(
                        "jobtime"=>I("post.jobtime"),
                        "fengge"=>$fengge,
                        "lingyu"=>$lingyu,
                        "linian"=>I("post.linian"),
                        "text"=>I("post.text"),
                        "school"=>I("post.school"),
                        "cost"=>I("post.cost"),
                        // "cases"=>I("post.case"),
                        "register_time"=>time(),
                                     );
                $userModel = D("User");
                $id = I("post.id");
                $userModel->edtiUserInfo($id,$userData);
                $result = D("Userdes")->editDes($id,$detailsData);

                if($result !== false){
                    //导入扩展文件
                    import('Library.Org.Util.App');
                    $app = new \App();
                    //记录日志
                    $data = array(
                      "username"=>$_SESSION["u_userInfo"]["name"],
                      "userid"=>$_SESSION["u_userInfo"]["id"],
                      "ip"=>$app->get_client_ip(),
                      "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                      "info"=>"用户编辑个人信息 成功",
                      "time"=>date("Y-m-d H:i:s"),
                      "action"=>CONTROLLER_NAME."/".ACTION_NAME
                    );
                    D("Loguser")->addLog($data);
                    $userModel->commit();
                    //删除设计师的blog缓存
                    //S('Cache:'.$_SESSION["u_userInfo"]["bm"].$id.'blog',null);
                    $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                }else{
                    $userModel->rollback();
                    $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
                }
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
        }else{
            $info["user"] = $this->baseInfo;
            //从业时间
            $jobtime = array("应届","一年","二年","三年~五年","五年~八年","八年~十年","十年以上");
            $info["jobtime"] = $jobtime;
            //设计师风格
            $fg = D("Fengge")->getfg();
            $info["fengge"] = $fg;
            //擅长领域
            $lingyu = array("住宅公寓","写字楼","别墅","专卖展示店","酒店宾馆","餐饮酒吧","歌舞迪","其他");
            $info["lingyu"] = $lingyu;
            //获取城市信息
            $citys = D("Area")->getCityArray($_SESSION["u_userInfo"]["cs"]);
            $citys["shen"] = $citys["shen"][0];
            $citys["shi"] = $citys["shi"][$_SESSION["u_userInfo"]["cs"]];
            $info["citys"] = $citys;
            //查询设计师的个人信息
            $designer = D("User")->getSingeleDesInfoById($_SESSION["u_userInfo"]["id"]);
            $info["designer"] = $designer;
            $this->assign("info",$info);
            //侧边栏
             $this->assign("nav",1);
            $this->display();
        }
    }

    public function sendsms()
    {
        if (IS_POST) {
            if (S("Cache:Geetest:".session_id()) == 1) {
                $_POST['tel'] =  $this->baseInfo["tel_safe"];
                R("Common/Sms/sendsms");
                die();
            }
        }
    }

    /**
     * 邮件发送
     * @return [type] [description]
     */
    public function sendemail(){
        if(IS_POST){
            if (S("Cache:Geetest:".session_id()) == 1) {
                S("Cache:Geetest:".session_id(),null);
                $_POST['email'] =  $this->baseInfo["mail_safe"];
                R("Common/Sms/sendemail");
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"验证错误！","status"=>9));
        die();
    }

}