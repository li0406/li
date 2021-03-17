<?php
namespace User\Controller;
use User\Common\Controller\DesignerBaseController;
class DesignerteamController extends DesignerBaseController{
    public function _initialize(){
        parent::_initialize();
        //侧边栏
        $this->assign("nav",5);
    }


    public function index(){
        $info["user"] = $this->baseInfo;
        //查询该设计师是否已入住装修公司
        $team = D("Team")->getUserTeamInfo($_SESSION["u_userInfo"]["id"]);
        if(count($team) > 0){
            //已注入状态
            //查询该装修公司的信息
            $comid = $team["comid"];
            $company = D("User")->getSingleUserInfoById($comid);
            $info["company"] = $company;
        }else{
            //查询邀请该设计师的装修公司
            $pageIndex = 1;
            $pageCount = 10;
            if(I("get.p") !== ""){
                $pageIndex = I("get.p");
            }
            $team = $this->getInviteCompany($_SESSION["u_userInfo"]["id"],$pageIndex,$pageCount);
            $info["team"] = $team["team"];
            $info["page"] = $team["page"];
        }
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 加入/拒绝加入装修公司
     * @return [type] [description]
     */
    public function joinus(){
        if($_POST){
            $id = I("post.comid");
            $agree = I("post.agree") == 1?2:1;
            //查询是否存在邀请
            $team = D("Team")->getTeamInfo($id,$comid,0);
            if(count($team) > 0){
                $teamModel = D("Team");
                $teamModel->startTrans();
                if($agree == 2){
                    //1.将其他公司邀请改为拒绝
                    $data = array(
                            "zt"=>1
                                  );
                    D("Team")->editTeam($_SESSION["u_userInfo"]["id"],"",$data);
                    //入住公司
                    $data = array(
                            "zt"=>2
                                  );
                    $i = D("Team")->editTeam($_SESSION["u_userInfo"]["id"],$id,$data);

                    //更新设计师数据
                    $tempdata['team_count'] = M('team')->where(array('zt' => '2','comid' => $id))->count();
                    M("user_company")->where(array('userid' => $id))->save($tempdata);

                }else{
                    //拒绝入住公司
                    $data = array(
                            "zt"=>1
                                  );
                    $i = D("Team")->editTeam($_SESSION["u_userInfo"]["id"],$id,$data);
                }
                if($i !== false){
                    $teamModel->commit();
                    $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                }else{
                    $teamModel->rollback();
                    $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
                }
            }
            $this->ajaxReturn(array("data"=>"","info"=>"该公司并未邀请您加入,加入失败！","status"=>0));
        }
    }

    /**
     * 解除绑定
     * @return [type] [description]
     */
    public function release(){
        if($_POST){
            $id = session("u_userInfo.id");//不接收页面传过来的id 要去session中取出id 因为这才是用户本身
            $comid = I("post.comid");

            //查询该设计师和装修公式是否是雇佣关系
            $team = D("Team")->getTeamInfo($id,$comid,2);
            if(count($team) > 0){
                $i = D("Team")->deleteTeam($id,$comid);
                if($i !== false){

                    //更新设计师数据
                    $data['team_count'] = M('team')->where(array('zt' => '2','comid' => $comid))->count();
                    M("user_company")->where(array('userid' => $comid))->save($data);


                    $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                }
                $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重试","status"=>0));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"您与该公司无关系,解除绑定失败！","status"=>0));
        }
    }

    private function getInviteCompany($id,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Team")->getInviteCompanyCount($_SESSION["u_userInfo"]["id"]);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $team = D("Team")->getInviteCompany($_SESSION["u_userInfo"]["id"],($page->pageIndex-1)*$pageCount,$pageCount);
            return array("team"=>$team,"page"=>$pageTmp);
        }
        return null;
    }
}